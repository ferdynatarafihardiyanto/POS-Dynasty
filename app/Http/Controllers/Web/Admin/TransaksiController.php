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
        $metode = $request->get('metode', 'all');

        $paidCondition = function ($q) {
            $q->whereHas('pembayaran', function ($p) {
                $p->where('status', 'berhasil');
            })->orWhereIn('status', ['dibayar', 'diproses', 'disajikan', 'selesai']);
        };

        $query = Pesanan::with(['detailPesanan.produk', 'pembayaran', 'meja'])
            ->where($paidCondition);
        
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

        if ($metode == 'cash') {
            $query->where(function ($q) {
                $q->whereHas('pembayaran', function ($p) {
                    $p->where('metode_pembayaran', 'cash');
                })->orWhere(function ($q2) {
                    $q2->doesntHave('pembayaran')
                       ->where(function ($q3) {
                           $q3->where('catatan', 'like', '%TUNAI%')
                              ->orWhere('catatan', 'not like', '%QRIS%');
                       });
                });
            });
        } elseif ($metode == 'qris') {
            $query->where(function ($q) {
                $q->whereHas('pembayaran', function ($p) {
                    $p->where('metode_pembayaran', 'qris');
                })->orWhere('catatan', 'like', '%QRIS%');
            });
        } elseif ($metode == 'transfer') {
            $query->where(function ($q) {
                $q->whereHas('pembayaran', function ($p) {
                    $p->whereIn('metode_pembayaran', ['transfer', 'debit']);
                })->orWhere('catatan', 'like', '%DEBIT%')
                  ->orWhere('catatan', 'like', '%TRANSFER%');
            });
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
        
        return view('admin.transaksi.index', compact('transaksis', 'totalTransaksi', 'totalPenjualan', 'labaKotor', 'periode', 'metode'));
    }

    public function print($id)
    {
        $pesanan = Pesanan::with(['detailPesanan.produk', 'meja'])->findOrFail($id);
        return view('admin.transaksi.print', compact('pesanan'));
    }
}
