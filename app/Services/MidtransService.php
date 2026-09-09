<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MidtransService
{
    protected $pembayaranService;

    public function __construct(PembayaranService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
    }

    /**
     * Membuat Snap Token untuk Pesanan Meja
     */
    public function createSnapToken(Pesanan $pesanan, ?string $customerName = null): array
    {
        $serverKey = config('midtrans.server_key');
        $snapUrl = config('midtrans.snap_url');

        if (empty($serverKey)) {
            throw new Exception('Midtrans Server Key belum dikonfigurasi di file .env.');
        }

        $pesanan->loadMissing(['detailPesanan.produk', 'meja']);

        $cleanCustomerName = trim($customerName ?: '');
        if (empty($cleanCustomerName)) {
            // Ambil dari catatan pesanan jika ada format "Pemesan: Nama"
            if (!empty($pesanan->catatan) && preg_match('/Pemesan:\s*([^|]+)/i', $pesanan->catatan, $m)) {
                $cleanCustomerName = trim($m[1]);
            } else {
                $cleanCustomerName = 'Pelanggan Meja ' . ($pesanan->meja ? $pesanan->meja->table_number : '-');
            }
        }

        // Susun item details
        $itemDetails = [];
        $totalItemsPrice = 0;

        foreach ($pesanan->detailPesanan as $detail) {
            $itemPrice = (int) round($detail->harga);
            $qty = (int) $detail->jumlah;
            $subtotal = $itemPrice * $qty;
            $totalItemsPrice += $subtotal;

            $itemDetails[] = [
                'id' => 'ITEM-' . $detail->id,
                'price' => $itemPrice,
                'quantity' => $qty,
                'name' => mb_substr($detail->nama_produk ?? 'Menu Kafe', 0, 45),
            ];
        }

        // Pastikan total item_details sama persis dengan total_harga pesanan
        $grandTotal = (int) round($pesanan->total_harga);
        $diff = $grandTotal - $totalItemsPrice;
        if ($diff !== 0) {
            $itemDetails[] = [
                'id' => 'ADJ-1',
                'price' => $diff,
                'quantity' => 1,
                'name' => 'Biaya Layanan / Pembulatan',
            ];
        }

        // Nomor transaksi unik untuk Midtrans (dapat dipanggil ulang jika gagal/batal)
        $midtransOrderId = $pesanan->nomor_pesanan . '-' . time();

        $payload = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $grandTotal,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $cleanCustomerName,
                'email' => 'customer@dynastycafe.id',
                'phone' => '081234567890',
            ],
            'callbacks' => [
                'finish' => url('/'),
            ]
        ];

        Log::info('Midtrans Snap Request:', ['order_id' => $midtransOrderId, 'total' => $grandTotal]);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withBasicAuth($serverKey, '')
          ->post($snapUrl, $payload);

        if (!$response->successful()) {
            $errorMsg = $response->json('error_messages.0') ?? $response->body();
            Log::error('Midtrans Snap Error:', ['status' => $response->status(), 'body' => $response->body()]);
            throw new Exception('Gagal membuat transaksi Midtrans: ' . $errorMsg);
        }

        $result = $response->json();

        return [
            'snap_token' => $result['token'],
            'redirect_url' => $result['redirect_url'] ?? null,
            'midtrans_order_id' => $midtransOrderId,
            'client_key' => config('midtrans.client_key'),
        ];
    }

    /**
     * Memproses notifikasi Webhook dari Midtrans
     */
    public function handleNotification(array $payload): array
    {
        $serverKey = config('midtrans.server_key');
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            throw new Exception('Data notifikasi Midtrans tidak lengkap.');
        }

        // Verifikasi keaslian Signature SHA512
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($expectedSignature !== $signatureKey) {
            Log::warning('Midtrans Invalid Signature:', [
                'received' => $signatureKey,
                'expected' => $expectedSignature,
                'order_id' => $orderId
            ]);
            throw new Exception('Signature Key Midtrans tidak valid.');
        }

        // Cari pesanan berdasarkan nomor pesanan (baik persis atau dipisahkan tanda -)
        $pesanan = Pesanan::where('nomor_pesanan', $orderId)->first();
        if (!$pesanan) {
            $parts = explode('-', $orderId);
            // Contoh ORD-20260909-001-1725...
            if (count($parts) >= 3) {
                $baseOrderNumber = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
                $pesanan = Pesanan::where('nomor_pesanan', $baseOrderNumber)->first();
            }
        }

        if (!$pesanan) {
            Log::error("Pesanan dengan ID Midtrans {$orderId} tidak ditemukan di database.");
            throw new Exception("Pesanan {$orderId} tidak ditemukan.");
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? 'qris';

        Log::info("Midtrans Webhook: Pesanan {$pesanan->nomor_pesanan}, Status: {$transactionStatus}, Type: {$paymentType}");

        // Pemetaan metode bayar ke sistem POS ('qris' atau 'transfer')
        $posPaymentMethod = 'qris';
        if (in_array($paymentType, ['bank_transfer', 'echannel', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'cimb_va'])) {
            $posPaymentMethod = 'transfer';
        }

        $isSuccess = false;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $isSuccess = true;
            }
        } elseif ($transactionStatus == 'settlement') {
            $isSuccess = true;
        }

        if ($isSuccess) {
            if ($pesanan->status_pembayaran === 'dibayar') {
                return [
                    'success' => true,
                    'message' => 'Pesanan sudah berstatus lunas sebelumnya.'
                ];
            }

            // Proses pelunasan, pengurangan stok bahan, dan perhitungan HPP
            $this->pembayaranService->prosesPembayaran(
                $pesanan->id,
                $posPaymentMethod,
                $pesanan->total_harga
            );

            return [
                'success' => true,
                'message' => 'Pembayaran Midtrans berhasil diverifikasi dan pesanan diproses.'
            ];
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            Log::info("Pesanan {$pesanan->nomor_pesanan} pembayaran dibatalkan/kadaluarsa oleh Midtrans.");
        }

        return [
            'success' => true,
            'message' => 'Notifikasi Midtrans tercatat dengan status: ' . $transactionStatus
        ];
    }
}
