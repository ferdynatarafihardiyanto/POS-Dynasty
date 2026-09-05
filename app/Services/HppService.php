<?php

namespace App\Services;

use App\Models\Produk;

class HppService
{
    /**
     * Hitung HPP produk berdasarkan resep dan harga beli bahan baku.
     * HPP = Σ (jumlah bahan baku × harga beli per unit)
     *
     * @param Produk $produk
     * @return float
     */
    public function hitungHppProduk(Produk $produk): float
    {
        // Jika produk tidak memiliki resep, kembalikan HPP dari database produk
        if (!$produk->resep) {
            return (float) $produk->hpp;
        }

        $hpp = 0;

        foreach ($produk->resep->detail as $dtl) {
            // jumlah bahan baku × harga beli per unit bahan baku
            $hpp += $dtl->jumlah * $dtl->bahanBaku->harga_beli;
        }

        return (float) $hpp;
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
}
