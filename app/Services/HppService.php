<?php

namespace App\Services;

use App\Models\Produk;

class HppService
{
    /**
     * Hitung HPP produk berdasarkan resep dan harga beli bahan baku atau bundling.
     * HPP Resep = Σ (jumlah bahan baku × harga beli per unit)
     *
     * @param Produk $produk
     * @return float
     */
    public function hitungHppProduk(Produk $produk): float
    {
        return $produk->hitungHpp();
    }

    /**
     * Hitung margin berdasarkan harga jual dan HPP.
     * Margin = Harga Jual - HPP
     *
     * @param float $hargaJual
     * @param float $hpp
     * @return float
     */
    public function hitungMargin(float $hargaJual, float $hpp): float
    {
        return $hargaJual - $hpp;
    }

    /**
     * Sinkronisasi HPP satu produk ke database.
     *
     * @param Produk $produk
     * @return float
     */
    public function syncProdukHpp(Produk $produk): float
    {
        $hpp = $produk->hitungHpp();
        $produk->hpp = (int) round($hpp);
        $produk->saveQuietly();
        return $hpp;
    }

    /**
     * Sinkronisasi seluruh HPP produk olahan dan bundling.
     */
    public function syncAllHpp(): void
    {
        $produks = Produk::with(['resep.detail.bahanBaku', 'bundleItems.item'])->get();
        foreach ($produks as $p) {
            if ($p->isResep() || $p->isBundle()) {
                $p->hpp = (int) round($p->hitungHpp());
                $p->saveQuietly();
            }
        }
    }
}
