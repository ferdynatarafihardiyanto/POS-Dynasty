<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Produk;
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
        // Reject duplicates
        $produkIds = array_column($items, 'produk_id');
        if (count($produkIds) !== count(array_unique($produkIds))) {
            throw new Exception("Terdapat produk duplikat dalam pesanan.");
        }

        // Validasi stok (tanpa lock, karena pesanan belum dibayar)
        $this->stokService->validasiStokPesanan($items, false);

        $produkDb = Produk::whereIn('id', $produkIds)->get()->keyBy('id');
        $totalHarga = 0;
        $details = [];

        foreach ($items as $item) {
            $produk = $produkDb[$item['produk_id']];
            $subtotal = $produk->harga * $item['jumlah'];
            $totalHarga += $subtotal;

            $details[] = [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama,
                'harga' => $produk->harga, // Backend calculation (SSOT)
                'jumlah' => $item['jumlah'],
                'subtotal' => $subtotal
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

            foreach ($details as &$detail) {
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
}
