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
        // Jika produk tidak memiliki resep, kembalikan 0 sesuai aturan
        if (!$produk->resep) {
            return 0;
        }

        $hpp = 0;

        foreach ($produk->resep->details as $detail) {
            // jumlah bahan baku × harga beli per unit bahan baku
            $hpp += $detail->jumlah * $detail->bahanBaku->harga_beli;
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
