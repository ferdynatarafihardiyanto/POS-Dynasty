<?php

namespace App\Services;

use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * Dapatkan laporan harian berdasarkan tanggal.
     */
    public function getLaporanHarian($tanggal)
    {
        $query = Pembayaran::with(['pesanan.detailPesanan'])
            ->where('status', 'berhasil')
            ->whereDate('dibayar_pada', $tanggal);

        return $this->aggregateLaporan($query, $tanggal, 'tanggal');
    }

    /**
     * Dapatkan laporan bulanan berdasarkan bulan (YYYY-MM).
     */
    public function getLaporanBulanan($bulan)
    {
        $parts = explode('-', $bulan);
        $query = Pembayaran::with(['pesanan.detailPesanan'])
            ->where('status', 'berhasil')
            ->whereYear('dibayar_pada', $parts[0])
            ->whereMonth('dibayar_pada', $parts[1]);

        return $this->aggregateLaporan($query, $bulan, 'bulan');
    }

    /**
     * Dapatkan trend penjualan harian untuk sebuah bulan (YYYY-MM).
     */
    public function getTrenPenjualan($bulan)
    {
        $parts = explode('-', $bulan);
        
        // Kita ingin menggunakan total_harga pesanan, lebih aman join
        $trenData = DB::table('pembayaran')
            ->join('pesanan', 'pembayaran.pesanan_id', '=', 'pesanan.id')
            ->where('pembayaran.status', 'berhasil')
            ->whereYear('pembayaran.dibayar_pada', $parts[0])
            ->whereMonth('pembayaran.dibayar_pada', $parts[1])
            ->select(
                DB::raw('DATE(pembayaran.dibayar_pada) as tanggal'),
                DB::raw('COUNT(pembayaran.id) as jumlah_transaksi'),
                DB::raw('SUM(pesanan.total_harga) as omzet')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Buat struktur array penuh untuk sebulan
        $parts = explode('-', $bulan);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $parts[1], $parts[0]);
        
        $result = [];
        $trenAssoc = $trenData->keyBy('tanggal');

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = $bulan . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            if (isset($trenAssoc[$dateStr])) {
                $result[] = [
                    'tanggal' => $dateStr,
                    'jumlah_transaksi' => (int) $trenAssoc[$dateStr]->jumlah_transaksi,
                    'omzet' => (int) $trenAssoc[$dateStr]->omzet,
                ];
            } else {
                $result[] = [
                    'tanggal' => $dateStr,
                    'jumlah_transaksi' => 0,
                    'omzet' => 0,
                ];
            }
        }

        return $result;
    }

    /**
     * Agregasi internal untuk laporan harian dan bulanan.
     */
    private function aggregateLaporan($query, $periodeNilai, $periodeKey)
    {
        $pembayarans = $query->get();

        $jumlahTransaksi = $pembayarans->count();
        $totalOmzet = 0;
        $totalHpp = 0;

        $pembayaranMethods = [
            'cash' => 0,
            'qris' => 0,
            'transfer' => 0
        ];

        foreach ($pembayarans as $p) {
            $totalOmzet += $p->pesanan->total_harga ?? 0;
            
            $metode = strtolower($p->metode_pembayaran);
            if (isset($pembayaranMethods[$metode])) {
                $pembayaranMethods[$metode] += $p->pesanan->total_harga ?? 0;
            }

            if ($p->pesanan && $p->pesanan->detailPesanan) {
                foreach ($p->pesanan->detailPesanan as $detail) {
                    $totalHpp += ($detail->hpp ?? 0) * $detail->jumlah;
                }
            }
        }

        $totalMargin = $totalOmzet - $totalHpp;
        $marginPersen = $totalOmzet > 0 ? round(($totalMargin / $totalOmzet) * 100, 2) : 0;

        return [
            $periodeKey => $periodeNilai,
            'jumlah_transaksi' => $jumlahTransaksi,
            'total_omzet' => $totalOmzet,
            'total_hpp' => $totalHpp,
            'total_margin' => $totalMargin,
            'margin_persen' => $marginPersen,
            'pembayaran' => $pembayaranMethods
        ];
    }
}
