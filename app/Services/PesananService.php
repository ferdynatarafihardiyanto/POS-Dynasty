<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
use App\Models\ModifierOption;
use Illuminate\Support\Facades\DB;
use Exception;

class PesananService
{
    protected $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    public function buatPesanan($mejaId, $items, $catatan)
    {
        // Validasi stok produk utama
        $this->stokService->validasiStokPesanan($items, false);

        $produkIds = array_column($items, 'produk_id');
        $produkDb = Produk::with('modifierGroups.options')->whereIn('id', $produkIds)->get()->keyBy('id');
        
        $totalHarga = 0;
        $details = [];

        foreach ($items as $item) {
            if (!isset($produkDb[$item['produk_id']])) {
                throw new Exception("Produk tidak ditemukan.");
            }
            $produk = $produkDb[$item['produk_id']];
            
            // Proses Modifiers
            $modifierData = $this->processModifiers($produk, $item['modifiers'] ?? []);
            
            $hargaSatuan = $produk->harga + $modifierData['total_tambahan'];
            $subtotal = $hargaSatuan * $item['jumlah'];
            $totalHarga += $subtotal;

            $details[] = [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama,
                'harga' => $hargaSatuan, 
                'jumlah' => $item['jumlah'],
                'catatan' => $item['catatan'] ?? null,
                'subtotal' => $subtotal,
                'modifiers_snapshot' => empty($modifierData['snapshot']) ? null : json_encode($modifierData['snapshot'])
            ];
        }

        DB::beginTransaction();
        try {
            $nomorPesanan = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $pesanan = Pesanan::create([
                'nomor_pesanan' => $nomorPesanan,
                'meja_id' => $mejaId,
                'status' => 'menunggu_pembayaran',
                'total_harga' => $totalHarga,
                'catatan' => $catatan
            ]);

            foreach ($details as $detail) {
                $detail['pesanan_id'] = $pesanan->id;
                DetailPesanan::create($detail);
            }

            DB::commit();
            return $pesanan;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function processModifiers($produk, $inputModifierOptionIds)
    {
        $snapshot = [];
        $totalTambahan = 0;
        
        $validGroups = $produk->modifierGroups->where('aktif', true)->keyBy('id');
        
        // Group input option IDs by their group
        $selectedOptions = ModifierOption::whereIn('id', $inputModifierOptionIds)->where('aktif', true)->get();
        
        $groupedSelections = [];
        foreach ($selectedOptions as $opt) {
            $groupedSelections[$opt->modifier_group_id][] = $opt;
        }

        // Validate required groups and selections
        foreach ($validGroups as $groupId => $group) {
            $selections = $groupedSelections[$groupId] ?? [];
            $count = count($selections);

            if ($group->wajib_diisi && $count == 0) {
                throw new Exception("Pilihan {$group->nama} wajib diisi untuk produk {$produk->nama}.");
            }

            if ($count > 0) {
                if ($count < $group->min_pilihan) {
                    throw new Exception("Pilihan {$group->nama} minimal {$group->min_pilihan} untuk produk {$produk->nama}.");
                }
                if ($count > $group->max_pilihan) {
                    throw new Exception("Pilihan {$group->nama} maksimal {$group->max_pilihan} untuk produk {$produk->nama}.");
                }

                foreach ($selections as $opt) {
                    $totalTambahan += $opt->harga_tambahan;
                    $snapshot[] = [
                        'group_id' => $group->id,
                        'group_nama' => $group->nama,
                        'option_id' => $opt->id,
                        'option_nama' => $opt->nama,
                        'harga_tambahan' => $opt->harga_tambahan
                    ];
                }
            }
        }

        // Check if any selected option doesn't belong to the product's valid groups
        foreach ($groupedSelections as $groupId => $selections) {
            if (!isset($validGroups[$groupId])) {
                throw new Exception("Varian yang dipilih tidak valid untuk produk {$produk->nama}.");
            }
        }

        return [
            'total_tambahan' => $totalTambahan,
            'snapshot' => $snapshot
        ];
    }
}
