<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;

use App\Models\RiwayatStok;
use App\Models\Kategori;

class ProdukController extends Controller
{
    public function daftarProduk(Request $request)
    {
        $query = Produk::with('kategori');

        if ($request->has('cari')) {
            $query->where('nama', 'like', '%' . $request->cari . '%');
        }

        if ($request->has('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->has('aktif')) {
            $query->where('aktif', filter_var($request->aktif, FILTER_VALIDATE_BOOLEAN));
        }

        $produk = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'Daftar Produk',
            'data' => $produk->items(),
            'meta' => [
                'current_page' => $produk->currentPage(),
                'last_page' => $produk->lastPage(),
                'total' => $produk->total(),
            ]
        ]);
    }

    public function tambahProduk(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0'
        ]);

        $kategori = Kategori::find($request->kategori_id);
        if (!$kategori->aktif) {
            return response()->json(['message' => 'Kategori sedang tidak aktif'], 422);
        }

        $produk = Produk::create([
            'kategori_id' => $request->kategori_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'aktif' => true
        ]);

        if ($produk->stok > 0) {
            RiwayatStok::create([
                'produk_id' => $produk->id,
                'jenis' => 'masuk',
                'jumlah' => $produk->stok,
                'stok_sebelum' => 0,
                'stok_sesudah' => $produk->stok,
                'keterangan' => 'Stok awal produk'
            ]);
        }

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data' => $produk
        ], 201);
    }

    public function detailProduk($id)
    {
        $produk = Produk::with('kategori')->find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail Produk',
            'data' => $produk
        ]);
    }

    public function ubahProduk(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'gambar' => 'nullable|string'
        ]);

        $kategori = Kategori::find($request->kategori_id);
        if (!$kategori->aktif) {
            return response()->json(['message' => 'Kategori sedang tidak aktif'], 422);
        }

        $produk->update([
            'kategori_id' => $request->kategori_id,
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
            'gambar' => $request->gambar ?? $produk->gambar
        ]);

        return response()->json([
            'message' => 'Produk berhasil diubah',
            'data' => $produk
        ]);
    }

    public function ubahStatusProduk(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $request->validate([
            'aktif' => 'required|boolean'
        ]);

        $produk->aktif = $request->aktif;
        $produk->save();

        return response()->json([
            'message' => 'Status produk berhasil diubah',
            'data' => $produk
        ]);
    }
}
