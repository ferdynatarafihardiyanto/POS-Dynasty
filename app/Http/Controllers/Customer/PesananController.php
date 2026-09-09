<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\CafeTable;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class PesananController extends Controller
{
    public function buatPesanan(Request $request, \App\Services\PesananService $pesananService)
    {
        $request->validate([
            'qr_token' => 'required|string',
            'catatan' => 'nullable|string',
            'nama_pelanggan' => 'nullable|string|max:100',
            'produk' => 'required|array|min:1',
            'produk.*.produk_id' => 'required|integer',
            'produk.*.jumlah' => 'required|integer|min:1',
            'produk.*.catatan' => 'nullable|string'
        ], [
            'produk.required' => 'Pesanan tidak boleh kosong',
            'produk.min' => 'Pesanan tidak boleh kosong'
        ]);

        $meja = CafeTable::where('qr_token', $request->qr_token)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        if ($meja->status !== 'active') {
            return response()->json(['message' => 'Meja sedang tidak tersedia'], 422);
        }

        try {
            $catatanPesanan = $request->catatan;
            if ($request->filled('nama_pelanggan')) {
                $nama = trim($request->nama_pelanggan);
                if (!str_contains($catatanPesanan ?? '', 'Pemesan:')) {
                    $catatanPesanan = "Pemesan: {$nama}" . ($catatanPesanan ? " | {$catatanPesanan}" : "");
                }
            }

            $pesanan = $pesananService->buatPesanan($meja->id, $request->produk, $catatanPesanan);

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'meja' => $meja->table_number,
                    'status' => $pesanan->status,
                    'status_pembayaran' => $pesanan->status_pembayaran,
                    'total_harga' => $pesanan->total_harga,
                    'catatan' => $pesanan->catatan,
                    'detail' => $pesanan->detailPesanan->map(function ($d) {
                        return [
                            'nama_produk' => $d->nama_produk,
                            'harga' => $d->harga,
                            'jumlah' => $d->jumlah,
                            'subtotal' => $d->subtotal
                        ];
                    })
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function daftarPesanan(Request $request)
    {
        $qrToken = $request->query('qr_token');
        
        if (!$qrToken) {
            return response()->json(['message' => 'QR Token diperlukan'], 400);
        }

        $meja = CafeTable::where('qr_token', $qrToken)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $pesanans = Pesanan::where('meja_id', $meja->id)->get(['nomor_pesanan', 'status', 'status_pembayaran', 'total_harga']);

        return response()->json([
            'message' => 'Daftar pesanan berhasil diambil',
            'data' => $pesanans
        ]);
    }

    public function riwayatPesanan(Request $request)
    {
        $qrToken = $request->query('qr_token');
        
        if (!$qrToken) {
            return response()->json(['message' => 'QR Token diperlukan'], 400);
        }

        $meja = CafeTable::where('qr_token', $qrToken)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $orderNumbersParam = $request->query('order_numbers');
        $deviceOrderNumbers = [];
        if ($orderNumbersParam) {
            $deviceOrderNumbers = is_array($orderNumbersParam)
                ? $orderNumbersParam
                : array_filter(array_map('trim', explode(',', $orderNumbersParam)));
        }

        // Keamanan & Privasi: Jika perangkat belum pernah memesan, jangan tampilkan pesanan orang lain!
        if (empty($deviceOrderNumbers)) {
            return response()->json([
                'message' => 'Belum ada riwayat pesanan untuk perangkat ini',
                'data' => []
            ]);
        }

        $pesanans = Pesanan::with(['detailPesanan', 'pembayaran'])
            ->where('meja_id', $meja->id)
            ->whereIn('nomor_pesanan', $deviceOrderNumbers)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($p) use ($meja) {
                return [
                    'id' => $p->id,
                    'nomor_pesanan' => $p->nomor_pesanan,
                    'meja' => $meja->table_number,
                    'status' => $p->status,
                    'status_pembayaran' => $p->status_pembayaran,
                    'metode_pembayaran' => $p->pembayaran ? $p->pembayaran->metode_pembayaran : null,
                    'total_harga' => $p->total_harga,
                    'catatan' => $p->catatan,
                    'created_at' => $p->created_at ? $p->created_at->toISOString() : null,
                    'items' => $p->detailPesanan->map(function ($d) {
                        return [
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
            'message' => 'Riwayat pesanan meja berhasil diambil',
            'data' => $pesanans
        ]);
    }

    public function detailPesanan(Request $request, $nomor_pesanan)
    {
        $qrToken = $request->query('qr_token');
        
        if (!$qrToken) {
            return response()->json(['message' => 'QR Token diperlukan'], 400);
        }

        $meja = CafeTable::where('qr_token', $qrToken)->first();

        if (!$meja) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }

        $pesanan = Pesanan::with(['detailPesanan', 'pembayaran'])
            ->where('nomor_pesanan', $nomor_pesanan)
            ->first();

        if (!$pesanan) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($pesanan->meja_id !== $meja->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke pesanan ini'], 403);
        }

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil',
            'data' => [
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'meja' => $meja->table_number,
                'status' => $pesanan->status,
                'status_pembayaran' => $pesanan->status_pembayaran,
                'metode_pembayaran' => $pesanan->pembayaran ? $pesanan->pembayaran->metode_pembayaran : null,
                'total_harga' => $pesanan->total_harga,
                'catatan' => $pesanan->catatan,
                'created_at' => $pesanan->created_at ? $pesanan->created_at->toISOString() : null,
                'detail' => $pesanan->detailPesanan->map(function ($d) {
                    return [
                        'nama_produk' => $d->nama_produk,
                        'harga' => $d->harga,
                        'jumlah' => $d->jumlah,
                        'subtotal' => $d->subtotal,
                        'catatan' => $d->catatan,
                        'modifiers' => $d->modifiers_snapshot ? json_decode($d->modifiers_snapshot, true) : []
                    ];
                })
            ]
        ]);
    }

    public function bayarOnline(Request $request, $nomor_pesanan, \App\Services\PembayaranService $pembayaranService)
    {
        $nomor_pesanan = ltrim($nomor_pesanan, '#');

        $request->validate([
            'qr_token' => 'nullable|string',
            'metode_pembayaran' => 'required|string|in:qris,transfer,debit',
            'nama_pelanggan' => 'nullable|string|max:100'
        ]);

        $pesanan = Pesanan::with(['detailPesanan.produk', 'meja'])
            ->where('nomor_pesanan', $nomor_pesanan)
            ->first();

        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan'], 404);
        }

        if ($request->filled('nama_pelanggan')) {
            $namaPelanggan = trim($request->nama_pelanggan);
            $currentCatatan = $pesanan->catatan ?? '';
            if (empty($currentCatatan) || str_starts_with($currentCatatan, 'Pesanan Meja')) {
                $pesanan->catatan = $namaPelanggan;
            } elseif (!str_contains($currentCatatan, $namaPelanggan)) {
                $pesanan->catatan = $namaPelanggan . ' - ' . $currentCatatan;
            }
            $pesanan->save();
        }

        if ($request->filled('qr_token')) {
            $meja = CafeTable::where('qr_token', $request->qr_token)->first();
            if ($meja && $pesanan->meja_id && $pesanan->meja_id !== $meja->id) {
                // Log and keep proceeding if order number is authenticated by the client
                \Log::info("bayarOnline: Token mismatch for order {$nomor_pesanan}, proceeding with order's table.");
            }
        }

        if ($pesanan->status_pembayaran === 'dibayar') {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan sudah lunas dibayar',
                'data' => [
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'status' => $pesanan->status,
                    'status_pembayaran' => 'dibayar'
                ]
            ]);
        }

        try {
            $metode = in_array($request->metode_pembayaran, ['qris', 'transfer']) ? $request->metode_pembayaran : 'transfer';
            $pembayaran = $pembayaranService->prosesPembayaran($pesanan->id, $metode, $pesanan->total_harga);

            $pesanan->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran ' . strtoupper($metode) . ' berhasil dikonfirmasi! Pesanan Anda segera disiapkan.',
                'data' => [
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                    'nomor_transaksi' => $pembayaran->nomor_transaksi,
                    'metode_pembayaran' => $pembayaran->metode_pembayaran,
                    'customerName' => $request->nama_pelanggan ?? null,
                    'nama_pelanggan' => $request->nama_pelanggan ?? null,
                    'total_harga' => $pesanan->total_harga,
                    'status' => $pesanan->status,
                    'status_pembayaran' => 'dibayar'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getProfilToko()
    {
        $filePath = storage_path('app/store_profile.json');
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);
            if ($data) {
                return response()->json(['success' => true, 'data' => $data]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'namaToko' => 'Dynasty Cafe',
                'slogan' => 'Authentic Coffee & Eatery',
                'telepon' => '0812-3456-7890',
                'sosmed' => '@dynastycafe.id',
                'alamat' => 'Jl. Mawar No. 123, Jakarta Selatan',
                'pesanFooterStruk' => "Terima kasih atas kunjungan Anda!\nSilakan datang kembali.",
                'cetakLogoStruk' => true,
                'wifiList' => [
                    ['ssid' => 'Dynasty Cafe Free (Lt. 1)', 'password' => 'kedaidynasty123'],
                    ['ssid' => 'Dynasty Cafe VIP (Lt. 2)', 'password' => 'dynastyvip88']
                ]
            ]
        ]);
    }

    public function saveProfilToko(Request $request)
    {
        $data = $request->all();

        // Handle uploaded logo file if provided
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = 'store_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/logos', $filename);
            $data['logo_url'] = '/storage/logos/' . $filename;
        }

        $filePath = storage_path('app/store_profile.json');
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'success' => true,
            'message' => 'Profil dan data struk berhasil disimpan',
            'data' => $data
        ]);
    }
}
