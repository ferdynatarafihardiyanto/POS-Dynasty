<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\CafeTable;
use App\Services\PesananService;
use App\Services\PembayaranService;
use App\Http\Requests\StorePosOrderRequest;
use Illuminate\Http\Request;
use Exception;

class POSController extends Controller
{
    protected $pesananService;
    protected $pembayaranService;

    public function __construct(PesananService $pesananService, PembayaranService $pembayaranService)
    {
        $this->pesananService = $pesananService;
        $this->pembayaranService = $pembayaranService;
    }

    public function index()
    {
        $kategoris = Kategori::where('aktif', true)->get();
        $produks = Produk::with(['kategori', 'modifierGroups' => function($q) {
            $q->where('modifier_groups.aktif', true)
              ->with(['options' => function($q2) {
                  $q2->where('aktif', true);
              }]);
        }])->where('aktif', true)->get();
        $tables = CafeTable::where('status', 'active')->get();

        return view('admin.pos.index', compact('kategoris', 'produks', 'tables'));
    }

    public function checkout(StorePosOrderRequest $request)
    {
        try {
            $pesanan = $this->pesananService->buatPesanan(
                $request->meja_id, 
                $request->items, 
                $request->catatan
            );

            // Map payment method from frontend (tunai/debit) to backend (cash/transfer)
            $methodMap = [
                'tunai' => 'cash',
                'qris' => 'qris',
                'debit' => 'transfer'
            ];
            $backendMethod = $methodMap[$request->payment_method] ?? $request->payment_method;

            // Proses pembayaran langsung karena ini POS
            $pembayaran = $this->pembayaranService->prosesPembayaran(
                $pesanan->id,
                $backendMethod,
                $request->cash_received
            );

            return response()->json([
                'success' => true,
                'message' => 'Pesanan dan pembayaran berhasil diproses.',
                'data' => [
                    'pesanan_id' => $pesanan->id,
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'transaksi' => $pembayaran->nomor_transaksi
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400); // Bad Request for business logic errors
        }
    }

    public function pesananAktif()
    {
        $pesanans = \App\Models\Pesanan::with(['meja', 'detailPesanan', 'pembayaran'])
            ->whereIn('status', ['menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses', 'disajikan'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) {
                $rawCatatan = $p->catatan;
                $catatanKhusus = '';
                if (!empty($rawCatatan)) {
                    if (preg_match('/^Pemesan:\s*[^|]+(?:\s*\|\s*(.*))?$/i', $rawCatatan, $m)) {
                        $catatanKhusus = isset($m[1]) ? trim($m[1]) : '';
                    } elseif (!str_starts_with($rawCatatan, 'Pesanan Meja')) {
                        if (str_contains($rawCatatan, ' - ')) {
                            $parts = explode(' - ', $rawCatatan, 2);
                            $catatanKhusus = trim($parts[1]);
                        }
                    }
                }

                return [
                    'id' => $p->id,
                    'nomor_pesanan' => $p->nomor_pesanan,
                    'meja_id' => $p->meja_id,
                    'meja_nomor' => $p->meja ? ($p->meja->table_number ? str_pad($p->meja->table_number, 2, '0', STR_PAD_LEFT) : $p->meja->id) : '-',
                    'meja_nama' => $p->meja ? ($p->meja->name ?? ('Meja ' . $p->meja->table_number)) : 'Meja Umum',
                    'nama_pelanggan' => $p->nama_pelanggan,
                    'catatan_khusus' => $catatanKhusus,
                    'status' => $p->status,
                    'status_pembayaran' => $p->status_pembayaran,
                    'metode_pembayaran' => $p->pembayaran ? $p->pembayaran->metode_pembayaran : null,
                    'total_harga' => $p->total_harga,
                    'catatan' => $p->catatan,
                    'waktu' => $p->created_at ? $p->created_at->timezone('Asia/Jakarta')->format('H:i') : '-',
                    'created_at' => $p->created_at ? $p->created_at->toISOString() : null,
                    'items' => $p->detailPesanan->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'produk_id' => $d->produk_id,
                            'nama_produk' => $d->nama_produk,
                            'harga' => $d->harga,
                            'jumlah' => $d->jumlah,
                            'subtotal' => $d->subtotal,
                            'catatan' => $d->catatan,
                            'modifiers' => $d->modifiers_snapshot ? json_decode($d->modifiers_snapshot, true) : []
                        ];
                    })
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pesanans,
            'total_aktif' => $pesanans->count()
        ]);
    }

    public function ubahStatusPesanan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:menunggu_pembayaran,diproses,disajikan,selesai,batal'
        ]);

        $pesanan = \App\Models\Pesanan::with(['meja', 'detailPesanan'])->find($id);

        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        $pesanan->status = $request->status;
        $pesanan->save();

        $statusLabels = [
            'diproses' => 'Sedang Dimasak di Dapur',
            'disajikan' => 'Sudah Dikirim / Disajikan ke Meja',
            'selesai' => 'Pesanan Selesai',
            'batal' => 'Pesanan Dibatalkan'
        ];

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan ' . $pesanan->nomor_pesanan . ' berhasil diubah menjadi: ' . ($statusLabels[$request->status] ?? $request->status),
            'data' => [
                'id' => $pesanan->id,
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'status' => $pesanan->status
            ]
        ]);
    }

    public function bayarPesananMeja(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string|in:tunai,cash,qris,debit,transfer',
            'cash_received' => 'nullable|numeric'
        ]);

        try {
            $pesanan = \App\Models\Pesanan::with(['detailPesanan.produk', 'meja'])->findOrFail($id);

            $methodMap = [
                'tunai' => 'cash',
                'qris' => 'qris',
                'debit' => 'transfer'
            ];
            $backendMethod = $methodMap[$request->payment_method] ?? $request->payment_method;
            $cashReceived = $request->cash_received ?? $pesanan->total_harga;

            $pembayaran = $this->pembayaranService->prosesPembayaran(
                $pesanan->id,
                $backendMethod,
                $cashReceived
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran pesanan ' . $pesanan->nomor_pesanan . ' berhasil.',
                'data' => [
                    'pesanan_id' => $pesanan->id,
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'transaksi' => $pembayaran->nomor_transaksi,
                    'kembalian' => $pembayaran->kembalian,
                    'total_harga' => $pesanan->total_harga
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
