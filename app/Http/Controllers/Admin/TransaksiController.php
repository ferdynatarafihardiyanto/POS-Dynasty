<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pembayaran;

class TransaksiController extends Controller
{
    public function daftarTransaksi(Request $request)
    {
        $query = Pembayaran::with('pesanan.meja')
            ->where('status', 'berhasil');

        if ($request->has('cari')) {
            $cari = $request->cari;
            $query->where(function($q) use ($cari) {
                $q->where('nomor_transaksi', 'like', "%{$cari}%")
                  ->orWhereHas('pesanan', function($q) use ($cari) {
                      $q->where('nomor_pesanan', 'like', "%{$cari}%")
                        ->orWhereHas('meja', function($q) use ($cari) {
                            $q->where('table_number', 'like', "%{$cari}%");
                        });
                  });
            });
        }

        if ($request->has('tanggal_mulai') && $request->has('tanggal_selesai')) {
            $request->validate([
                'tanggal_mulai' => 'date',
                'tanggal_selesai' => 'date|after_or_equal:tanggal_mulai'
            ]);
            $query->whereDate('dibayar_pada', '>=', $request->tanggal_mulai)
                  ->whereDate('dibayar_pada', '<=', $request->tanggal_selesai);
        }

        if ($request->has('metode_pembayaran')) {
            $request->validate(['metode_pembayaran' => 'in:cash,qris']);
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $transaksi = $query->orderBy('dibayar_pada', 'desc')->paginate(10);

        return response()->json([
            'message' => 'History transaksi berhasil diambil',
            'data' => $transaksi->map(function($t) {
                return [
                    'id' => $t->id,
                    'nomor_transaksi' => $t->nomor_transaksi,
                    'nomor_pesanan' => $t->pesanan->nomor_pesanan ?? null,
                    'meja' => $t->pesanan->meja->table_number ?? null,
                    'metode_pembayaran' => $t->metode_pembayaran,
                    'total_harga' => $t->pesanan->total_harga ?? 0,
                    'jumlah_bayar' => $t->jumlah_bayar,
                    'kembalian' => $t->kembalian,
                    'status' => $t->status,
                    'dibayar_pada' => $t->dibayar_pada
                ];
            }),
            'meta' => [
                'current_page' => $transaksi->currentPage(),
                'last_page' => $transaksi->lastPage(),
                'total' => $transaksi->total(),
            ]
        ]);
    }

    public function detailTransaksi($id)
    {
        $transaksi = Pembayaran::with('pesanan.meja', 'pesanan.detailPesanan.produk')
            ->where('status', 'berhasil')
            ->find($id);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail transaksi berhasil diambil',
            'data' => [
                'nomor_transaksi' => $transaksi->nomor_transaksi,
                'nomor_pesanan' => $transaksi->pesanan->nomor_pesanan ?? null,
                'meja' => $transaksi->pesanan->meja->table_number ?? null,
                'tanggal' => $transaksi->dibayar_pada,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'daftar_produk' => $transaksi->pesanan->detailPesanan->map(function($d) {
                    return [
                        'nama_produk' => $d->nama_produk,
                        'harga' => $d->harga,
                        'jumlah' => $d->jumlah,
                        'subtotal' => $d->subtotal
                    ];
                }),
                'total' => $transaksi->pesanan->total_harga ?? 0,
                'jumlah_bayar' => $transaksi->jumlah_bayar,
                'kembalian' => $transaksi->kembalian,
                'status' => $transaksi->status
            ]
        ]);
    }
}
