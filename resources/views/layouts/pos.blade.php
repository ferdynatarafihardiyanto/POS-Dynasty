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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'DM Sans', 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            overflow-x: hidden;
            height: 100vh;
        }
        h1, h2, h3, h4, h5, h6, .fw-bold, .fw-semibold {
            font-family: 'Outfit', sans-serif;
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
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pos-sidebar.collapsed {
            width: 76px;
        }
        .pos-sidebar .brand {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
            overflow: hidden;
            min-height: 90px;
        }
        .pos-sidebar.collapsed .brand {
            padding: 12px 8px;
            justify-content: center;
        }
        .pos-sidebar .brand-text {
            transition: opacity 0.2s;
        }
        .pos-sidebar.collapsed .brand-text {
            opacity: 0;
            display: none;
        }
        .pos-sidebar .brand-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pos-sidebar .nav-item {
            margin: 5px 15px;
        }
        .pos-sidebar.collapsed .nav-item {
            margin: 5px 10px;
        }
        .pos-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 10px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .pos-sidebar.collapsed .nav-link {
            padding: 10px;
            justify-content: center;
        }
        .pos-sidebar .nav-link i.icon-main {
            width: 28px;
            text-align: center;
            font-size: 1.15rem;
            margin-right: 10px;
            flex-shrink: 0;
        }
        .pos-sidebar.collapsed .nav-link i.icon-main {
            margin-right: 0;
        }
        .pos-sidebar .nav-link:hover, .pos-sidebar .nav-link.active {
            background-color: #fff;
            color: #922c24;
            font-weight: 600;
        }
        .pos-sidebar .nav-text {
            transition: opacity 0.2s;
        }
        .pos-sidebar.collapsed .nav-text, 
        .pos-sidebar.collapsed .bi-chevron-down {
            display: none !important;
        }
        .pos-sidebar.collapsed .collapse > div {
            padding: 5px 0 !important;
            margin: 0 !important;
            border: none !important;
            background: rgba(0, 0, 0, 0.15);
            border-radius: 8px;
            margin-top: 5px !important;
        }
        .pos-sidebar.collapsed .collapse > div > a {
            justify-content: center;
            padding: 8px 0;
            margin: 0;
            width: 100%;
        }
        .pos-sidebar.collapsed .collapse > div > a i {
            width: 28px !important;
            text-align: center;
            margin: 0 !important;
            font-size: 0.95rem;
            flex-shrink: 0;
            opacity: 0.8;
        }
        .pos-sidebar.collapsed .collapse > div > a:hover i {
            opacity: 1;
            transform: scale(1.1);
            transition: all 0.2s;
        }
        .pos-sidebar-bottom {
            margin-top: auto;
            padding: 20px 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
        }
        .pos-sidebar.collapsed .pos-sidebar-bottom {
            padding: 20px 10px;
            text-align: center;
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

        @media (max-width: 767px) {
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
<body x-data="posLayout()">
    <div class="pos-wrapper" @touchstart="handleTouchStart" @touchend="handleTouchEnd">
        <!-- Sidebar -->
        <div class="pos-sidebar position-relative" :class="{ 'collapsed': sidebarCollapsed }">
            <!-- Desktop Toggle Button -->
            <button type="button" 
                class="btn btn-sm btn-light rounded-circle position-absolute d-none d-md-flex align-items-center justify-content-center shadow-sm" 
                @click="sidebarCollapsed = !sidebarCollapsed"
                style="width: 28px; height: 28px; right: -14px; top: 30px; z-index: 1050; border: 1px solid #ddd;"
                title="Toggle Sidebar">
                <i class="bi" :class="sidebarCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'" style="font-size: 0.8rem; color: #922c24;"></i>
            </button>

            <div class="brand">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo" class="sidebar-logo" style="max-width: 180px; width: 100%; height: auto; max-height: 65px; object-fit: contain; display: block;" onerror="this.onerror=null; this.style.display='none'; document.getElementById('sidebarTextLogo')?.classList.remove('d-none');">
                <div id="sidebarTextLogo" class="d-none text-white fw-bold fs-5 tracking-wide">
                    DYNASTY <span style="color: #f59e0b;">CAFE</span>
                </div>
                <button type="button" class="btn btn-sm text-white ms-auto d-lg-none rounded-circle d-flex align-items-center justify-content-center" id="closeSidebarBtn" title="Tutup Menu" style="width: 32px; height: 32px; background: rgba(255,255,255,0.15);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="flex-grow-1 overflow-auto py-3">
                <div class="nav-item">
                    <a href="{{ route('admin.pos.index') }}" class="nav-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}" title="Kasir">
                        <i class="bi bi-cart3 icon-main"></i> <span class="nav-text">Kasir</span>
                        <span class="badge rounded-pill bg-danger ms-auto global-table-order-badge d-none" style="font-size: 0.7rem;"></span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#collapseBarang" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('admin.produk.*', 'admin.bahan-baku.*', 'admin.resep.*', 'admin.stok.*', 'admin.stock_opname.*') ? 'active' : '' }}" role="button" aria-expanded="false" aria-controls="collapseBarang" title="Barang & Stok">
                        <i class="bi bi-box-seam icon-main"></i> <span class="nav-text">Barang & Stok</span>
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.produk.*', 'admin.bahan-baku.*', 'admin.resep.*', 'admin.stok.*', 'admin.stock_opname.*') ? 'show' : '' }}" id="collapseBarang">
                        <div class="d-flex flex-column gap-1 py-2 px-3 ps-4 ms-2 mt-1" style="border-left: 1px solid rgba(255,255,255,0.2);">
                            <a href="{{ route('admin.produk.index') }}" class="text-decoration-none {{ request()->routeIs('admin.produk.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Daftar Barang"><i class="bi bi-box"></i> <span class="nav-text">Daftar Barang</span></a>
                            <a href="{{ route('admin.bahan-baku.index') }}" class="text-decoration-none {{ request()->routeIs('admin.bahan-baku.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Bahan Baku"><i class="bi bi-basket"></i> <span class="nav-text">Bahan Baku</span></a>
                            <a href="{{ route('admin.resep.index') }}" class="text-decoration-none {{ request()->routeIs('admin.resep.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Resep Produk"><i class="bi bi-journal-text"></i> <span class="nav-text">Resep Produk</span></a>
                            <a href="{{ route('admin.stok.index') }}" class="text-decoration-none {{ request()->routeIs('admin.stok.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Mutasi Stok"><i class="bi bi-arrow-left-right"></i> <span class="nav-text">Mutasi Stok</span></a>
                            <a href="{{ route('admin.stock_opname.index') }}" class="text-decoration-none {{ request()->routeIs('admin.stock_opname.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Stock Opname"><i class="bi bi-clipboard-check"></i> <span class="nav-text">Stock Opname</span></a>
                        </div>
                    </div>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.transaksi.index') }}" class="nav-link {{ request()->routeIs('admin.transaksi.*') ? 'active' : '' }}" title="Riwayat Transaksi">
                        <i class="bi bi-clock-history icon-main"></i> <span class="nav-text">Riwayat Transaksi</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.laporan.index') }}" class="nav-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}" title="Laporan">
                        <i class="bi bi-bar-chart icon-main"></i> <span class="nav-text">Laporan</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('admin.pengeluaran.index') }}" class="nav-link {{ request()->routeIs('admin.pengeluaran.*') ? 'active' : '' }}" title="Pengeluaran">
                        <i class="bi bi-receipt icon-main"></i> <span class="nav-text">Pengeluaran</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#collapsePengaturan" data-bs-toggle="collapse" class="nav-link {{ request()->routeIs('admin.profil.*', 'admin.kategori.*', 'admin.satuan.*', 'admin.modifier-group.*', 'admin.meja.*', 'admin.karyawan.*') ? 'active' : '' }}" role="button" aria-expanded="false" aria-controls="collapsePengaturan" title="Pengaturan">
                        <i class="bi bi-gear icon-main"></i> <span class="nav-text">Pengaturan</span>
                        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.profil.*', 'admin.kategori.*', 'admin.satuan.*', 'admin.modifier-groups.*', 'admin.meja.*', 'admin.karyawan.*') ? 'show' : '' }}" id="collapsePengaturan">
                        <div class="d-flex flex-column gap-1 py-2 px-3 ps-4 ms-2 mt-1" style="border-left: 1px solid rgba(255,255,255,0.2);">
                            <a href="{{ route('admin.profil.index') }}" class="text-decoration-none {{ request()->routeIs('admin.profil.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Profil Toko"><i class="bi bi-shop"></i> <span class="nav-text">Profil Toko</span></a>
                            <a href="{{ route('admin.kategori.index') }}" class="text-decoration-none {{ request()->routeIs('admin.kategori.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Kategori Produk"><i class="bi bi-tags"></i> <span class="nav-text">Kategori Produk</span></a>
                            <a href="{{ route('admin.satuan.index') }}" class="text-decoration-none {{ request()->routeIs('admin.satuan.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Satuan Unit"><i class="bi bi-rulers"></i> <span class="nav-text">Satuan Unit</span></a>
                            <a href="{{ route('admin.modifier-groups.index') }}" class="text-decoration-none {{ request()->routeIs('admin.modifier-groups.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Varian / Topping"><i class="bi bi-layers"></i> <span class="nav-text">Varian / Topping</span></a>
                            <a href="{{ route('admin.meja.index') }}" class="text-decoration-none {{ request()->routeIs('admin.meja.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Manajemen Meja"><i class="bi bi-grid-3x3"></i> <span class="nav-text">Manajemen Meja</span></a>
                            <a href="{{ route('admin.karyawan.index') }}" class="text-decoration-none {{ request()->routeIs('admin.karyawan.*') ? 'text-white fw-bold' : 'text-white opacity-75' }} small py-1 d-flex align-items-center gap-2" title="Karyawan"><i class="bi bi-person-badge"></i> <span class="nav-text">Karyawan</span></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pos-sidebar-bottom">
                <div class="nav-item">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link text-white bg-transparent border-0 w-100 text-start" title="Keluar">
                            <i class="bi bi-box-arrow-right icon-main"></i> <span class="nav-text">Keluar</span>
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
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posLayout', () => ({
                sidebarCollapsed: false,
                touchStartX: 0,
                touchEndX: 0,
                init() {
                    // 1. Cek state dari localStorage agar tidak reset saat pindah halaman
                    const savedState = localStorage.getItem('posSidebarCollapsed');
                    if (savedState !== null) {
                        this.sidebarCollapsed = savedState === 'true';
                    } else if (window.innerWidth >= 768 && window.innerWidth <= 1024) {
                        // Auto-collapse on tablet view initially if no saved state
                        this.sidebarCollapsed = true;
                    }

                    // 2. Simpan setiap ada perubahan state
                    this.$watch('sidebarCollapsed', value => {
                        localStorage.setItem('posSidebarCollapsed', value);
                    });
                },
                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].screenX;
                },
                handleTouchEnd(e) {
                    this.touchEndX = e.changedTouches[0].screenX;
                    this.handleSwipe();
                },
                handleSwipe() {
                    // Only process swipe if screen width is >= 768px (not offcanvas mobile mode)
                    if (window.innerWidth < 768) return;
                    
                    let swipeDistance = this.touchEndX - this.touchStartX;
                    // Geser Kiri (Collapse)
                    if (swipeDistance < -50) {
                        this.sidebarCollapsed = true;
                    } 
                    // Geser Kanan (Expand)
                    else if (swipeDistance > 50) {
                        this.sidebarCollapsed = false;
                    }
                }
            }));
        });
    </script>
    @include('partials.global_order_notifier')
    @stack('scripts')
</body>
</html>
