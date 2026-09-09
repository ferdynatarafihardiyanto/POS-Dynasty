<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynasty Cafe - Admin</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 10px 15px; display: block; border-radius: 5px; margin-bottom: 5px;}
        .sidebar a:hover, .sidebar a.active { color: #fff; background-color: #343a40; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4 fw-bold">☕ Dynasty Cafe</span>
            </a>
            <hr class="text-white">
            <ul class="nav nav-pills flex-column mb-auto">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                
                <li class="mt-3 mb-1 text-muted text-uppercase small fw-bold px-3">Transaksi</li>
                <li>
                    <a href="{{ route('admin.pos.index') }}" class="nav-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }} d-flex align-items-center">
                        <i class="bi bi-shop me-2"></i> <span>POS / Kasir</span>
                        <span class="badge rounded-pill bg-danger ms-auto global-table-order-badge d-none" style="font-size: 0.7rem;"></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.transaksi.index') }}" class="nav-link {{ request()->routeIs('admin.transaksi.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt me-2"></i> Riwayat Transaksi
                    </a>
                </li>
                
                <li class="mt-3 mb-1 text-muted text-uppercase small fw-bold px-3">Master Data</li>
                <li>
                    <a href="{{ route('admin.kategori.index') }}" class="nav-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
                        <i class="bi bi-tags me-2"></i> Kategori
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.produk.index') }}" class="nav-link {{ request()->routeIs('admin.produk.*') ? 'active' : '' }}">
                        <i class="bi bi-cup-hot me-2"></i> Produk / Menu
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.modifier-groups.index') }}" class="nav-link {{ request()->routeIs('admin.modifier-groups.*') ? 'active' : '' }}">
                        <i class="bi bi-list-stars me-2"></i> Varian / Topping
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.meja.index') }}" class="nav-link {{ request()->routeIs('admin.meja.*') ? 'active' : '' }}">
                        <i class="bi bi-grid-3x3-gap me-2"></i> Meja & QR
                    </a>
                </li>
                
                <li class="mt-3 mb-1 text-muted text-uppercase small fw-bold px-3">Dapur & Inventory</li>
                <li>
                    <a href="{{ route('admin.bahan-baku.index') }}" class="nav-link {{ request()->routeIs('admin.bahan-baku.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2"></i> Bahan Baku
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.resep.index') }}" class="nav-link {{ request()->routeIs('admin.resep.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text me-2"></i> Resep
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.stok.index') }}" class="nav-link {{ request()->routeIs('admin.stok.*') ? 'active' : '' }}">
                        <i class="bi bi-arrow-left-right me-2"></i> Keluar/Masuk Stok
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.stock_opname.index') }}" class="nav-link {{ request()->routeIs('admin.stock_opname.*') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-check me-2"></i> Stock Opname
                    </a>
                </li>

                <li class="mt-3 mb-1 text-muted text-uppercase small fw-bold px-3">Keuangan</li>
                <li>
                    <a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-graph-up-arrow me-2"></i> Laporan
                    </a>
                </li>
            </ul>
            <hr class="text-white">
            <div class="dropdown">
                        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm px-3">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </button>
                        </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 shadow-sm">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h4 fw-bold">@yield('title', 'Admin Panel')</span>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3"><i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name ?? 'Admin Kasir' }}</span>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <div class="p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @include('partials.global_order_notifier')
    @stack('scripts')
</body>
</html>
