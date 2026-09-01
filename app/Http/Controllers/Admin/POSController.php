<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class POSController extends Controller
{
    public function daftarPesanan()
    {
        $pesanans = Pesanan::with('meja')
            ->where('status', 'menunggu_pembayaran')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nomor_pesanan' => $p->nomor_pesanan,
                    'meja' => $p->meja->table_number ?? null,
                    'total_harga' => $p->total_harga,
                    'status' => $p->status
                ];
            });

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $pesanans
        ]);
    }

    public function detailPesanan($id)
    {
        $pesanan = Pesanan::with(['meja', 'detailPesanan'])->find($id);

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil',
            'data' => [
                'id' => $pesanan->id,
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'meja' => $pesanan->meja->table_number ?? null,
                'status' => $pesanan->status,
                'total_harga' => $pesanan->total_harga,
                'catatan' => $pesanan->catatan,
                'tanggal' => $pesanan->created_at,
                'detail' => $pesanan->detailPesanan->map(function ($d) {
                    return [
                        'nama_produk' => $d->nama_produk,
                        'harga' => $d->harga,
                        'jumlah' => $d->jumlah,
                        'subtotal' => $d->subtotal
                    ];
                })
            ]
        ]);
    }

    public function prosesPembayaran(Request $request)
    {
        $request->validate([
            'pesanan_id' => 'required|integer',
            'metode_pembayaran' => 'required|string|in:cash,qris',
            'jumlah_bayar' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            $pesanan = Pesanan::lockForUpdate()->find($request->pesanan_id);

            if (!$pesanan) {
                DB::rollBack();
                return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
            }

            if ($pesanan->status === 'dibayar') {
                DB::rollBack();
                return response()->json(['message' => 'Pesanan sudah dibayar'], 422);
            }

            if ($pesanan->status !== 'menunggu_pembayaran') {
                DB::rollBack();
                return response()->json(['message' => 'Pesanan tidak dapat dibayar'], 422);
            }

            // Validasi Stok
            foreach ($pesanan->detailPesanan as $detail) {
                $produk = \App\Models\Produk::lockForUpdate()->find($detail->produk_id);
                if ($produk->stok < $detail->jumlah) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok produk tidak mencukupi'], 422);
                }
            }

            $kembalian = 0;

            if ($request->metode_pembayaran === 'cash') {
                if ($request->jumlah_bayar < $pesanan->total_harga) {
                    DB::rollBack();
                    return response()->json(['message' => 'Jumlah pembayaran tidak mencukupi'], 422);
                }
                $kembalian = $request->jumlah_bayar - $pesanan->total_harga;
            } else if ($request->metode_pembayaran === 'qris') {
                if ($request->jumlah_bayar !== $pesanan->total_harga) {
                    DB::rollBack();
                    return response()->json(['message' => 'Jumlah pembayaran QRIS harus sesuai dengan total pesanan'], 422);
                }
                $kembalian = 0;
            }

            $nomorTransaksi = 'TRX-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $pembayaran = Pembayaran::create([
                'pesanan_id' => $pesanan->id,
                'nomor_transaksi' => $nomorTransaksi,
                'metode_pembayaran' => $request->metode_pembayaran,
                'jumlah_bayar' => $request->jumlah_bayar,
                'kembalian' => $kembalian,
                'status' => 'berhasil',
                'dibayar_pada' => now()
            ]);

            $pesanan->status = 'dibayar';
            $pesanan->save();

            // Kurangi Stok & Buat Riwayat
            foreach ($pesanan->detailPesanan as $detail) {
                $produk = \App\Models\Produk::find($detail->produk_id);
                $stokSebelum = $produk->stok;
                $produk->stok -= $detail->jumlah;
                $produk->save();

                \App\Models\RiwayatStok::create([
                    'produk_id' => $produk->id,
                    'jenis' => 'keluar',
                    'jumlah' => $detail->jumlah,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $produk->stok,
                    'referensi' => $pesanan->nomor_pesanan,
                    'keterangan' => 'Pesanan dibayar'
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => $request->metode_pembayaran === 'qris' ? 'Pembayaran QRIS berhasil dikonfirmasi' : 'Pembayaran berhasil',
                'data' => [
                    'nomor_transaksi' => $pembayaran->nomor_transaksi,
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'metode_pembayaran' => $pembayaran->metode_pembayaran,
                    'total_harga' => $pesanan->total_harga,
                    'jumlah_bayar' => $pembayaran->jumlah_bayar,
                    'kembalian' => $pembayaran->kembalian,
                    'status' => $pembayaran->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses pembayaran'], 500);
        }
    }
}
