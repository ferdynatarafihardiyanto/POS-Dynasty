<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\RiwayatStok;
use Exception;

class StokService
{
    /**
     * Memvalidasi ketersediaan stok untuk daftar produk yang dipesan.
     * Harus mengunci bahan baku / produk jika berada di dalam transaction.
     * 
     * @param array $details (format: [['produk_id' => X, 'jumlah' => Y], ...])
     * @param bool $lock
     * @throws Exception
     */
    public function validasiStokPesanan(array $details, $lock = false)
    {
        // Kumpulkan semua produk_id
        $produkIds = array_column($details, 'produk_id');
        $produks = Produk::with('resep.details')->whereIn('id', $produkIds)->get()->keyBy('id');

        // Kumpulkan semua bahan_baku_id yang dibutuhkan
        $kebutuhanBahan = [];
        $kebutuhanProduk = [];

        foreach ($details as $item) {
            $produk = $produks[$item['produk_id']] ?? null;
            if (!$produk) {
                throw new Exception("Produk dengan ID {$item['produk_id']} tidak ditemukan.");
            }
            if (!$produk->aktif) {
                throw new Exception("Produk {$produk->nama} sedang tidak tersedia.");
            }

            if ($produk->resep) {
                if ($produk->resep->details->isEmpty()) {
                    throw new Exception("Produk {$produk->nama} memiliki resep tetapi tidak ada detail resep (INVALID RECIPE).");
                }
                foreach ($produk->resep->details as $resepDetail) {
                    $bahanId = $resepDetail->bahan_baku_id;
                    if (!isset($kebutuhanBahan[$bahanId])) {
                        $kebutuhanBahan[$bahanId] = 0;
                    }
                    $kebutuhanBahan[$bahanId] += $resepDetail->jumlah * $item['jumlah'];
                }
            } else {
                if (!isset($kebutuhanProduk[$produk->id])) {
                    $kebutuhanProduk[$produk->id] = 0;
                }
                $kebutuhanProduk[$produk->id] += $item['jumlah'];
            }
        }

        // Lock & Validasi Bahan Baku
        if (!empty($kebutuhanBahan)) {
            $bahanIds = array_keys($kebutuhanBahan);
            sort($bahanIds); // order by id to prevent deadlock
            
            $query = BahanBaku::whereIn('id', $bahanIds);
            if ($lock) $query->lockForUpdate();
            $bahanBakus = $query->get()->keyBy('id');

            foreach ($kebutuhanBahan as $bahanId => $totalButuh) {
                $bahan = $bahanBakus[$bahanId] ?? null;
                if (!$bahan || !$bahan->aktif) {
                    throw new Exception("Bahan baku ID {$bahanId} tidak tersedia atau tidak aktif.");
                }
                if ($bahan->stok < $totalButuh) {
                    throw new Exception("Stok bahan baku {$bahan->nama} tidak mencukupi.");
                }
            }
        }

        // Lock & Validasi Produk (tanpa resep)
        if (!empty($kebutuhanProduk)) {
            $prodIds = array_keys($kebutuhanProduk);
            sort($prodIds);
            
            $query = Produk::whereIn('id', $prodIds);
            if ($lock) $query->lockForUpdate();
            $produkTbl = $query->get()->keyBy('id');

            foreach ($kebutuhanProduk as $prodId => $totalButuh) {
                $prod = $produkTbl[$prodId];
                if ($prod->stok < $totalButuh) {
                    throw new Exception("Stok produk {$prod->nama} tidak mencukupi.");
                }
            }
        }
    }

    /**
     * Mengurangi stok (produk atau bahan baku) berdasarkan pesanan.
     * Asumsi: Sudah divalidasi dan di-lock oleh validasiStokPesanan($details, true).
     */
    public function kurangiStokPesanan($pesanan)
    {
        $kebutuhanBahan = [];
        $kebutuhanProduk = [];
        $produks = Produk::with('resep.details')->whereIn('id', $pesanan->detailPesanan->pluck('produk_id'))->get()->keyBy('id');

        foreach ($pesanan->detailPesanan as $detail) {
            $produk = $produks[$detail->produk_id];
            if ($produk->resep) {
                foreach ($produk->resep->details as $resepDetail) {
                    $bahanId = $resepDetail->bahan_baku_id;
                    if (!isset($kebutuhanBahan[$bahanId])) {
                        $kebutuhanBahan[$bahanId] = 0;
                    }
                    $kebutuhanBahan[$bahanId] += $resepDetail->jumlah * $detail->jumlah;
                }
            } else {
                if (!isset($kebutuhanProduk[$produk->id])) {
                    $kebutuhanProduk[$produk->id] = 0;
                }
                $kebutuhanProduk[$produk->id] += $detail->jumlah;
            }
        }

        foreach ($kebutuhanBahan as $bahanId => $totalButuh) {
            $bahan = BahanBaku::find($bahanId);
            $stokSebelum = $bahan->stok;
            $bahan->stok -= $totalButuh;
            $bahan->save();

            RiwayatStok::create([
                'bahan_baku_id' => $bahan->id,
                'produk_id' => null,
                'jenis' => 'keluar',
                'jumlah' => $totalButuh,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $bahan->stok,
                'referensi' => $pesanan->nomor_pesanan,
                'keterangan' => 'Pesanan dibayar'
            ]);
        }

        foreach ($kebutuhanProduk as $prodId => $totalButuh) {
            $prod = Produk::find($prodId);
            $stokSebelum = $prod->stok;
            $prod->stok -= $totalButuh;
            $prod->save();

            RiwayatStok::create([
                'produk_id' => $prod->id,
                'bahan_baku_id' => null,
                'jenis' => 'keluar',
                'jumlah' => $totalButuh,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $prod->stok,
                'referensi' => $pesanan->nomor_pesanan,
                'keterangan' => 'Pesanan dibayar'
            ]);
        }
    }
}
