<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiwayatStok;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StokController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatStok::with(['produk', 'bahanBaku', 'user']);

        if ($request->filled('tipe') && $request->tipe !== 'all') {
            $query->where('jenis', $request->tipe);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referensi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('produk', function($qp) use ($search) {
                      $qp->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bahanBaku', function($qb) use ($search) {
                      $qb->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $riwayats = $query->latest()->paginate(15)->withQueryString();
        $produks = \App\Models\Produk::where('aktif', true)->orderBy('nama')->get();
        $bahanBakus = \App\Models\BahanBaku::where('aktif', true)->orderBy('nama')->get();

        return view('admin.stok.index', compact('riwayats', 'produks', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_item' => 'required|in:produk,bahan_baku',
            'item_id' => 'required|integer',
            'tipe' => 'required|in:masuk,keluar,penyesuaian',
            'qty' => 'required|numeric|gt:0',
            'keterangan' => 'nullable|string|max:255'
        ], [
            'item_id.required' => 'Silakan pilih produk atau bahan baku.',
            'qty.required' => 'Jumlah Qty wajib diisi.',
            'qty.gt' => 'Jumlah Qty harus lebih dari 0.',
        ]);

        if ($validated['tipe_item'] == 'produk') {
            $model = \App\Models\Produk::find($validated['item_id']);
        } else {
            $model = \App\Models\BahanBaku::find($validated['item_id']);
        }

        if (!$model) {
            return redirect()->back()->with('error', 'Item yang dipilih tidak ditemukan.');
        }

        $stokSebelum = $model->stok;
        $qty = abs($validated['qty']);

        if ($validated['tipe'] == 'keluar') {
            if ($model->stok < $qty) {
                return redirect()->back()->with('error', "Stok {$model->nama} tidak mencukupi! Stok saat ini: {$model->stok}");
            }
            $model->stok -= $qty;
        } else {
            $model->stok += $qty;
        }
        $model->save();

        $riwayat = new RiwayatStok();
        $riwayat->jenis = $validated['tipe'];
        $riwayat->jumlah = $qty;
        $riwayat->stok_sebelum = $stokSebelum;
        $riwayat->stok_sesudah = $model->stok;
        $riwayat->keterangan = $validated['keterangan'] ?? '-';
        $riwayat->user_id = auth()->id();
        $riwayat->referensi = 'MANUAL-' . time();

        if ($validated['tipe_item'] == 'produk') {
            $riwayat->produk_id = $model->id;
        } else {
            $riwayat->bahan_baku_id = $model->id;
        }

        $riwayat->save();

        return redirect()->route('admin.stok.index')->with('success', "Mutasi stok {$model->nama} ({$validated['tipe']}) berhasil dicatat.");
    }

    private function buildQuery(Request $request)
    {
        $query = RiwayatStok::with(['produk', 'bahanBaku', 'user']);

        if ($request->filled('tipe') && $request->tipe !== 'all') {
            $query->where('jenis', $request->tipe);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referensi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('produk', fn($qp) => $qp->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('bahanBaku', fn($qb) => $qb->where('nama', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    public function exportExcel(Request $request)
    {
        $riwayats = $this->buildQuery($request)->latest()->get();

        $filename = 'mutasi-stok-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=".$filename,
        ];

        $callback = function () use ($riwayats) {
            $out = fopen('php://output', 'w');
            // BOM untuk Excel agar bisa baca UTF-8
            fputs($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Tanggal', 'No. Referensi', 'Tipe', 'Item', 'Jenis Item', 'Qty Mutasi', 'Stok Sebelum', 'Stok Sesudah', 'Satuan', 'Keterangan', 'User']);

            foreach ($riwayats as $r) {
                $item      = $r->produk ? $r->produk->nama : ($r->bahanBaku ? $r->bahanBaku->nama : '-');
                $jenisItem = $r->produk ? 'Produk' : ($r->bahanBaku ? 'Bahan Baku' : '-');
                $satuan    = $r->produk ? ($r->produk->satuan ?? 'Pcs') : ($r->bahanBaku ? $r->bahanBaku->satuan : '');
                $tipe      = ucfirst($r->jenis);
                $qty       = ($r->jenis == 'keluar' ? '-' : '+') . abs($r->jumlah);
                $user      = $r->user ? $r->user->name : 'Sistem/POS';

                fputcsv($out, [
                    $r->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i'),
                    $r->referensi ?? '-',
                    $tipe,
                    $item,
                    $jenisItem,
                    $qty,
                    $r->stok_sebelum,
                    $r->stok_sesudah,
                    $satuan,
                    $r->keterangan ?? '-',
                    $user,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $riwayats = $this->buildQuery($request)->latest()->get();
        $filters  = [
            'tipe'    => $request->tipe,
            'tanggal' => $request->tanggal,
            'search'  => $request->search,
        ];

        $html = view('admin.stok.pdf', compact('riwayats', 'filters'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'sans-serif');

        $filename = 'mutasi-stok-' . now()->format('Ymd-His') . '.pdf';
        return $pdf->download($filename);
    }
}
