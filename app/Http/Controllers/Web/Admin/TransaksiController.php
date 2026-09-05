<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'all');
        $query = Pesanan::with(['detailPesanan'])->where('status', 'dibayar');
        
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        
        if ($periode == 'today') {
            $query->whereDate('created_at', $now->toDateString());
        } elseif ($periode == 'week') {
            $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
        } elseif ($periode == 'month') {
            $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
        } elseif ($periode == 'year') {
            $query->whereYear('created_at', $now->year);
        }

        $transaksis = $query->latest()->get();
        
        $totalTransaksi = $transaksis->count();
        $totalPenjualan = $transaksis->sum('total_harga');
        
        // Hitung Laba Kotor dari HPP
        $labaKotor = 0;
        foreach ($transaksis as $transaksi) {
            foreach ($transaksi->detailPesanan as $detail) {
                $hpp = $detail->hpp ?? 0;
                $labaKotor += $detail->subtotal - ($hpp * $detail->jumlah);
            }
        }
        
        return view('admin.transaksi.index', compact('transaksis', 'totalTransaksi', 'totalPenjualan', 'labaKotor', 'periode'));
    }

    public function print($id)
    {
        $pesanan = Pesanan::with(['detailPesanan.produk', 'meja'])->findOrFail($id);
        return view('admin.transaksi.print', compact('pesanan'));
    }
}
