<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with(['user', 'detail.produk', 'detail.bahanBaku'])->latest()->paginate(10);
        $produks = \App\Models\Produk::all();
        $bahanBakus = \App\Models\BahanBaku::all();
        return view('admin.stock_opname.index', compact('opnames', 'produks', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required|integer',
            'stok_fisik' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string'
        ]);

        $opname = StockOpname::create([
            'tanggal' => now(),
            'user_id' => auth()->id()
        ]);

        $stok_sistem = 0;
        if ($request->tipe_item == 'produk') {
            $model = \App\Models\Produk::find($request->item_id);
            if ($model) $stok_sistem = $model->stok;
        } else {
            $model = \App\Models\BahanBaku::find($request->item_id);
            if ($model) $stok_sistem = $model->stok;
        }

        $selisih = $request->stok_fisik - $stok_sistem;

        if ($model) {
            $opname->detail()->create([
                'produk_id' => $request->tipe_item == 'produk' ? $request->item_id : null,
                'bahan_baku_id' => $request->tipe_item == 'bahan_baku' ? $request->item_id : null,
                'stok_sistem' => $stok_sistem,
                'stok_fisik' => $request->stok_fisik,
                'selisih' => $selisih,
                'keterangan' => $request->keterangan
            ]);

            // Update physical stock to model
            $model->stok = $request->stok_fisik;
            $model->save();

            // Record riwayat stok
            \App\Models\RiwayatStok::create([
                'produk_id' => $request->tipe_item == 'produk' ? $request->item_id : null,
                'bahan_baku_id' => $request->tipe_item == 'bahan_baku' ? $request->item_id : null,
                'user_id' => auth()->id(),
                'tipe' => 'penyesuaian',
                'qty' => $selisih,
                'keterangan' => 'Stock Opname: ' . $request->keterangan,
                'referensi' => 'OPNAME-' . time()
            ]);
        }

        return redirect()->back()->with('success', 'Stock opname berhasil dicatat dan stok disesuaikan.');
    }
}
