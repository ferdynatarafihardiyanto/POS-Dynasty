<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\ResepDetail;
use App\Services\StokService;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    public function index(StokService $stokService)
    {
        // Pastikan seluruh data HPP dan Stok resep selalu tersinkronisasi terbaru
        $stokService->syncAllProdukResep();

        $produks = Produk::with(['resep.detail.bahanBaku', 'kategori'])
            ->where('aktif', true)
            ->where('tipe_produk', '!=', 'bundling')
            ->orderBy('nama')
            ->get();

        $bahanBakus = BahanBaku::where('aktif', true)->orderBy('nama')->get();

        return view('admin.resep.index', compact('produks', 'bahanBakus'));
    }

    public function storeDetail(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'bahan_baku_id' => 'required|exists:bahan_baku,id',
            'qty' => 'required|numeric|min:0.001'
        ]);

        $resep = Resep::firstOrCreate(['produk_id' => $request->produk_id]);
        
        $resep->detail()->updateOrCreate(
            ['bahan_baku_id' => $request->bahan_baku_id],
            ['jumlah' => $request->qty]
        );

        $produk = Produk::with('resep.detail.bahanBaku')->find($request->produk_id);
        if ($produk) {
            $produk->tipe_produk = 'resep';
            $produk->syncStokDanHpp();
        }

        $namaBahan = BahanBaku::find($request->bahan_baku_id)?->nama ?? 'Bahan';
        return redirect()->route('admin.resep.index', ['produk_id' => $request->produk_id])
            ->with('success', "Bahan \"{$namaBahan}\" berhasil disimpan ke resep {$produk->nama}. HPP otomatis diperbarui: Rp " . number_format($produk->hpp, 0, ',', '.') . " | Stok tersedia: {$produk->stok} porsi.");
    }

    public function destroyDetail($id)
    {
        $detail = ResepDetail::find($id);
        $produkId = null;

        if ($detail) {
            $resep = $detail->resep;
            $produkId = $resep ? $resep->produk_id : null;
            $namaBahan = $detail->bahanBaku?->nama ?? 'Bahan';
            $detail->delete();

            if ($produkId) {
                $produk = Produk::with('resep.detail.bahanBaku')->find($produkId);
                if ($produk) {
                    if (!$produk->resep || $produk->resep->detail->isEmpty()) {
                        $produk->tipe_produk = 'satuan';
                    }
                    $produk->syncStokDanHpp();
                }
            }

            return redirect()->route('admin.resep.index', ['produk_id' => $produkId])
                ->with('success', "Bahan \"{$namaBahan}\" berhasil dihapus dari resep.");
        }

        return redirect()->back()->with('success', 'Bahan berhasil dihapus.');
    }
}
