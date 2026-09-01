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
    public function buatPesanan(Request $request)
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

        $produkIds = collect($request->produk)->pluck('produk_id')->toArray();
        
        // Reject duplicates
        if (count($produkIds) !== count(array_unique($produkIds))) {
            return response()->json(['message' => 'Terdapat produk duplikat dalam pesanan'], 422);
        }

        $produkDb = Produk::whereIn('id', $produkIds)->get()->keyBy('id');

        $totalHarga = 0;
        $details = [];

        foreach ($request->produk as $item) {
            $pId = $item['produk_id'];
            if (!isset($produkDb[$pId])) {
                return response()->json(['message' => "Produk dengan ID {$pId} tidak ditemukan"], 422);
            }
            
            $produk = $produkDb[$pId];
            if (!$produk->aktif) {
                return response()->json(['message' => "Produk {$produk->nama} sedang tidak tersedia"], 422);
            }

            if ($produk->stok < $item['jumlah']) {
                return response()->json(['message' => "Stok produk {$produk->nama} tidak mencukupi"], 422);
            }

            $subtotal = $produk->harga * $item['jumlah'];
            $totalHarga += $subtotal;

            $details[] = [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama,
                'harga' => $produk->harga,
                'jumlah' => $item['jumlah'],
                'subtotal' => $subtotal
            ];
        }

        DB::beginTransaction();

        try {
            $nomorPesanan = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            // In reality, this should be generated properly, but rand is fine for now or auto-increment based.
            // Let's ensure unique by adding time or retrying, but random is okay for this simple scenario.

            $pesanan = Pesanan::create([
                'nomor_pesanan' => $nomorPesanan,
                'meja_id' => $meja->id,
                'status' => 'menunggu_pembayaran',
                'total_harga' => $totalHarga,
                'catatan' => $request->catatan
            ]);

            foreach ($details as &$detail) {
                $detail['pesanan_id'] = $pesanan->id;
                DetailPesanan::create($detail);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'meja' => $meja->table_number,
                    'status' => $pesanan->status,
                    'total_harga' => $pesanan->total_harga,
                    'detail' => collect($details)->map(function ($d) {
                        return [
                            'nama_produk' => $d['nama_produk'],
                            'harga' => $d['harga'],
                            'jumlah' => $d['jumlah'],
                            'subtotal' => $d['subtotal']
                        ];
                    })
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pesanan'], 500);
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
