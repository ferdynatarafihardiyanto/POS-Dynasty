<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;

class PesananController extends Controller
{
    public function daftarPesanan()
    {
        $pesanans = Pesanan::with('meja')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nomor_pesanan' => $p->nomor_pesanan,
                'meja' => $p->meja->table_number ?? null,
                'status' => $p->status,
                'total_harga' => $p->total_harga,
                'tanggal' => $p->created_at
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
                        'subtotal' => $d->subtotal,
                        'catatan' => $d->catatan,
                        'modifiers_snapshot' => $d->modifiers_snapshot
                    ];
                })
            ]
        ]);
    }
}
