<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\Satuan;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $bahanBakus = BahanBaku::latest()->paginate(10);
        $satuans = Satuan::where('aktif', true)->orderBy('nama')->get();
        return view('admin.bahan_baku.index', compact('bahanBakus', 'satuans'));
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

    public function update(Request $request, BahanBaku $bahanBaku)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'stok' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
        ]);

        $bahanBaku->update($validated);
        return redirect()->back()->with('success', 'Bahan baku berhasil diperbarui');
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
