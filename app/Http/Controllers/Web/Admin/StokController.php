<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;

class StokController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatStok::with(['produk', 'bahanBaku', 'user']);

        if ($request->filled('tipe') && $request->tipe !== 'all') {
            $query->where('jenis', $request->tipe);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referensi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('produk', function($qp) use ($search) {
                      $qp->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bahanBaku', function($qb) use ($search) {
                      $qb->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $riwayats = $query->latest()->paginate(15)->withQueryString();
        $produks = \App\Models\Produk::where('aktif', true)->orderBy('nama')->get();
        $bahanBakus = \App\Models\BahanBaku::where('aktif', true)->orderBy('nama')->get();

        return view('admin.stok.index', compact('riwayats', 'produks', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required|integer',
            'tipe' => 'required|in:masuk,keluar,penyesuaian',
            'qty' => 'required|numeric|gt:0',
            'keterangan' => 'nullable|string|max:255'
        ], [
            'item_id.required' => 'Silakan pilih produk atau bahan baku.',
            'qty.required' => 'Jumlah Qty wajib diisi.',
            'qty.gt' => 'Jumlah Qty harus lebih dari 0.',
        ]);

        if ($validated['tipe_item'] == 'produk') {
            $model = \App\Models\Produk::find($validated['item_id']);
        } else {
            $model = \App\Models\BahanBaku::find($validated['item_id']);
        }

        if (!$model) {
            return redirect()->back()->with('error', 'Item yang dipilih tidak ditemukan.');
        }

        $stokSebelum = $model->stok;
        $qty = abs($validated['qty']);

        if ($validated['tipe'] == 'keluar') {
            if ($model->stok < $qty) {
                return redirect()->back()->with('error', "Stok {$model->nama} tidak mencukupi! Stok saat ini: {$model->stok}");
            }
            $model->stok -= $qty;
        } else {
            $model->stok += $qty;
        }
        $model->save();

        $riwayat = new RiwayatStok();
        $riwayat->jenis = $validated['tipe'];
        $riwayat->jumlah = $qty;
        $riwayat->stok_sebelum = $stokSebelum;
        $riwayat->stok_sesudah = $model->stok;
        $riwayat->keterangan = $validated['keterangan'] ?? '-';
        $riwayat->user_id = auth()->id();
        $riwayat->referensi = 'MANUAL-' . time();

        if ($validated['tipe_item'] == 'produk') {
            $riwayat->produk_id = $model->id;
        } else {
            $riwayat->bahan_baku_id = $model->id;
        }

        $riwayat->save();

        return redirect()->route('admin.stok.index')->with('success', "Mutasi stok {$model->nama} ({$validated['tipe']}) berhasil dicatat.");
    }
}
