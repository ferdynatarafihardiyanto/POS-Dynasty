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
}
