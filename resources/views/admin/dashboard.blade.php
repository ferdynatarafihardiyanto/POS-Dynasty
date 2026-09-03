@extends('layouts.admin')

@section('title', 'Dashboard Utama')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-5 text-center">
                <div class="display-1 text-success mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h2 class="fw-bold mb-3">Autentikasi Web Berhasil!</h2>
                <p class="lead text-muted">Selamat datang di sistem manajemen <b>Dynasty Cafe</b> versi Web Fullstack.</p>
                <hr class="w-50 mx-auto my-4">
                <p class="mb-0 text-secondary">
                    Di tahap selanjutnya, kita akan menyuntikkan fitur POS (Kasir), Manajemen Stok, Resep, dan Laporan Keuangan ke dalam antarmuka web ini agar kasir lebih mudah menggunakannya.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
