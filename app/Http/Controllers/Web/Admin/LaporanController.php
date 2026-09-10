<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pesanan;
use App\Models\Pengeluaran;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->query('periode', 'today'); // today, week, month, year
        
        $queryPesanan = Pesanan::query();
        $queryPengeluaran = Pengeluaran::query();
        $chartTitle = 'Pendapatan Hari Ini';
        
        if ($periode == 'today') {
            $queryPesanan->whereDate('created_at', Carbon::today());
            $queryPengeluaran->whereDate('tanggal', Carbon::today());
            $chartTitle = 'Pendapatan Hari Ini';
        } elseif ($periode == 'week') {
            $queryPesanan->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $queryPengeluaran->whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $chartTitle = 'Pendapatan Minggu Ini';
        } elseif ($periode == 'month') {
            $queryPesanan->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year);
            $queryPengeluaran->whereMonth('tanggal', Carbon::now()->month)->whereYear('tanggal', Carbon::now()->year);
            $chartTitle = 'Pendapatan Bulan Ini';
        } elseif ($periode == 'year') {
            $queryPesanan->whereYear('created_at', Carbon::now()->year);
            $queryPengeluaran->whereYear('tanggal', Carbon::now()->year);
            $chartTitle = 'Pendapatan Tahun Ini';
        }

        $paidCondition = function ($q) {
            $q->whereHas('pembayaran', function ($p) {
                $p->where('status', 'berhasil');
            })->orWhereIn('status', ['dibayar', 'diproses', 'disajikan', 'selesai']);
        };

        $totalPendapatan = (clone $queryPesanan)->where($paidCondition)->sum('total_harga');
        $totalPesanan = (clone $queryPesanan)->where($paidCondition)->count();
        $totalPengeluaran = $queryPengeluaran->sum('nominal');

        $pesanans = (clone $queryPesanan)->where($paidCondition)->with(['detailPesanan', 'pembayaran'])->get();
        
        $totalHpp = 0;
        foreach ($pesanans as $pesanan) {
            foreach ($pesanan->detailPesanan as $detail) {
                $totalHpp += ($detail->hpp ?? 0) * $detail->jumlah;
            }
        }

        $totalLaba = ($totalPendapatan - $totalHpp) - $totalPengeluaran;

        // --- Chart Data ---
        
        // 1. Line Chart & Bar Chart (Grouping by date/hour)
        $chartLabels = [];
        $chartPendapatan = [];
        $chartPengeluaran = [];

        if ($periode == 'today') {
            // Group by hour
            $sales = $pesanans->groupBy(function($item) {
                return Carbon::parse($item->created_at)->format('H:00');
            });
            $expenses = clone $queryPengeluaran;
            $expenses = $expenses->get()->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->format('H:00');
            });
            
            for ($i = 9; $i <= 24; $i++) {
                $hourKey = sprintf('%02d:00', $i == 24 ? 0 : $i);
                $displayHour = $i == 24 ? '00:00' : sprintf('%02d:00', $i);
                
                $chartLabels[] = $displayHour;
                $chartPendapatan[] = isset($sales[$hourKey]) ? $sales[$hourKey]->sum('total_harga') : 0;
                $chartPengeluaran[] = isset($expenses[$hourKey]) ? $expenses[$hourKey]->sum('nominal') : 0;
            }
        } elseif ($periode == 'week') {
            // Group by day of week
            $sales = $pesanans->groupBy(function($item) {
                return Carbon::parse($item->created_at)->format('Y-m-d');
            });
            $expenses = clone $queryPengeluaran;
            $expenses = $expenses->get()->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->format('Y-m-d');
            });
            
            $start = Carbon::now()->startOfWeek();
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i)->format('Y-m-d');
                $chartLabels[] = $start->copy()->addDays($i)->translatedFormat('l'); // Senin, Selasa
                $chartPendapatan[] = isset($sales[$date]) ? $sales[$date]->sum('total_harga') : 0;
                $chartPengeluaran[] = isset($expenses[$date]) ? $expenses[$date]->sum('nominal') : 0;
            }
        } elseif ($periode == 'month') {
            // Group by day of month
            $sales = $pesanans->groupBy(function($item) {
                return Carbon::parse($item->created_at)->format('d');
            });
            $expenses = clone $queryPengeluaran;
            $expenses = $expenses->get()->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->format('d');
            });
            
            $daysInMonth = Carbon::now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $day = sprintf('%02d', $i);
                $chartLabels[] = $day;
                $chartPendapatan[] = isset($sales[$day]) ? $sales[$day]->sum('total_harga') : 0;
                $chartPengeluaran[] = isset($expenses[$day]) ? $expenses[$day]->sum('nominal') : 0;
            }
        } elseif ($periode == 'year') {
            // Group by month
            $sales = $pesanans->groupBy(function($item) {
                return Carbon::parse($item->created_at)->format('n');
            });
            $expenses = clone $queryPengeluaran;
            $expenses = $expenses->get()->groupBy(function($item) {
                return Carbon::parse($item->tanggal)->format('n');
            });
            
            for ($i = 1; $i <= 12; $i++) {
                $month = Carbon::create()->month($i)->translatedFormat('M');
                $chartLabels[] = $month;
                $chartPendapatan[] = isset($sales[$i]) ? $sales[$i]->sum('total_harga') : 0;
                $chartPengeluaran[] = isset($expenses[$i]) ? $expenses[$i]->sum('nominal') : 0;
            }
        }

        // 2. Donut Chart (Metode Pembayaran)
        $paymentMethodsCount = [
            'cash' => 0,
            'qris' => 0,
            'transfer' => 0
        ];
        
        $pembayarans = \App\Models\Pembayaran::whereIn('pesanan_id', $pesanans->pluck('id'))->get();
        foreach ($pembayarans as $p) {
            if (isset($paymentMethodsCount[$p->metode_pembayaran])) {
                $paymentMethodsCount[$p->metode_pembayaran]++;
            } else {
                $paymentMethodsCount['transfer']++; // default fallback for debit/etc
            }
        }
        
        $totalPayments = array_sum($paymentMethodsCount) ?: 1; // Prevent division by zero
        $paymentMethodsPercents = [
            'cash' => round(($paymentMethodsCount['cash'] / $totalPayments) * 100, 1),
            'qris' => round(($paymentMethodsCount['qris'] / $totalPayments) * 100, 1),
            'transfer' => round(($paymentMethodsCount['transfer'] / $totalPayments) * 100, 1),
        ];

        return view('admin.laporan.index', compact(
            'totalPendapatan', 'totalPesanan', 'totalPengeluaran', 'totalLaba', 
            'periode', 'chartTitle', 
            'chartLabels', 'chartPendapatan', 'chartPengeluaran',
            'paymentMethodsCount', 'paymentMethodsPercents'
        ));
    }
}
