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
use Illuminate\Support\Facades\Storage;
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
            ->map(fn ($p) => $p->toPosPayload());

        return response()->json([
            'success' => true,
            'data' => $pesanans,
            'total_aktif' => $pesanans->count()
        ]);
    }

    public function ubahStatusPesanan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:menunggu_pembayaran,menunggu_konfirmasi,diproses,disajikan,selesai,batal'
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

    /**
     * Verifikasi & konfirmasi 1-klik bukti pembayaran QRIS/Transfer dari pelanggan.
     */
    public function konfirmasiBukti(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'nullable|string|in:tunai,cash,qris,debit,transfer'
        ]);

        try {
            $pesanan = \App\Models\Pesanan::with(['detailPesanan.produk', 'meja', 'pembayaran'])->findOrFail($id);

            if ($pesanan->status_pembayaran === 'dibayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ' . $pesanan->nomor_pesanan . ' sudah dikonfirmasi lunas sebelumnya.'
                ], 400);
            }

            $methodMap = [
                'tunai' => 'cash',
                'cash' => 'cash',
                'qris' => 'qris',
                'debit' => 'transfer',
                'transfer' => 'transfer',
            ];

            $requestedMethod = $request->input('payment_method') ?: ($pesanan->metode_pembayaran_customer ?: 'qris');
            $backendMethod = $methodMap[strtolower($requestedMethod)] ?? 'qris';

            $pembayaran = $this->pembayaranService->prosesPembayaran(
                $pesanan->id,
                $backendMethod,
                $pesanan->total_harga
            );

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran pesanan ' . $pesanan->nomor_pesanan . ' berhasil dikonfirmasi! Pesanan sedang dimasak.',
                'data' => [
                    'pesanan_id' => $pesanan->id,
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'transaksi' => $pembayaran->nomor_transaksi,
                    'metode_pembayaran' => $pembayaran->metode_pembayaran,
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

    /**
     * Tolak bukti pembayaran yang tidak valid / dana belum masuk agar pelanggan unggah ulang.
     */
    public function tolakBukti(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'nullable|string|max:255'
        ]);

        try {
            $pesanan = \App\Models\Pesanan::with('pembayaran')->findOrFail($id);

            if ($pesanan->status_pembayaran === 'dibayar') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan sudah berstatus lunas, tidak dapat menolak bukti.'
                ], 400);
            }

            if ($pesanan->bukti_pembayaran && Storage::disk('public')->exists($pesanan->bukti_pembayaran)) {
                Storage::disk('public')->delete($pesanan->bukti_pembayaran);
            }

            $alasan = trim((string) $request->input('alasan'));
            $pesanan->bukti_pembayaran = null;
            $pesanan->waktu_upload_bukti = null;
            $pesanan->catatan_tolak_bukti = $alasan !== ''
                ? $alasan
                : 'Bukti pembayaran tidak valid atau dana belum masuk mutasi. Silakan unggah ulang.';
            $pesanan->status = 'menunggu_pembayaran';
            $pesanan->save();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran pesanan ' . $pesanan->nomor_pesanan . ' ditolak. Pelanggan diminta mengunggah ulang.',
                'data' => [
                    'id' => $pesanan->id,
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'status' => $pesanan->status,
                    'catatan_tolak_bukti' => $pesanan->catatan_tolak_bukti
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tampilkan file gambar bukti pembayaran untuk Admin/Kasir tanpa bergantung symlink storage.
     */
    public function lihatBukti($id)
    {
        $pesanan = \App\Models\Pesanan::findOrFail($id);

        if (empty($pesanan->bukti_pembayaran)) {
            abort(404, 'Bukti pembayaran tidak ditemukan.');
        }

        $path = $pesanan->bukti_pembayaran;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return redirect()->away($path);
        }

        $cleanPath = ltrim(preg_replace('#^/?storage/#', '', $path), '/');
        if (!Storage::disk('public')->exists($cleanPath)) {
            abort(404, 'File bukti pembayaran tidak ditemukan di penyimpanan.');
        }

        return response()->file(Storage::disk('public')->path($cleanPath), [
            'Cache-Control' => 'private, max-age=300'
        ]);
    }
}
