<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index()
    {
        $riwayats = RiwayatStok::with(['produk', 'bahanBaku', 'user'])->latest()->paginate(15);
        $produks = \App\Models\Produk::all();
        $bahanBakus = \App\Models\BahanBaku::all();
        return view('admin.stok.index', compact('riwayats', 'produks', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required|integer',
            'tipe' => 'required|in:masuk,keluar,penyesuaian',
            'qty' => 'required|numeric',
            'keterangan' => 'nullable|string'
        ]);

        $riwayat = new RiwayatStok();
        $riwayat->jenis = $validated['tipe'];
        $riwayat->jumlah = $validated['qty'];
        $riwayat->keterangan = $validated['keterangan'];
        $riwayat->user_id = auth()->id();
        $riwayat->referensi = 'MANUAL-' . time();

        if ($validated['tipe_item'] == 'produk') {
            $riwayat->produk_id = $validated['item_id'];
            $model = \App\Models\Produk::find($validated['item_id']);
        } else {
            $riwayat->bahan_baku_id = $validated['item_id'];
            $model = \App\Models\BahanBaku::find($validated['item_id']);
        }

        if ($model) {
            $stokSebelum = $model->stok;
            
            // Adjust current stock
            if ($validated['tipe'] == 'keluar') {
                $model->stok -= abs($validated['qty']);
            } else {
                $model->stok += abs($validated['qty']); // Masuk/Penyesuaian (asumsi positif untuk tambah)
            }
            $model->save();
            
            $riwayat->stok_sebelum = $stokSebelum;
            $riwayat->stok_sesudah = $model->stok;
            $riwayat->save();
        }

        return redirect()->back()->with('success', 'Mutasi stok berhasil dicatat');
    }
}
