<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\CafeTable;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function buatPesanan(Request $request, \App\Services\PesananService $pesananService)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'catatan' => 'nullable|string',
            'produk' => 'required|array|min:1',
            'produk.*.produk_id' => 'required|integer',
            'produk.*.jumlah' => 'required|integer|min:1'
        ], [
            'produk.required' => 'Pesanan tidak boleh kosong',
            'produk.min' => 'Pesanan tidak boleh kosong'
        ]);

        $meja = CafeTable::where('qr_token', $request->qr_token)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        if ($meja->status !== 'active') {
            return response()->json(['message' => 'Meja sedang tidak tersedia'], 422);
        }

        try {
            $pesanan = $pesananService->buatPesanan($meja->id, $request->produk, $request->catatan);

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'meja' => $meja->table_number,
                    'status' => $pesanan->status,
                    'total_harga' => $pesanan->total_harga,
                    'detail' => $pesanan->detailPesanan->map(function ($d) {
                        return [
                            'nama_produk' => $d->nama_produk,
                            'harga' => $d->harga,
                            'jumlah' => $d->jumlah,
                            'subtotal' => $d->subtotal
                        ];
                    })
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function daftarPesanan(Request $request)
    {
        $qrToken = $request->query('qr_token');
        
        if (!$qrToken) {
            return response()->json(['message' => 'QR Token diperlukan'], 400);
        }

        $meja = CafeTable::where('qr_token', $qrToken)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $pesanans = Pesanan::where('meja_id', $meja->id)->get(['nomor_pesanan', 'status', 'total_harga']);

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $pesanans
        ]);
    }

    public function detailPesanan(Request $request, $nomor_pesanan)
    {
        $qrToken = $request->query('qr_token');
        
        if (!$qrToken) {
            return response()->json(['message' => 'QR Token diperlukan'], 400);
        }

        $meja = CafeTable::where('qr_token', $qrToken)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $pesanan = Pesanan::with('detailPesanan')
            ->where('nomor_pesanan', $nomor_pesanan)
            ->first();

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($pesanan->meja_id !== $meja->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pesanan ini'], 403);
        }

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil',
            'data' => [
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'meja' => $meja->table_number,
                'status' => $pesanan->status,
                'total_harga' => $pesanan->total_harga,
                'catatan' => $pesanan->catatan,
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
}
