<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private function getDateRange(Request $request)
    {
        $mulai = $request->tanggal_mulai ?? date('Y-m-d');
        $selesai = $request->tanggal_selesai ?? date('Y-m-d');
        
        return [$mulai, $selesai];
    }

    public function laporanPenjualan(Request $request)
    {
        [$mulai, $selesai] = $this->getDateRange($request);

        $pembayarans = Pembayaran::with('pesanan')
            ->where('status', 'berhasil')
            ->whereDate('dibayar_pada', '>=', $mulai)
            ->whereDate('dibayar_pada', '<=', $selesai)
            ->get();

        $totalTransaksi = $pembayarans->count();
        $totalPendapatan = $pembayarans->sum(function($p) {
            return $p->pesanan->total_harga ?? 0;
        });

        $totalCash = $pembayarans->where('metode_pembayaran', 'cash')->sum(function($p) {
            return $p->pesanan->total_harga ?? 0;
        });

        $totalQris = $pembayarans->where('metode_pembayaran', 'qris')->sum(function($p) {
            return $p->pesanan->total_harga ?? 0;
        });

        $totalProdukTerjual = DetailPesanan::whereHas('pesanan.pembayaran', function($q) use ($mulai, $selesai) {
            $q->where('status', 'berhasil')
              ->whereDate('dibayar_pada', '>=', $mulai)
              ->whereDate('dibayar_pada', '<=', $selesai);
        })->sum('jumlah');

        return response()->json([
            'message' => 'Laporan berhasil diambil',
            'data' => [
                'periode' => [
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai
                ],
                'total_transaksi' => $totalTransaksi,
                'total_pendapatan' => $totalPendapatan,
                'total_cash' => $totalCash,
                'total_qris' => $totalQris,
                'total_produk_terjual' => (int) $totalProdukTerjual
            ]
        ]);
    }

    public function laporanProduk(Request $request)
    {
        [$mulai, $selesai] = $this->getDateRange($request);

        $produk = DetailPesanan::whereHas('pesanan.pembayaran', function($q) use ($mulai, $selesai) {
            $q->where('status', 'berhasil')
              ->whereDate('dibayar_pada', '>=', $mulai)
              ->whereDate('dibayar_pada', '<=', $selesai);
        })
        ->select('nama_produk as produk', DB::raw('SUM(jumlah) as jumlah_terjual'))
        ->groupBy('produk_id', 'nama_produk')
        ->orderByDesc('jumlah_terjual')
        ->get();

        return response()->json([
            'data' => $produk
        ]);
    }

    public function laporanPembayaran(Request $request)
    {
        [$mulai, $selesai] = $this->getDateRange($request);

        $pembayarans = Pembayaran::with('pesanan')
            ->where('status', 'berhasil')
            ->whereDate('dibayar_pada', '>=', $mulai)
            ->whereDate('dibayar_pada', '<=', $selesai)
            ->get();

        $cash = $pembayarans->where('metode_pembayaran', 'cash');
        $qris = $pembayarans->where('metode_pembayaran', 'qris');

        return response()->json([
            'data' => [
                'cash' => [
                    'jumlah_transaksi' => $cash->count(),
                    'total' => $cash->sum(function($p) { return $p->pesanan->total_harga ?? 0; })
                ],
                'qris' => [
                    'jumlah_transaksi' => $qris->count(),
                    'total' => $qris->sum(function($p) { return $p->pesanan->total_harga ?? 0; })
                ]
            ]
        ]);
    }
}
