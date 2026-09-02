<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Exception;

class PembayaranService
{
    protected $stokService;
    protected $hppService;

    public function __construct(StokService $stokService, HppService $hppService)
    {
        $this->stokService = $stokService;
        $this->hppService = $hppService;
    }

    public function prosesPembayaran($pesananId, $metodePembayaran, $jumlahBayar)
    {
        if (!in_array($metodePembayaran, ['cash', 'qris', 'transfer'])) {
            throw new Exception("Metode pembayaran tidak valid.");
        }

        DB::beginTransaction();

        try {
            $pesanan = Pesanan::with(['detailPesanan.produk'])->lockForUpdate()->find($pesananId);

            if (!$pesanan) {
                throw new Exception("Pesanan tidak ditemukan.");
            }

            if ($pesanan->status === 'dibayar') {
                throw new Exception("Pesanan sudah dibayar.");
            }

            if ($pesanan->status !== 'menunggu_pembayaran') {
                throw new Exception("Pesanan tidak dapat dibayar.");
            }

            // Lock and validate stock
            $detailsForStock = $pesanan->detailPesanan->map(function($d) {
                return ['produk_id' => $d->produk_id, 'jumlah' => $d->jumlah];
            })->toArray();
            
            $this->stokService->validasiStokPesanan($detailsForStock, true);

            // Validasi jumlah bayar
            $kembalian = 0;
            if ($metodePembayaran === 'cash') {
                if ($jumlahBayar < $pesanan->total_harga) {
                    throw new Exception("Jumlah pembayaran tidak mencukupi.");
                }
                $kembalian = $jumlahBayar - $pesanan->total_harga;
            } else if (in_array($metodePembayaran, ['qris', 'transfer'])) {
                if ($jumlahBayar != $pesanan->total_harga) {
                    throw new Exception("Jumlah pembayaran {$metodePembayaran} harus sesuai dengan total pesanan.");
                }
                $kembalian = 0;
            }

            // Hitung dan simpan HPP untuk setiap detail
            foreach ($pesanan->detailPesanan as $detail) {
                $produk = $detail->produk;
                $hpp = $this->hppService->hitungHppProduk($produk);
                $detail->hpp = $hpp;
                $detail->save();
            }

            // Kurangi stok
            $this->stokService->kurangiStokPesanan($pesanan);

            $nomorTransaksi = 'TRX-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $pembayaran = Pembayaran::create([
                'pesanan_id' => $pesanan->id,
                'nomor_transaksi' => $nomorTransaksi,
                'metode_pembayaran' => $metodePembayaran,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'status' => 'berhasil',
                'dibayar_pada' => now()
            ]);

            $pesanan->status = 'dibayar';
            $pesanan->save();

            DB::commit();

            return $pembayaran;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
