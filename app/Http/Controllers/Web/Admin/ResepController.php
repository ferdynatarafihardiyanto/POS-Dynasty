<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\Produk;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    public function index()
    {
        $produks = Produk::with('resep.detail.bahanBaku')->where('aktif', true)->get();
        $bahanBakus = \App\Models\BahanBaku::all(); // Needed for the dropdown in modal
        return view('admin.resep.index', compact('produks', 'bahanBakus'));
    }

    public function storeDetail(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'qty' => 'required|numeric|min:0.01'
        ]);

        $resep = Resep::firstOrCreate(['produk_id' => $request->produk_id]);
        
        $resep->detail()->updateOrCreate(
            ['bahan_baku_id' => $request->bahan_baku_id],
            ['jumlah' => $request->qty]
        );

        return redirect()->back()->with('success', 'Bahan berhasil ditambahkan ke resep');
    }

    public function destroyDetail($id)
    {
        \App\Models\ResepDetail::destroy($id);
        return redirect()->back()->with('success', 'Bahan berhasil dihapus dari resep');
    }
}
