<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\MidtransService;
use App\Services\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class MidtransController extends Controller
{
    protected $midtransService;
    protected $pembayaranService;

    public function __construct(MidtransService $midtransService, PembayaranService $pembayaranService)
    {
        $this->midtransService = $midtransService;
        $this->pembayaranService = $pembayaranService;
    }

    /**
     * Dapatkan Snap Token Midtrans untuk pesanan meja customer
     */
    public function createSnapToken(Request $request, $nomor_pesanan)
    {
        $nomor_pesanan = urldecode(ltrim($nomor_pesanan, '#'));

        $pesanan = Pesanan::with(['detailPesanan.produk', 'meja'])
            ->where('nomor_pesanan', $nomor_pesanan)
            ->orWhere('nomor_pesanan', '#' . $nomor_pesanan)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ' . $nomor_pesanan . ' tidak ditemukan'
            ], 404);
        }

        if ($pesanan->status_pembayaran === 'dibayar') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibayar lunas sebelumnya'
            ], 400);
        }

        $namaPelanggan = $request->input('nama_pelanggan');
        if ($namaPelanggan) {
            $namaPelanggan = trim($namaPelanggan);
            $currentCatatan = $pesanan->catatan ?? '';
            if (preg_match('/^Pemesan:\s*([^|]+)(?:\s*\|\s*(.*))?$/i', $currentCatatan, $matches)) {
                $extraNote = isset($matches[2]) ? trim($matches[2]) : '';
                $pesanan->catatan = "Pemesan: {$namaPelanggan}" . ($extraNote ? " | {$extraNote}" : "");
            } elseif (empty($currentCatatan) || str_starts_with($currentCatatan, 'Pesanan Meja')) {
                $pesanan->catatan = "Pemesan: {$namaPelanggan}";
            } elseif (!str_contains($currentCatatan, $namaPelanggan)) {
                $pesanan->catatan = "Pemesan: {$namaPelanggan} | {$currentCatatan}";
            }
            $pesanan->save();
        }

        try {
            $snapData = $this->midtransService->createSnapToken($pesanan, $namaPelanggan);

            return response()->json([
                'success' => true,
                'message' => 'Snap Token berhasil dibuat',
                'data' => $snapData
            ]);
        } catch (Exception $e) {
            Log::error('Error generating Midtrans Snap Token:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook listener resmi Midtrans (HTTP POST callback)
     */
    public function handleNotification(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Midtrans Webhook endpoint is active and listening.'
            ], 200);
        }

        try {
            $payload = $request->all();
            Log::info('Midtrans Webhook Received:', $payload);

            // Deteksi tes ping probe dari dashboard Midtrans
            $orderId = $payload['order_id'] ?? '';
            $signature = $payload['signature_key'] ?? '';
            if (empty($signature) || str_contains((string)$orderId, 'payment_notif_test')) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Test notification received successfully'
                ], 200);
            }

            $result = $this->midtransService->handleNotification($payload);

            return response()->json($result, 200);
        } catch (Exception $e) {
            Log::error('Midtrans Webhook Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * Konfirmasi instan saat transaksi sukses di popup Midtrans (Fallback sinkron)
     */
    public function confirmSuccess(Request $request, $nomor_pesanan)
    {
        $nomor_pesanan = ltrim($nomor_pesanan, '#');

        $pesanan = Pesanan::where('nomor_pesanan', $nomor_pesanan)->first();
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($pesanan->status_pembayaran === 'dibayar') {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan sudah lunas',
                'data' => [
                    'status' => $pesanan->status,
                    'status_pembayaran' => 'dibayar'
                ]
            ]);
        }

        try {
            $paymentType = $request->input('payment_type', 'qris');
            $method = in_array($paymentType, ['bank_transfer', 'echannel']) ? 'transfer' : 'qris';

            $pembayaran = $this->pembayaranService->prosesPembayaran(
                $pesanan->id,
                $method,
                $pesanan->total_harga
            );

            $pesanan->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran Midtrans terkonfirmasi!',
                'data' => [
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'status' => $pesanan->status,
                    'status_pembayaran' => 'dibayar',
                    'nomor_transaksi' => $pembayaran->nomor_transaksi
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
