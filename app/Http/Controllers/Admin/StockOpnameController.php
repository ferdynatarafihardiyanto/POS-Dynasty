<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function daftarStockOpname()
    {
        $opnames = StockOpname::orderBy('id', 'desc')->get();
        return response()->json([
            'message' => 'Daftar stock opname berhasil diambil',
            'data' => $opnames
        ]);
    }

    public function buatStockOpname(Request $request)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.produk_id' => 'required_without:detail.*.bahan_baku_id|exists:produk,id',
            'detail.*.bahan_baku_id' => 'required_without:detail.*.produk_id|exists:bahan_baku,id',
            'detail.*.stok_fisik' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $nomorOpname = 'OPNAME-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $opname = StockOpname::create([
                'nomor_opname' => $nomorOpname,
                'tanggal' => date('Y-m-d'),
                'status' => 'draft',
                'keterangan' => $request->keterangan
            ]);

            foreach ($request->detail as $det) {
                if (isset($det['bahan_baku_id'])) {
                    $bahanBaku = BahanBaku::find($det['bahan_baku_id']);
                    $stokSistem = $bahanBaku->stok;
                    $stokFisik = $det['stok_fisik'];
                    $selisih = $stokFisik - $stokSistem;
                    
                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'bahan_baku_id' => $bahanBaku->id,
                        'produk_id' => null,
                        'stok_sistem' => $stokSistem,
                        'stok_fisik' => $stokFisik,
                        'selisih' => $selisih
                    ]);
                } else {
                    $produk = Produk::find($det['produk_id']);
                    $stokSistem = $produk->stok;
                    $stokFisik = $det['stok_fisik'];
                    $selisih = $stokFisik - $stokSistem;
    
                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'produk_id' => $produk->id,
                        'bahan_baku_id' => null,
                        'stok_sistem' => $stokSistem,
                        'stok_fisik' => $stokFisik,
                        'selisih' => $selisih
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Stock opname draft berhasil dibuat',
                'data' => $opname->load('detail')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat stock opname'], 500);
        }
    }

    public function detailStockOpname($id)
    {
        $opname = StockOpname::with(['detail.produk', 'detail.bahanBaku'])->find($id);
        if (!$opname) {
            return response()->json(['message' => 'Stock opname tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail stock opname berhasil diambil',
            'data' => [
                'id' => $opname->id,
                'nomor_opname' => $opname->nomor_opname,
                'tanggal' => $opname->tanggal,
                'status' => $opname->status,
                'keterangan' => $opname->keterangan,
                'detail' => $opname->detail->map(function($d) {
                    $item = $d->bahan_baku_id ? ($d->bahanBaku->nama ?? null) : ($d->produk->nama ?? null);
                    $tipe = $d->bahan_baku_id ? 'bahan_baku' : 'produk';

                    return [
                        'id' => $d->id,
                        'tipe_item' => $tipe,
                        'item_id' => $d->bahan_baku_id ?? $d->produk_id,
                        'nama_item' => $item,
                        'stok_sistem' => $d->stok_sistem,
                        'stok_fisik' => $d->stok_fisik,
                        'selisih' => $d->selisih
                    ];
                })
            ]
        ]);
    }

    public function ubahStockOpname(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string',
            'detail' => 'required|array|min:1',
            'detail.*.produk_id' => 'required_without:detail.*.bahan_baku_id|exists:produk,id',
            'detail.*.bahan_baku_id' => 'required_without:detail.*.produk_id|exists:bahan_baku,id',
            'detail.*.stok_fisik' => 'required|numeric|min:0'
        ]);

        $opname = StockOpname::find($id);
        if (!$opname) {
            return response()->json(['message' => 'Stock opname tidak ditemukan'], 404);
        }

        if ($opname->status !== 'draft') {
            return response()->json(['message' => 'Hanya draft yang dapat diubah'], 422);
        }

        DB::beginTransaction();
        try {
            $opname->keterangan = $request->keterangan;
            $opname->save();

            // Hapus detail lama
            StockOpnameDetail::where('stock_opname_id', $opname->id)->delete();

            // Buat detail baru dengan stok sistem saat ini
            foreach ($request->detail as $det) {
                if (isset($det['bahan_baku_id'])) {
                    $bahanBaku = BahanBaku::find($det['bahan_baku_id']);
                    $stokSistem = $bahanBaku->stok;
                    $stokFisik = $det['stok_fisik'];
                    $selisih = $stokFisik - $stokSistem;

                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'bahan_baku_id' => $bahanBaku->id,
                        'produk_id' => null,
                        'stok_sistem' => $stokSistem,
                        'stok_fisik' => $stokFisik,
                        'selisih' => $selisih
                    ]);
                } else {
                    $produk = Produk::find($det['produk_id']);
                    $stokSistem = $produk->stok;
                    $stokFisik = $det['stok_fisik'];
                    $selisih = $stokFisik - $stokSistem;

                    StockOpnameDetail::create([
                        'stock_opname_id' => $opname->id,
                        'produk_id' => $produk->id,
                        'bahan_baku_id' => null,
                        'stok_sistem' => $stokSistem,
                        'stok_fisik' => $stokFisik,
                        'selisih' => $selisih
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Stock opname draft berhasil diubah',
                'data' => $opname->load('detail')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah stock opname'], 500);
        }
    }

    public function selesaikanStockOpname($id)
    {
        DB::beginTransaction();
        try {
            $opname = StockOpname::lockForUpdate()->find($id);
            if (!$opname) {
                DB::rollBack();
                return response()->json(['message' => 'Stock opname tidak ditemukan'], 404);
            }

            if ($opname->status === 'selesai') {
                DB::rollBack();
                return response()->json(['message' => 'Stock opname sudah diselesaikan'], 422);
            }

            if ($opname->status !== 'draft') {
                DB::rollBack();
                return response()->json(['message' => 'Status stock opname tidak valid'], 422);
            }

            $details = StockOpnameDetail::where('stock_opname_id', $opname->id)->get();

            foreach ($details as $detail) {
                if ($detail->bahan_baku_id) {
                    $bahanBaku = BahanBaku::lockForUpdate()->find($detail->bahan_baku_id);
                    $stokSebelum = $bahanBaku->stok;
                    $bahanBaku->stok = $detail->stok_fisik; 
                    $bahanBaku->save();

                    $selisih = $detail->stok_fisik - $stokSebelum;
                    $detail->stok_sistem = $stokSebelum;
                    $detail->selisih = $selisih;
                    $detail->save();

                    RiwayatStok::create([
                        'bahan_baku_id' => $bahanBaku->id,
                        'produk_id' => null,
                        'jenis' => 'penyesuaian',
                        'jumlah' => $selisih,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $bahanBaku->stok,
                        'referensi' => $opname->nomor_opname,
                        'keterangan' => $opname->keterangan
                    ]);
                } else {
                    $produk = Produk::lockForUpdate()->find($detail->produk_id);
                    $stokSebelum = $produk->stok;
                    $produk->stok = $detail->stok_fisik; 
                    $produk->save();
    
                    $selisih = $detail->stok_fisik - $stokSebelum;
                    $detail->stok_sistem = $stokSebelum;
                    $detail->selisih = $selisih;
                    $detail->save();
    
                    RiwayatStok::create([
                        'produk_id' => $produk->id,
                        'bahan_baku_id' => null,
                        'jenis' => 'penyesuaian',
                        'jumlah' => $selisih,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $produk->stok,
                        'referensi' => $opname->nomor_opname,
                        'keterangan' => $opname->keterangan
                    ]);
                }
            }

            $opname->status = 'selesai';
            $opname->save();

            DB::commit();

            return response()->json([
                'message' => 'Stock opname berhasil diselesaikan',
                'data' => $opname
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyelesaikan stock opname'], 500);
        }
    }
}
