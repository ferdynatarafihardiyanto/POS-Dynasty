<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dynasty Cafe - POS</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow-x: hidden;
            height: 100vh;
        }
        /* Layout overrides for POS */
        .pos-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Left Sidebar */
        .pos-sidebar {
            width: 250px;
            background-color: #922c24; /* Dark Red from reference */
            color: #fff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow-y: auto;
        }
        .pos-sidebar .brand {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .pos-sidebar .brand-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pos-sidebar .nav-item {
            margin: 5px 15px;
        }
        .pos-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 10px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .pos-sidebar .nav-link:hover, .pos-sidebar .nav-link.active {
            background-color: #fff;
            color: #922c24;
            font-weight: 600;
        }
        .pos-sidebar-bottom {
            margin-top: auto;
            padding: 20px 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Main Content */
        .pos-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #fcfcfc;
            overflow-y: auto;
            position: relative;
            min-width: 0;
        }

        /* Right Cart Sidebar */
        .pos-cart {
            width: 360px;
            background-color: #fff;
            border-left: 1px solid #eee;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            z-index: 10;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive Breakpoints & Drawers */
        .pos-sidebar-backdrop, .pos-cart-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(2px);
            z-index: 1040;
            transition: opacity 0.3s ease;
        }
        .pos-sidebar-backdrop.show, .pos-cart-backdrop.show {
            display: block;
        }

        @media (max-width: 992px) {
            .pos-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 280px;
                max-width: 85vw;
                z-index: 1050;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.25);
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .pos-sidebar.show {
                transform: translateX(0);
            }
            .pos-cart {
                position: fixed;
                right: 0;
                top: 0;
                bottom: 0;
                width: 380px;
                max-width: 100vw;
                z-index: 1050;
                box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
                transform: translateX(100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .pos-cart.show {
                transform: translateX(0);
            }
        }

        @media (max-width: 576px) {
            .pos-cart {
                width: 100vw;
            }
        }

        /* Print Styles */
        @media print {
            .pos-sidebar, .pos-cart, header, .btn, button, .modal, .dropdown, form {
                display: none !important;
            }
            .pos-wrapper {
                height: auto;
                overflow: visible;
                display: block;
            }
            .pos-main {
                height: auto;
                overflow: visible;
                display: block;
            }
            body {
                height: auto;
                background-color: white;
            }
            .border-bottom {
                border-bottom: none !important;
            }
            .shadow-sm {
                box-shadow: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="pos-wrapper">
        <!-- Sidebar -->
        <div class="pos-sidebar">
            <div class="brand">
                <div class="brand-icon">
                    <i class="bi bi-bag"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5">Pos System</div>
                    <div class="small opacity-75">Sistem kasir</div>
                </div>
                <button type="button" class="btn btn-sm text-white ms-auto d-lg-none rounded-circle d-flex align-items-center justify-content-center" id="closeSidebarBtn" title="Tutup Menu" style="width: 32px; height: 32px; background: rgba(255,255,255,0.15);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="flex-grow-1 overflow-auto py-3">
                <div class="nav-item">
                    <a href="{{ route('admin.pos.index') }}" class="nav-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }} d-flex align-items-center">
                        <i class="bi bi-cart3"></i> <span>Kasir</span>
                        <span class="badge rounded-pill bg-danger ms-auto global-table-order-badge d-none" style="font-size: 0.7rem;"></span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#collapseBarang" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('admin.produk.*', 'admin.bahan-baku.*', 'admin.resep.*', 'admin.stok.*', 'admin.stock_opname.*') ? 'active' : '' }}" role="button" aria-expanded="false" aria-controls="collapseBarang">
                        <i class="bi bi-box-seam"></i> <span>Barang & Stok</span>
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.produk.*', 'admin.bahan-baku.*', 'admin.resep.*', 'admin.stok.*', 'admin.stock_opname.*') ? 'show' : '' }}" id="collapseBarang">
                        <div class="d-flex flex-column gap-1 py-2 px-3 ps-4 ms-2 mt-1" style="border-left: 1px solid rgba(255,255,255,0.2);">
                            <a href="{{ route('admin.produk.index') }}" class="text-decoration-none {{ request()->routeIs('admin.produk.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Daftar Barang</a>
                            <a href="{{ route('admin.bahan-baku.index') }}" class="text-decoration-none {{ request()->routeIs('admin.bahan-baku.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Bahan Baku</a>
                            <a href="{{ route('admin.resep.index') }}" class="text-decoration-none {{ request()->routeIs('admin.resep.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Resep Produk</a>
                            <a href="{{ route('admin.stok.index') }}" class="text-decoration-none {{ request()->routeIs('admin.stok.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Mutasi Stok</a>
                            <a href="{{ route('admin.stock_opname.index') }}" class="text-decoration-none {{ request()->routeIs('admin.stock_opname.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Stock Opname</a>
                        </div>
                    </div>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.transaksi.index') }}" class="nav-link {{ request()->routeIs('admin.transaksi.*') ? 'active' : '' }}">
                        <i class="bi bi-clock-history"></i> Riwayat Transaksi
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart"></i> Laporan
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.pengeluaran.index') }}" class="nav-link {{ request()->routeIs('admin.pengeluaran.*') ? 'active' : '' }}">
                        <i class="bi bi-receipt-cutoff"></i> Pengeluaran
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#collapsePengaturan" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('admin.meja.*', 'admin.kategori.*', 'admin.modifier-groups.*', 'admin.profil.*', 'admin.karyawan.*') ? 'active' : '' }}" role="button" aria-expanded="false" aria-controls="collapsePengaturan">
                        <i class="bi bi-gear"></i> <span>Pengaturan</span>
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.meja.*', 'admin.kategori.*', 'admin.satuan.*', 'admin.modifier-groups.*', 'admin.profil.*', 'admin.karyawan.*') ? 'show' : '' }}" id="collapsePengaturan">
                        <div class="d-flex flex-column gap-1 py-2 px-3 ps-4 ms-2 mt-1" style="border-left: 1px solid rgba(255,255,255,0.2);">
                            <a href="{{ route('admin.profil.index') }}" class="text-decoration-none {{ request()->routeIs('admin.profil.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Profil Toko</a>
                            <a href="{{ route('admin.meja.index') }}" class="text-decoration-none {{ request()->routeIs('admin.meja.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Meja & QR Code</a>
                            <a href="{{ route('admin.kategori.index') }}" class="text-decoration-none {{ request()->routeIs('admin.kategori.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Kategori Produk</a>
                            <a href="{{ route('admin.satuan.index') }}" class="text-decoration-none {{ request()->routeIs('admin.satuan.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Satuan Unit</a>
                            <a href="{{ route('admin.modifier-groups.index') }}" class="text-decoration-none {{ request()->routeIs('admin.modifier-groups.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Varian / Topping</a>
                            <a href="{{ route('admin.karyawan.index') }}" class="text-decoration-none {{ request()->routeIs('admin.karyawan.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1">Karyawan</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pos-sidebar-bottom">
                <div class="nav-item">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link text-white bg-transparent border-0 w-100 text-start">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @yield('content')
        
    </div>

    <!-- Backdrop untuk Sidebar Mobile/Tablet -->
    <div class="pos-sidebar-backdrop" id="posSidebarBackdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.pos-sidebar');
            const sidebarBackdrop = document.getElementById('posSidebarBackdrop');
            const closeSidebarBtn = document.getElementById('closeSidebarBtn');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('show');
                if (sidebarBackdrop) sidebarBackdrop.classList.add('show');
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('show');
                if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
            }

            // Otomatis pastikan ada tombol hamburger di header jika belum ada (untuk semua halaman admin di tablet & mobile)
            function ensureSidebarToggleBtn() {
                if (document.getElementById('openSidebarBtn') || document.querySelector('.btn-open-sidebar')) return;

                // Cari baris header pertama di dalam .pos-main
                const mainHeader = document.querySelector('.pos-main > div:first-child');
                if (mainHeader) {
                    const firstChild = mainHeader.firstElementChild;
                    if (firstChild) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.id = 'openSidebarBtn';
                        btn.className = 'btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center me-2 flex-shrink-0';
                        btn.style.width = '38px';
                        btn.style.height = '38px';
                        btn.title = 'Buka Menu Navigasi';
                        btn.innerHTML = '<i class="bi bi-list fs-5"></i>';

                        const wrapper = document.createElement('div');
                        wrapper.className = 'd-flex align-items-center gap-2';
                        firstChild.parentNode.insertBefore(wrapper, firstChild);
                        wrapper.appendChild(btn);
                        wrapper.appendChild(firstChild);
                        return;
                    }
                }
            }

            ensureSidebarToggleBtn();

            // Delegated click handler untuk tombol buka sidebar (bisa dipasang di header halaman manapun)
            document.addEventListener('click', function(e) {
                if (e.target.closest('#openSidebarBtn') || e.target.closest('.btn-open-sidebar')) {
                    openSidebar();
                }
            });

            if (closeSidebarBtn) {
                closeSidebarBtn.addEventListener('click', closeSidebar);
            }
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', closeSidebar);
            }
        });
    </script>
    @include('partials.global_order_notifier')
    @stack('scripts')
</body>
</html>
