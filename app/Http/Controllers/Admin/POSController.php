<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function daftarPesanan()
    {
        $pesanans = Pesanan::with('meja')
            ->where('status', 'menunggu_pembayaran')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nomor_pesanan' => $p->nomor_pesanan,
                    'meja' => $p->meja->table_number ?? null,
                    'total_harga' => $p->total_harga,
                    'status' => $p->status
                ];
            });

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $pesanans
        ]);
    }

    public function detailPesanan($id)
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan'])->find($id);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil',
            'data' => [
                'id' => $pesanan->id,
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'meja' => $pesanan->meja->table_number ?? null,
                'status' => $pesanan->status,
                'total_harga' => $pesanan->total_harga,
                'catatan' => $pesanan->catatan,
                'tanggal' => $pesanan->created_at,
                'detail' => $pesanan->detailPesanan->map(function ($d) {
                    return [
                        'nama_produk' => $d->nama_produk,
                        'harga' => $d->harga,
                        'jumlah' => $d->jumlah,
                        'subtotal' => $d->subtotal
                    ];
                })
            ]
        ]);
    }

    public function prosesPembayaran(Request $request, \App\Services\PembayaranService $pembayaranService)
    {
        $request->validate([
            'pesanan_id' => 'required|integer',
            'metode_pembayaran' => 'required|string|in:cash,qris,transfer',
            'jumlah_bayar' => 'required|integer|min:0'
        ]);

        try {
            $pembayaran = $pembayaranService->prosesPembayaran(
                $request->pesanan_id,
                $request->metode_pembayaran,
                $request->jumlah_bayar
            );

            return response()->json([
                'message' => 'Pembayaran berhasil',
                'data' => [
                    'nomor_transaksi' => $pembayaran->nomor_transaksi,
                    'nomor_pesanan' => $pembayaran->pesanan->nomor_pesanan,
                    'metode_pembayaran' => $pembayaran->metode_pembayaran,
                    'total_harga' => $pembayaran->pesanan->total_harga,
                    'jumlah_bayar' => $pembayaran->jumlah_bayar,
                    'kembalian' => $pembayaran->kembalian,
                    'status' => $pembayaran->status
                ]
            ]);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Pesanan tidak ditemukan.') {
                return response()->json(['message' => $e->getMessage()], 404);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
