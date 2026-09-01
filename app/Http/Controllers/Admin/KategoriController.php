<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function daftarKategori()
    {
        return response()->json([
            'message' => 'Daftar Kategori',
            'data' => Kategori::all()
        ]);
    }

    public function tambahKategori(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori,nama',
            'deskripsi' => 'nullable|string'
        ]);

        $kategori = Kategori::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'aktif' => true
        ]);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $kategori
        ], 201);
    }

    public function detailKategori($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail Kategori',
            'data' => $kategori
        ]);
    }

    public function ubahKategori(Request $request, $id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:100|unique:kategori,nama,' . $id,
            'deskripsi' => 'nullable|string'
        ]);

        $kategori->update([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi
        ]);

        return response()->json([
            'message' => 'Kategori berhasil diubah',
            'data' => $kategori
        ]);
    }

    public function ubahStatusKategori(Request $request, $id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json(['message' => 'Kategori tidak ditemukan'], 404);
        }

        $request->validate([
            'aktif' => 'required|boolean'
        ]);

        $kategori->aktif = $request->aktif;
        $kategori->save();

        return response()->json([
            'message' => 'Status kategori berhasil diubah',
            'data' => $kategori
        ]);
    }
}
