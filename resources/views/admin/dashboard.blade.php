@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold mb-3">Selamat Datang di Dynasty Cafe!</h2>
        <p class="text-muted">Ringkasan penjualan hari ini ({{ \Carbon\Carbon::parse($laporanHariIni['tanggal'])->translatedFormat('d F Y') }}).</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">Total Transaksi</h6>
                <h3 class="fw-bold mb-0">{{ $laporanHariIni['jumlah_transaksi'] }} <span class="fs-6 text-muted fw-normal">struk</span></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">Total Omzet</h6>
                <h3 class="fw-bold text-success mb-0">Rp {{ number_format($laporanHariIni['total_omzet'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <h6 class="text-muted text-uppercase fw-semibold mb-2">Total HPP</h6>
                <h3 class="fw-bold text-danger mb-0">Rp {{ number_format($laporanHariIni['total_hpp'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50 text-uppercase fw-semibold mb-2">Profit (Margin)</h6>
                <h3 class="fw-bold mb-0">Rp {{ number_format($laporanHariIni['total_margin'], 0, ',', '.') }}</h3>
                <small class="text-white-50">{{ $laporanHariIni['margin_persen'] }}% Margin</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4 text-center">
                <p class="mb-0 text-secondary">
                    Di tahap selanjutnya, kita akan menyuntikkan fitur POS (Kasir), Manajemen Stok, Resep, dan Manajemen Varian/Topping ke dalam antarmuka web ini agar kasir lebih mudah menggunakannya.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
