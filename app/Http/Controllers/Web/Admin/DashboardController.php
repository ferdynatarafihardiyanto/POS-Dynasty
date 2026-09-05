<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LaporanService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');
        $laporanHariIni = $this->laporanService->getLaporanHarian($today);

        return view('admin.dashboard', compact('laporanHariIni'));
    }
}
