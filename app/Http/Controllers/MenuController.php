<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\CafeTable;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with(['kategori', 'modifierGroups' => function($q) {
            $q->where('modifier_groups.aktif', true)->with(['options' => function($q2) {
                $q2->where('aktif', true);
            }]);
        }])
        ->where('aktif', true)
        ->whereHas('kategori', function ($q) {
            $q->where('aktif', true);
        });

        if ($request->has('kategori_id') && $request->kategori_id != '') {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->has('cari') && $request->cari != '') {
            $query->where('nama', 'like', '%' . $request->cari . '%');
        }

        return response()->json([
            'message' => 'Daftar menu',
            'data' => $query->get()
        ]);
    }

    public function show($id)
    {
        $produk = Produk::with('kategori')
            ->where('aktif', true)
            ->whereHas('kategori', function ($q) {
                $q->where('aktif', true);
            })
            ->find($id);

        if (!$produk) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Produk berhasil ditemukan',
            'data' => [
                'id' => $produk->id,
                'nama' => $produk->nama,
                'deskripsi' => $produk->deskripsi,
                'harga' => $produk->harga,
                'gambar' => $produk->gambar,
                'gambar_url' => $produk->gambar_url,
                'kategori' => [
                    'id' => $produk->kategori->id,
                    'nama' => $produk->kategori->nama
                ]
            ]
        ]);
    }

    public function daftarKategori()
    {
        $kategori = Kategori::where('aktif', true)->get(['id', 'nama']);

        return response()->json([
            'message' => 'Kategori berhasil diambil',
            'data' => $kategori
        ]);
    }

    public function menuBerdasarkanMeja($qr_token)
    {
        $table = CafeTable::where('qr_token', $qr_token)->first();

        if (!$table) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        if ($table->status !== 'active') {
            return response()->json(['message' => 'Meja sedang tidak tersedia'], 422);
        }

        $kategori = Kategori::where('aktif', true)->get(['id', 'nama']);
        
        $produk = Produk::with('kategori')
            ->where('aktif', true)
            ->whereHas('kategori', function ($q) {
                $q->where('aktif', true);
            })
            ->get(['id', 'nama', 'harga', 'kategori_id', 'gambar']);

        $produkFormatted = $produk->map(function ($p) {
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'harga' => $p->harga,
                'gambar' => $p->gambar,
                'gambar_url' => $p->gambar_url
            ];
        });

        return response()->json([
            'message' => 'Menu berhasil diambil',
            'data' => [
                'meja' => [
                    'nomor' => $table->table_number
                ],
                'kategori' => $kategori,
                'produk' => $produkFormatted
            ]
        ]);
    }
}
