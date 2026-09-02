<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\RiwayatStok;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function daftarStok(Request $request)
    {
        $tipe = $request->query('tipe', 'produk');
        
        if ($tipe === 'bahan_baku') {
            $data = BahanBaku::select('id', 'nama', 'stok', 'satuan', 'stok_minimum')->get()->map(function($b) {
                $status = 'AMAN';
                if ($b->stok == 0) {
                    $status = 'HABIS';
                } elseif ($b->stok <= $b->stok_minimum) {
                    $status = 'STOK MENIPIS';
                }
                
                return [
                    'id' => $b->id,
                    'nama_bahan_baku' => $b->nama,
                    'stok' => $b->stok,
                    'satuan' => $b->satuan,
                    'stok_minimum' => $b->stok_minimum,
                    'status' => $status
                ];
            });
        } else {
            $data = Produk::select('id', 'nama', 'stok')->get()->map(function($p) {
                return [
                    'id' => $p->id,
                    'nama_produk' => $p->nama,
                    'stok' => $p->stok
                ];
            });
        }

        return response()->json([
            'message' => 'Data stok berhasil diambil',
            'data' => $data
        ]);
    }

    public function detailStok(Request $request, $id)
    {
        $tipe = $request->query('tipe', 'produk');
        
        if ($tipe === 'bahan_baku') {
            $item = BahanBaku::find($id);
            if (!$item) return response()->json(['message' => 'Bahan baku tidak ditemukan'], 404);
            
            $status = 'AMAN';
            if ($item->stok == 0) $status = 'HABIS';
            elseif ($item->stok <= $item->stok_minimum) $status = 'STOK MENIPIS';
                
            return response()->json([
                'message' => 'Detail stok berhasil diambil',
                'data' => [
                    'id' => $item->id,
                    'nama_bahan_baku' => $item->nama,
                    'stok' => $item->stok,
                    'satuan' => $item->satuan,
                    'stok_minimum' => $item->stok_minimum,
                    'status' => $status
                ]
            ]);
        } else {
            $item = Produk::find($id);
            if (!$item) return response()->json(['message' => 'Produk tidak ditemukan'], 404);
            
            return response()->json([
                'message' => 'Detail stok berhasil diambil',
                'data' => [
                    'id' => $item->id,
                    'nama_produk' => $item->nama,
                    'stok' => $item->stok
                ]
            ]);
        }
    }

    public function stokMasuk(Request $request)
    {
        $request->validate([
            'produk_id' => 'required_without:bahan_baku_id|exists:produk,id',
            'bahan_baku_id' => 'required_without:produk_id|exists:bahan_baku,id',
            'jumlah' => 'required|numeric|min:0.001',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            if ($request->bahan_baku_id) {
                $bahanBaku = BahanBaku::lockForUpdate()->find($request->bahan_baku_id);
                $stokSebelum = $bahanBaku->stok;
                $bahanBaku->stok += $request->jumlah;
                $bahanBaku->save();

                RiwayatStok::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'produk_id' => null,
                    'jenis' => 'masuk',
                    'jumlah' => $request->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $bahanBaku->stok,
                    'keterangan' => $request->keterangan
                ]);
                $responseData = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'stok_sekarang' => $bahanBaku->stok
                ];
            } else {
                $produk = Produk::lockForUpdate()->find($request->produk_id);
                $stokSebelum = $produk->stok;
                $produk->stok += $request->jumlah;
                $produk->save();

                RiwayatStok::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => null,
                    'jenis' => 'masuk',
                    'jumlah' => $request->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $produk->stok,
                    'keterangan' => $request->keterangan
                ]);
                $responseData = [
                    'produk_id' => $produk->id,
                    'stok_sekarang' => $produk->stok
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Stok berhasil ditambahkan',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menambah stok'], 500);
        }
    }

    public function stokKeluar(Request $request)
    {
        $request->validate([
            'produk_id' => 'required_without:bahan_baku_id|exists:produk,id',
            'bahan_baku_id' => 'required_without:produk_id|exists:bahan_baku,id',
            'jumlah' => 'required|numeric|min:0.001',
            'keterangan' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            if ($request->bahan_baku_id) {
                $bahanBaku = BahanBaku::lockForUpdate()->find($request->bahan_baku_id);
                if ($bahanBaku->stok < $request->jumlah) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok bahan baku tidak mencukupi'], 422);
                }
                
                $stokSebelum = $bahanBaku->stok;
                $bahanBaku->stok -= $request->jumlah;
                $bahanBaku->save();

                RiwayatStok::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'produk_id' => null,
                    'jenis' => 'keluar',
                    'jumlah' => $request->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $bahanBaku->stok,
                    'keterangan' => $request->keterangan
                ]);
                $responseData = [
                    'bahan_baku_id' => $bahanBaku->id,
                    'stok_sekarang' => $bahanBaku->stok
                ];
            } else {
                $produk = Produk::lockForUpdate()->find($request->produk_id);
                if ($produk->stok < $request->jumlah) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok produk tidak mencukupi'], 422);
                }
                
                $stokSebelum = $produk->stok;
                $produk->stok -= $request->jumlah;
                $produk->save();

                RiwayatStok::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => null,
                    'jenis' => 'keluar',
                    'jumlah' => $request->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $produk->stok,
                    'keterangan' => $request->keterangan
                ]);
                $responseData = [
                    'produk_id' => $produk->id,
                    'stok_sekarang' => $produk->stok
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Stok berhasil dikurangi',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengurangi stok'], 500);
        }
    }

    public function riwayatStok()
    {
        $riwayat = RiwayatStok::with(['produk', 'bahanBaku'])->latest()->get()->map(function($r) {
            $item = $r->bahan_baku_id ? ($r->bahanBaku->nama ?? null) : ($r->produk->nama ?? null);
            $tipe = $r->bahan_baku_id ? 'bahan_baku' : 'produk';
            
            return [
                'id' => $r->id,
                'tipe_item' => $tipe,
                'item_id' => $r->bahan_baku_id ?? $r->produk_id,
                'nama_item' => $item,
                'jenis' => $r->jenis,
                'jumlah' => $r->jumlah,
                'stok_sebelum' => $r->stok_sebelum,
                'stok_sesudah' => $r->stok_sesudah,
                'referensi' => $r->referensi,
                'keterangan' => $r->keterangan,
                'tanggal' => $r->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'message' => 'Riwayat stok berhasil diambil',
            'data' => $riwayat
        ]);
    }
}
