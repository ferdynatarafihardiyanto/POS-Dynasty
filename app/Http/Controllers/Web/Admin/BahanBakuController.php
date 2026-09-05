<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $bahanBakus = BahanBaku::latest()->paginate(10);
        return view('admin.bahan_baku.index', compact('bahanBakus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
        ]);

        BahanBaku::create($validated);
        return redirect()->back()->with('success', 'Bahan baku berhasil ditambahkan');
    }

    public function destroy(BahanBaku $bahanBaku)
    {
        try {
            $bahanBaku->delete();
            return redirect()->back()->with('success', 'Bahan baku berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            // Error code 1451 means foreign key constraint violation
            if ($e->getCode() == 23000) {
                return redirect()->back()->with('error', 'Gagal menghapus! Bahan baku ini sedang digunakan pada resep produk atau riwayat stok. Silakan nonaktifkan bahan baku jika sudah tidak dipakai.');
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }
}
