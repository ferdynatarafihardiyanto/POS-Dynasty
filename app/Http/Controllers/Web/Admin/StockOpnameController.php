<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockOpnameController extends Controller
{
    public function index()
    {
        $opnames = StockOpname::with(['user', 'details.produk', 'details.bahanBaku'])->latest()->paginate(15);
        $produks = Produk::where('aktif', true)->orderBy('nama')->get();
        $bahanBakus = BahanBaku::where('aktif', true)->orderBy('nama')->get();
        return view('admin.stock_opname.index', compact('opnames', 'produks', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required|integer',
            'stok_fisik' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255'
        ], [
            'item_id.required' => 'Silakan pilih produk atau bahan baku yang ingin disesuaikan.',
            'stok_fisik.required' => 'Jumlah stok fisik nyata wajib diisi.',
            'stok_fisik.min' => 'Stok fisik tidak boleh kurang dari 0.'
        ]);

        if ($validated['tipe_item'] == 'produk') {
            $model = Produk::find($validated['item_id']);
        } else {
            $model = BahanBaku::find($validated['item_id']);
        }

        if (!$model) {
            return redirect()->back()->with('error', 'Item yang dipilih tidak ditemukan.');
        }

        $stok_sistem = $model->stok;
        $stok_fisik = $validated['stok_fisik'];
        $selisih = $stok_fisik - $stok_sistem;
        $nomorOpname = 'OPNAME-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $opname = StockOpname::create([
            'nomor_opname' => $nomorOpname,
            'tanggal' => now(),
            'status' => 'selesai',
            'keterangan' => $validated['keterangan'] ?? 'Penyesuaian stok fisik',
            'user_id' => auth()->id()
        ]);

        $opname->details()->create([
            'produk_id' => $validated['tipe_item'] == 'produk' ? $model->id : null,
            'bahan_baku_id' => $validated['tipe_item'] == 'bahan_baku' ? $model->id : null,
            'stok_sistem' => $stok_sistem,
            'stok_fisik' => $stok_fisik,
            'selisih' => $selisih,
            'keterangan' => $validated['keterangan'] ?? '-'
        ]);

        // Update physical stock to model
        $model->stok = $stok_fisik;
        $model->save();

        // Record riwayat stok
        RiwayatStok::create([
            'produk_id' => $validated['tipe_item'] == 'produk' ? $model->id : null,
            'bahan_baku_id' => $validated['tipe_item'] == 'bahan_baku' ? $model->id : null,
            'user_id' => auth()->id(),
            'jenis' => 'penyesuaian',
            'jumlah' => $selisih,
            'stok_sebelum' => $stok_sistem,
            'stok_sesudah' => $stok_fisik,
            'keterangan' => 'Stock Opname: ' . ($validated['keterangan'] ?? 'Penyesuaian fisik'),
            'referensi' => $nomorOpname
        ]);

        return redirect()->route('admin.stock_opname.index')->with('success', "Stock opname {$model->nama} berhasil dicatat! Stok disesuaikan dari {$stok_sistem} menjadi {$stok_fisik}.");
    }
}
