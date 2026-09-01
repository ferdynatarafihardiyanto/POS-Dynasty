<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function daftarStok()
    {
        $produk = Produk::select('id', 'nama', 'stok')->get()->map(function($p) {
            return [
                'id' => $p->id,
                'nama_produk' => $p->nama,
                'stok' => $p->stok
            ];
        });

        return response()->json([
            'message' => 'Data stok berhasil diambil',
            'data' => $produk
        ]);
    }

    public function detailStok($produk_id)
    {
        $produk = Produk::find($produk_id);
        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail stok berhasil diambil',
            'data' => [
                'id' => $produk->id,
                'nama_produk' => $produk->nama,
                'stok' => $produk->stok
            ]
        ]);
    }

    public function stokMasuk(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $produk = Produk::lockForUpdate()->find($request->produk_id);
            $stokSebelum = $produk->stok;
            $produk->stok += $request->jumlah;
            $produk->save();

            RiwayatStok::create([
                'produk_id' => $produk->id,
                'jenis' => 'masuk',
                'jumlah' => $request->jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $produk->stok,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Stok berhasil ditambahkan',
                'data' => [
                    'produk_id' => $produk->id,
                    'stok_sekarang' => $produk->stok
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menambah stok'], 500);
        }
    }

    public function stokKeluar(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $produk = Produk::lockForUpdate()->find($request->produk_id);
            $stokSebelum = $produk->stok;

            if ($produk->stok < $request->jumlah) {
                DB::rollBack();
                return response()->json(['message' => 'Stok produk tidak mencukupi'], 422);
            }

            $produk->stok -= $request->jumlah;
            $produk->save();

            RiwayatStok::create([
                'produk_id' => $produk->id,
                'jenis' => 'keluar',
                'jumlah' => $request->jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $produk->stok,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Stok berhasil dikurangi',
                'data' => [
                    'produk_id' => $produk->id,
                    'stok_sekarang' => $produk->stok
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengurangi stok'], 500);
        }
    }

    public function riwayatStok()
    {
        $riwayat = RiwayatStok::with('produk')->latest()->get()->map(function($r) {
            return [
                'produk' => $r->produk->nama ?? null,
                'jenis' => $r->jenis,
                'jumlah' => $r->jumlah,
                'stok_sebelum' => $r->stok_sebelum,
                'stok_sesudah' => $r->stok_sesudah,
                'referensi' => $r->referensi,
                'keterangan' => $r->keterangan
            ];
        });

        return response()->json([
            'message' => 'Riwayat stok berhasil diambil',
            'data' => $riwayat
        ]);
    }
}
