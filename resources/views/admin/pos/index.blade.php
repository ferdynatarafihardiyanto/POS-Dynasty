@extends('layouts.pos')

@push('styles')
<style>
    /* Ubah warna toggle switch Bootstrap menjadi Dark Red */
    .form-check-input:checked {
        background-color: #8b211e !important;
        border-color: #8b211e !important;
    }
    /* Pita Miring (Ribbon) Estetik Produk Habis */
    .ribbon-sold-out {
        position: absolute;
        top: 16px;
        left: -32px;
        width: 125px;
        transform: rotate(-45deg);
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: #ffffff;
        text-align: center;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 3px 0;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.25);
        z-index: 5;
        border-top: 1px solid rgba(255, 255, 255, 0.35);
        border-bottom: 1px solid rgba(0, 0, 0, 0.2);
        pointer-events: none;
    }
    /* Sembunyikan scrollbar kategori di mobile/tablet untuk tampilan lebih bersih */
    .category-pills-scroll::-webkit-scrollbar {
        display: none;
    }
    .category-pills-scroll {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@section('content')
<!-- Alpine Data Injection -->
<script>
    window.posData = {
        categories: @json($kategoris),
        products: @json($produks),
        tables: @json($tables),
        checkoutUrl: '{{ route('admin.pos.checkout') }}',
        pesananAktifUrl: '{{ route('admin.pos.pesanan_aktif') }}',
        ubahStatusBaseUrl: '{{ url('/admin/pos/pesanan') }}',
        bayarPesananBaseUrl: '{{ url('/admin/pos/pesanan') }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>

<div class="d-flex flex-grow-1 overflow-hidden position-relative" x-data="posSystem()">
    
    <!-- Toast Notification -->
    <div class="position-absolute top-0 end-0 p-3" :style="cart.length > 0 ? 'z-index: 1050; padding-right: 380px !important;' : 'z-index: 1050; padding-right: 20px !important;'">
        <div class="toast align-items-center bg-white border-0 shadow" :class="showToast ? 'show' : 'hide'" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 50px; transition: opacity 0.3s; opacity: showToast ? 1 : 0;">
            <div class="d-flex">
                <div class="toast-body fw-bold d-flex align-items-center gap-2 px-4 py-2" :style="toastType === 'warning' ? 'color: #dc2626;' : 'color: #8b211e;'">
                    <i :class="toastType === 'warning' ? 'bi bi-exclamation-circle-fill text-danger fs-6' : 'bi bi-check-circle-fill text-success fs-6'"></i>
                    <span x-text="toastMessage"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pos-main">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between p-3 p-md-4 bg-white border-bottom flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <!-- Hamburger Button untuk Sidebar di Mobile/Tablet -->
                <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center" id="openSidebarBtn" title="Buka Navigasi" style="width: 38px; height: 38px;">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h4 class="mb-0 fw-bold fs-5 fs-md-4">Kasir</h4>
                    <div class="text-danger fw-bold small" style="font-size: 0.72rem;">KEDAI DYNASTY</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3 ms-auto flex-wrap">
                <!-- Printer Status & Quick Settings Button -->
                <button type="button" class="btn btn-light rounded-pill px-2.5 px-sm-3 py-1 border d-flex align-items-center gap-1.5 shadow-sm" @click="openPrinterSettingsModal()" title="Pengaturan Printer Struk">
                    <i class="bi bi-printer-fill" style="color: #8b211e;"></i>
                    <div class="text-start d-none d-md-block" style="line-height: 1.1;">
                        <div class="fw-bold text-dark" style="font-size: 0.75rem;" x-text="printerConfig.printerName || 'Printer Thermal'"></div>
                        <div class="text-success" style="font-size: 0.65rem;"><i class="bi bi-circle-fill" style="font-size: 6px;"></i> Modul Siap</div>
                    </div>
                </button>

                <!-- Tombol Notifikasi Pesanan Meja (Self-Order) -->
                <button type="button" class="btn btn-light rounded-pill px-2.5 px-sm-3 py-1 border d-flex align-items-center gap-1.5 shadow-sm position-relative" @click="openTableOrdersModal()" title="Pesanan Meja Masuk (Self-Order)">
                    <i class="bi bi-bell-fill text-warning fs-6 fs-sm-5"></i>
                    <div class="text-start d-none d-sm-block" style="line-height: 1.1;">
                        <div class="fw-bold text-dark" style="font-size: 0.75rem;">Pesanan Meja</div>
                        <div class="text-muted" style="font-size: 0.65rem;" x-text="tableOrders.length > 0 ? (tableOrders.length + ' Pesanan Aktif') : 'Tidak Ada Pesanan'"></div>
                    </div>
                    <span x-show="tableOrders.length > 0" class="badge rounded-pill bg-danger ms-1" x-text="tableOrders.length" style="font-size: 0.7rem;"></span>
                </button>

                <!-- Waktu (Hanya tampil di tablet & desktop agar header mobile rapi) -->
                <div class="border rounded px-3 py-1 text-center bg-light d-none d-md-block">
                    <div class="small text-muted" style="font-size: 0.7rem;">WAKTU</div>
                    <div class="fw-bold" x-text="currentTime">--:--:-- WIB</div>
                </div>

                <!-- Tombol Buka Keranjang di Tablet/Mobile (Header) -->
                <button type="button" class="btn btn-light border rounded-pill px-3 py-1.5 position-relative d-lg-none shadow-sm d-flex align-items-center gap-2" @click="openCartDrawer()" title="Buka Keranjang Pesanan">
                    <i class="bi bi-bag-check-fill fs-6" style="color: #8b211e;"></i>
                    <span class="fw-bold small" style="color: #8b211e;" x-text="formatRupiah(grandTotal)"></span>
                    <span x-show="totalCartItems > 0" class="badge bg-danger rounded-pill" x-text="totalCartItems"></span>
                </button>

                <!-- Avatar User -->
                <div class="d-flex align-items-center gap-2 ps-1">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="rounded-circle" width="40" height="40" alt="Avatar">
                    <div class="d-none d-md-block">
                        <div class="fw-bold fs-6 lh-1">{{ Auth::user()->name }}</div>
                        <div class="text-danger small">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="p-4 pb-2">
            <!-- Search -->
            <div class="position-relative mb-3">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="searchInput" class="form-control form-control-lg rounded-pill ps-5 bg-white border-0 shadow-sm" placeholder="Cari produk (F2)" x-model="searchQuery">
            </div>

            <!-- Category Pills -->
            <div class="d-flex gap-2 overflow-auto pb-2 category-pills-scroll" style="white-space: nowrap;">
                <button class="btn rounded-pill px-4 shadow-sm border-0" 
                        :class="activeCategoryId === null ? 'text-white' : 'btn-white bg-white text-dark'"
                        @click="activeCategoryId = null"
                        :style="activeCategoryId === null ? 'background-color: #8b211e;' : ''">Semua produk</button>
                <template x-for="cat in categories" :key="cat.id">
                    <button class="btn rounded-pill px-4 shadow-sm border-0"
                            :class="activeCategoryId === cat.id ? 'text-white' : 'bg-white text-dark'"
                            @click="activeCategoryId = cat.id"
                            :style="activeCategoryId === cat.id ? 'background-color: #8b211e;' : ''"
                            x-text="cat.nama"></button>
                </template>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="flex-grow-1 overflow-auto p-3 p-md-4 pt-0" style="padding-bottom: 90px !important;">
            <div class="row g-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative d-flex flex-column"
                             :class="product.stok <= 0 ? 'bg-light border' : ''"
                             :style="product.stok <= 0 ? 'cursor: not-allowed;' : 'cursor: pointer;'"
                             @click="handleProductClick(product)">
                            <div class="position-relative bg-light overflow-hidden flex-shrink-0" style="aspect-ratio: 4/3; width: 100%;">
                                <img :src="getProductImage(product)" 
                                     class="w-100 h-100 object-fit-cover" 
                                     :style="product.stok <= 0 ? 'filter: grayscale(85%) brightness(0.85); opacity: 0.75; transition: all 0.3s;' : 'transition: all 0.3s;'"
                                     :alt="product.nama"
                                     x-on:error="$event.target.src = 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=800&q=80'">
                                
                                <span class="position-absolute top-0 end-0 m-2 badge bg-dark opacity-75 rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.7rem;" x-text="product.kategori ? product.kategori.nama : 'Lainnya'"></span>
                                
                                <!-- Pita Miring Teks Habis / Sold Out -->
                                <template x-if="product.stok <= 0">
                                    <div class="ribbon-sold-out">
                                        <i class="bi bi-slash-circle me-1"></i>Habis
                                    </div>
                                </template>
                            </div>
                            <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1" :class="product.stok <= 0 ? 'opacity-75' : ''">
                                <h6 class="fw-bold mb-1 text-truncate" :class="product.stok <= 0 ? 'text-muted' : ''" x-text="product.nama"></h6>
                                <div class="small text-muted mb-2 text-uppercase" x-text="'BRG-' + product.id.toString().padStart(3, '0')"></div>
                                
                                <div class="mt-auto d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold" :style="product.stok <= 0 ? 'color: #9ca3af;' : 'color: #8b211e;'" x-text="formatRupiah(product.harga)"></div>
                                        <template x-if="product.stok > 0">
                                            <div class="small text-muted" style="font-size:0.75rem;" x-text="'Stok: ' + product.stok"></div>
                                        </template>
                                        <template x-if="product.stok <= 0">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill fw-bold" style="font-size: 0.68rem;">
                                                <i class="bi bi-x-circle me-1"></i>Habis
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Tombol Tambah vs Tombol Terkunci -->
                                    <template x-if="product.stok > 0">
                                        <button type="button" class="btn rounded-circle text-white shadow-sm d-flex align-items-center justify-content-center" style="background-color: #8b211e; border-color: #8b211e; width: 32px; height: 32px; padding:0;" @click.stop="openProductModal(product)" title="Tambah ke keranjang">
                                            <i class="bi bi-plus fs-5"></i>
                                        </button>
                                    </template>
                                    <template x-if="product.stok <= 0">
                                        <button type="button" class="btn rounded-circle border-0 d-flex align-items-center justify-content-center" style="background-color: #e2e8f0; color: #94a3b8; width: 32px; height: 32px; padding:0; cursor: not-allowed;" disabled title="Stok produk ini sedang habis">
                                            <i class="bi bi-lock-fill" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="filteredProducts.length === 0" class="col-12 text-center text-muted py-5">
                    <i class="bi bi-search fs-1 mb-3 d-block opacity-50"></i>
                    Produk tidak ditemukan
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Bottom Bar untuk Mobile & Tablet saat ada item di keranjang -->
    <div x-show="cart.length > 0" class="d-lg-none position-fixed bottom-0 start-0 end-0 p-3" style="z-index: 1030; pointer-events: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-4">
        <div class="card border-0 rounded-4 shadow-lg text-white p-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(135deg, #8b211e, #5f1513); pointer-events: auto;">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle bg-white text-danger d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 38px; height: 38px; font-size: 0.9rem;" x-text="totalCartItems"></div>
                <div>
                    <div class="small opacity-75" style="font-size: 0.7rem; letter-spacing: 0.3px;">Total Pesanan</div>
                    <div class="fw-bold fs-6" x-text="formatRupiah(grandTotal)"></div>
                </div>
            </div>
            <button type="button" class="btn btn-light rounded-pill px-3.5 py-2 fw-bold text-danger d-flex align-items-center gap-1.5 shadow-sm" @click="openCartDrawer()" style="font-size: 0.85rem;">
                <span>Keranjang</span>
                <i class="bi bi-arrow-right-short fs-5"></i>
            </button>
        </div>
    </div>

    <!-- Right Cart Sidebar -->
    <div class="pos-cart shadow" x-show="cart.length > 0" x-cloak>
        <div class="p-3.5 p-md-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light border rounded-circle d-lg-none d-flex align-items-center justify-content-center shadow-xs" @click="closeCartDrawer()" title="Tutup Keranjang" style="width: 32px; height: 32px;">
                    <i class="bi bi-x-lg"></i>
                </button>
                <h5 class="fw-bold mb-0 fs-6 fs-md-5">Ringkasan Pembayaran</h5>
            </div>
            <span class="badge rounded-pill px-2.5 py-1" style="background-color: #fde8e8; color: #8b211e;" x-text="totalCartItems + ' Item'" x-show="totalCartItems > 0"></span>
        </div>
        
        <!-- Pelanggan Info Bar -->
        <div class="p-3 border-bottom bg-white">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control bg-light border-start-0 shadow-none" placeholder="Pelanggan (Opsional)" x-model="customerName" style="font-size: 0.8rem;">
            </div>
        </div>

        <div class="p-3 border-bottom bg-light">
            <div class="fw-bold small text-muted text-uppercase" style="letter-spacing: 0.5px; font-size: 0.75rem;">Item Dipilih</div>
        </div>

        <!-- Cart Items -->
        <div class="flex-grow-1 overflow-auto p-3 bg-light">
            <template x-if="cart.length === 0">
                <div class="h-100 d-flex flex-column align-items-center justify-content-center text-muted opacity-50">
                    <div class="bg-white rounded-circle p-3 mb-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                        <i class="bi bi-cart2 fs-1"></i>
                    </div>
                    <div class="small">Keranjang masih kosong</div>
                </div>
            </template>
            
            <template x-for="(item, index) in cart" :key="item.cartId">
                <div class="card border-0 shadow-sm rounded-3 mb-2">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-bold text-dark pe-2" style="font-size: 0.85rem; line-height: 1.2;">
                                <span x-text="item.product.nama"></span>
                                <template x-if="item.selectedOptions && item.selectedOptions.length > 0">
                                    <span>
                                        <template x-for="(opt, idx) in item.selectedOptions" :key="opt.id">
                                            <span x-text="(idx === 0 ? ' + ' : ' + ') + opt.nama"></span>
                                        </template>
                                    </span>
                                </template>
                            </div>
                            <div class="d-flex align-items-center border rounded-pill px-2 py-1" style="min-width: 80px; justify-content: space-between;">
                                <button class="btn btn-sm p-0 text-muted" @click="updateQuantity(index, -1)"><i class="bi bi-dash"></i></button>
                                <span class="fw-bold mx-2" style="font-size: 0.85rem;" x-text="item.quantity"></span>
                                <button class="btn btn-sm p-0 text-muted" @click="updateQuantity(index, 1)"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-1">
                            <div>
                                <div class="text-muted mb-1" style="font-size: 0.75rem;" x-text="formatRupiah(item.unitPrice) + ' X ' + item.quantity"></div>
                                <div class="fw-bold" style="color: #8b211e; font-size: 0.95rem;" x-text="formatRupiah(item.unitPrice * item.quantity)"></div>
                                <template x-if="item.catatan">
                                    <div class="text-muted small mt-1 fst-italic" style="font-size: 0.75rem;">
                                        <i class="bi bi-chat-left-dots text-danger me-1"></i><span x-text="item.catatan"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <button type="button" class="btn btn-sm px-2 py-1 bg-light border text-dark rounded-2 d-flex align-items-center gap-1 shadow-sm" @click="openEditCartModal(index)" title="Ubah Topping / Catatan">
                                    <i class="bi bi-pencil-square text-danger"></i>
                                    <span style="font-size: 0.75rem; font-weight: 600;">Ubah</span>
                                </button>
                                <button type="button" class="btn btn-sm p-1 text-danger" style="color: #ef4444 !important;" @click="removeFromCart(index)" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-4 bg-white border-top" x-show="cart.length > 0">
            <div class="d-flex justify-content-between mb-2">
                <div class="text-muted small">Sub total</div>
                <div class="fw-bold small" x-text="formatRupiah(subtotal)"></div>
            </div>
            <div class="mb-4">
                <div class="text-muted small mb-1">Total pembayaran</div>
                <div class="fw-bold text-danger fs-3 lh-1" style="color: #8b211e !important;" x-text="formatRupiah(grandTotal)"></div>
            </div>

            <button id="btnBayar" class="btn btn-lg w-100 rounded-3 mb-2 fw-bold text-white shadow-sm" style="background-color: #8b211e;" @click="openPaymentModal()" :disabled="cart.length === 0">
                Bayar (F9)
            </button>
            <button class="btn w-100 rounded-3 bg-white fw-bold" style="border: 1px solid #8b211e; color: #8b211e;" @click="clearCart()" :disabled="cart.length === 0">
                <i class="bi bi-trash3"></i> Hapus keranjang
            </button>
            
            <div x-show="errorMsg" class="alert alert-danger mt-3 small p-2 mb-0" x-text="errorMsg" style="display: none;"></div>
            <div x-show="successMsg" class="alert alert-success mt-3 small p-2 mb-0" x-text="successMsg" style="display: none;"></div>
        </div>
    </div>

    <!-- Backdrop untuk Cart Drawer di Mobile/Tablet -->
    <div class="pos-cart-backdrop" id="posCartBackdrop" @click="closeCartDrawer()"></div>

    <!-- Modifier Modal -->
    <div class="modal fade" id="modifierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold text-dark" x-text="editingCartIndex !== null ? 'Ubah: ' + (modalProduct ? modalProduct.nama : '') : (modalProduct ? modalProduct.nama : '')"></h5>
                        <div class="text-muted small" x-text="editingCartIndex !== null ? 'Sesuaikan varian topping atau catatan hidangan ini' : 'Pilih varian atau topping yang diinginkan'"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <template x-if="modalProduct && modalProduct.modifier_groups && modalProduct.modifier_groups.length > 0">
                        <div>
                            <template x-for="group in modalProduct.modifier_groups" :key="group.id">
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold" x-text="group.nama"></div>
                                        <div>
                                            <span class="badge bg-danger rounded-pill" x-show="group.wajib_diisi">Wajib</span>
                                            <span class="badge bg-secondary rounded-pill" x-text="'Pilih ' + group.min_pilihan + ' - ' + group.max_pilihan"></span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <template x-for="opt in group.options" :key="opt.id">
                                            <label class="border rounded-3 p-3 d-flex justify-content-between align-items-center cursor-pointer" :class="isOptionSelected(group.id, opt.id) ? 'border-danger bg-light' : ''">
                                                <div>
                                                    <span x-text="opt.nama"></span>
                                                    <span class="text-muted small ms-2" x-show="opt.harga_tambahan > 0" x-text="'+ ' + formatRupiah(opt.harga_tambahan)"></span>
                                                </div>
                                                <input :type="group.max_pilihan === 1 ? 'radio' : 'checkbox'" 
                                                       :name="'mod_' + group.id" 
                                                       :value="opt.id"
                                                       class="form-check-input"
                                                       @change="toggleOption(group, opt, $event)">
                                            </label>
                                        </template>
                                        <div x-show="!group.options || group.options.length === 0" class="text-muted small text-center py-3 border border-dashed rounded bg-light">
                                            Belum ada opsi topping aktif di grup ini.
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!modalProduct || !modalProduct.modifier_groups || modalProduct.modifier_groups.length === 0">
                        <div class="text-muted small text-center py-3 border border-dashed rounded bg-light mb-3">
                            Menu ini tidak memiliki pilihan topping tambahan.
                        </div>
                    </template>

                    <!-- Catatan / Request Khusus Menu -->
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label fw-bold small text-dark d-flex align-items-center justify-content-between mb-1">
                            <span><i class="bi bi-pencil-square me-1 text-danger"></i> Catatan Khusus / Request Menu</span>
                            <span class="text-muted fw-normal" style="font-size: 0.7rem;">Opsional</span>
                        </label>
                        <input type="text" class="form-control rounded-3 shadow-none" placeholder="Contoh: Tidak pedas, tanpa sayur, es dipisah..." x-model="modalCatatan" style="font-size: 0.85rem;">
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">Instruksi ini otomatis tercetak di struk pesanan untuk koki/barista.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-lg w-100 rounded-3 text-white fw-bold" style="background-color: #8b211e;" @click="saveModalItem()" x-text="editingCartIndex !== null ? 'Perbarui Pesanan' : 'Tambahkan ke Pesanan'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-4">Silakan pilih metode pembayaran dan masukkan jumlah yang dibayarkan</p>
                    
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3 mb-3" style="background-color: #fde8e8;">
                        <span class="fw-bold text-dark">Total yang harus dibayar:</span>
                        <span class="fw-bold fs-4" style="color: #8b211e;" x-text="formatRupiah(grandTotal)"></span>
                    </div>

                    <!-- Informasi Pelanggan & Meja di Modal Pembayaran -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="small fw-bold mb-1 text-muted"><i class="bi bi-person me-1"></i> Nama Pelanggan</label>
                            <input type="text" class="form-control shadow-none" placeholder="Pelanggan Umum (cth: Budi)" x-model="customerName">
                        </div>
                        <div class="col-5">
                            <label class="small fw-bold mb-1 text-muted"><i class="bi bi-geo-alt me-1"></i> Meja</label>
                            <select class="form-select shadow-none" x-model="selectedTableId">
                                <template x-for="tbl in tables" :key="tbl.id">
                                    <option :value="tbl.id" x-text="tbl.name || ('Meja ' + tbl.table_number)"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="small fw-bold mb-2">Metode pembayaran</div>
                        <div class="row g-2">
                            <div class="col-4">
                                <button class="btn bg-white w-100 py-2 rounded-3 text-dark" 
                                        :style="paymentMethod === 'tunai' ? 'border: 2px solid #8b211e;' : 'border: 1px solid #dee2e6;'"
                                        @click="paymentMethod = 'tunai'">
                                    <i class="bi bi-cash" :style="paymentMethod === 'tunai' ? 'color:#8b211e;' : 'color:#6c757d;'"></i> 
                                    <span class="fw-bold ms-1" :style="paymentMethod === 'tunai' ? 'color:#8b211e; font-size:0.8rem;' : 'color:#6c757d; font-size:0.8rem; font-weight: normal !important;'">Tunai</span>
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn bg-white w-100 py-2 rounded-3 text-dark"
                                        :style="paymentMethod === 'qris' ? 'border: 2px solid #8b211e;' : 'border: 1px solid #dee2e6;'"
                                        @click="paymentMethod = 'qris'">
                                    <i class="bi bi-qr-code-scan" :style="paymentMethod === 'qris' ? 'color:#8b211e;' : 'color:#6c757d;'"></i> 
                                    <span class="fw-bold ms-1" :style="paymentMethod === 'qris' ? 'color:#8b211e; font-size:0.8rem;' : 'color:#6c757d; font-size:0.8rem; font-weight: normal !important;'">QRIS</span>
                                </button>
                            </div>
                            <div class="col-4">
                                <button class="btn bg-white w-100 py-2 rounded-3 text-dark"
                                        :style="paymentMethod === 'debit' ? 'border: 2px solid #8b211e;' : 'border: 1px solid #dee2e6;'"
                                        @click="paymentMethod = 'debit'">
                                    <i class="bi bi-bank" :style="paymentMethod === 'debit' ? 'color:#8b211e;' : 'color:#6c757d;'"></i> 
                                    <span class="fw-bold ms-1" :style="paymentMethod === 'debit' ? 'color:#8b211e; font-size:0.8rem;' : 'color:#6c757d; font-size:0.8rem; font-weight: normal !important;'">Transfer (TF)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <template x-if="paymentMethod === 'tunai'">
                        <div class="mb-4">
                            <div class="small fw-bold mb-2">Jumlah bayar</div>
                            <div class="input-group input-group-lg mb-3 shadow-sm rounded-3">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="number" id="cashInput" class="form-control border-start-0 ps-0 shadow-none fw-bold text-dark" x-model.number="cashReceived" min="0">
                            </div>
                            
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <button class="btn btn-sm px-3 py-2 rounded-pill fw-bold" :class="cashReceived === 50000 ? 'btn-danger' : 'btn-outline-danger'" :style="cashReceived === 50000 ? 'background-color: #8b211e; border-color: #8b211e;' : 'color: #8b211e; border-color: #fca5a5;'" @click="cashReceived = 50000">Rp 50.000</button>
                                <button class="btn btn-sm px-3 py-2 rounded-pill fw-bold" :class="cashReceived === 100000 ? 'btn-danger' : 'btn-outline-danger'" :style="cashReceived === 100000 ? 'background-color: #8b211e; border-color: #8b211e;' : 'color: #8b211e; border-color: #fca5a5;'" @click="cashReceived = 100000">Rp 100.000</button>
                                <button class="btn btn-sm px-3 py-2 rounded-pill fw-bold" :class="cashReceived === 150000 ? 'btn-danger' : 'btn-outline-danger'" :style="cashReceived === 150000 ? 'background-color: #8b211e; border-color: #8b211e;' : 'color: #8b211e; border-color: #fca5a5;'" @click="cashReceived = 150000">Rp 150.000</button>
                                <button class="btn btn-sm px-3 py-2 rounded-pill fw-bold" :class="cashReceived === 200000 ? 'btn-danger' : 'btn-outline-danger'" :style="cashReceived === 200000 ? 'background-color: #8b211e; border-color: #8b211e;' : 'color: #8b211e; border-color: #fca5a5;'" @click="cashReceived = 200000">Rp 200.000</button>
                                <button class="btn btn-sm px-3 py-2 rounded-pill fw-bold" :class="cashReceived === grandTotal ? 'btn-danger' : 'btn-outline-danger'" :style="cashReceived === grandTotal ? 'background-color: #8b211e; border-color: #8b211e;' : 'color: #8b211e; border-color: #fca5a5;'" @click="cashReceived = grandTotal">Uang Pas</button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;" x-show="changeAmount >= 0">
                                <span class="small fw-bold text-muted">Kembalian:</span>
                                <span class="fw-bold text-success fs-5" x-text="formatRupiah(changeAmount)"></span>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="modal-footer border-top-0 pt-0 flex-column flex-sm-row gap-2">
                    <button type="button" class="btn w-100 rounded-3 text-white fw-bold py-2 m-0" style="background-color: #8b211e;" @click="checkout(true)" :disabled="isCheckoutDisabled">
                        <span x-show="!loading"><i class="bi bi-printer me-2"></i> Simpan & Cetak resi</span>
                        <span x-show="loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...</span>
                    </button>
                    <button type="button" class="btn w-100 rounded-3 bg-white fw-bold py-2 m-0 mt-2 mt-sm-0" style="border: 1px solid #8b211e; color: #8b211e;" @click="checkout(false)" :disabled="isCheckoutDisabled">
                        <span x-show="!loading">Simpan tanpa cetak resi</span>
                        <span x-show="loading"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Preview Struk (Thermal Receipt Preview) -->
    <div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-receipt-cutoff fs-4" style="color: #8b211e;"></i>
                        <div>
                            <h5 class="modal-title fw-bold mb-0">Preview Struk Pembayaran</h5>
                            <div class="small text-muted">Transaksi selesai dan tersimpan di sistem</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" @click="closeReceiptModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">
                        <!-- Kiri: Tampilan Kertas Struk Thermal Kasir -->
                        <div class="col-md-6 d-flex justify-content-center">
                            <div class="receipt-card bg-white p-4 shadow-sm text-dark w-100" style="max-width: 330px; font-family: 'Courier New', Courier, monospace; font-size: 12px; border: 1px dashed #cbd5e1; border-radius: 8px;">
                                <div class="text-center mb-2">
                                    <div class="fw-bold" style="font-size: 16px;">KEDAI DYNASTY</div>
                                    <div style="font-size: 11px;">Jl. Contoh No. 123</div>
                                    <div style="font-size: 10px;">Telp: 08123456789</div>
                                </div>
                                <div style="border-top: 1px dashed #475569; margin: 8px 0;"></div>
                                
                                <div style="font-size: 11px; line-height: 1.4;">
                                    <div class="d-flex justify-content-between">
                                        <span>No:</span>
                                        <span class="fw-bold" x-text="completedOrder ? completedOrder.nomor_pesanan : '-'"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Waktu:</span>
                                        <span x-text="completedOrder ? completedOrder.time : '-'"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kasir:</span>
                                        <span>{{ Auth::user()->name }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pelanggan:</span>
                                        <span class="fw-bold" x-text="completedOrder ? completedOrder.customerName : 'Pelanggan Umum'"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Meja:</span>
                                        <span class="fw-bold" x-text="completedOrder ? completedOrder.tableName : '-'"></span>
                                    </div>
                                </div>
                                <div style="border-top: 1px dashed #475569; margin: 8px 0;"></div>
                                
                                <!-- Rincian Produk -->
                                <div class="my-2" style="max-height: 220px; overflow-y: auto;">
                                    <template x-if="completedOrder">
                                        <template x-for="item in completedOrder.items" :key="item.cartId">
                                            <div class="mb-2">
                                                <div class="fw-bold" x-text="item.product.nama"></div>
                                                <template x-if="item.selectedOptions && item.selectedOptions.length > 0">
                                                    <div style="font-size: 10px; color: #64748b; padding-left: 6px;">
                                                        <template x-for="opt in item.selectedOptions" :key="opt.id">
                                                            <div x-text="'+ ' + opt.nama"></div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <div class="d-flex justify-content-between" style="font-size: 11px;">
                                                    <span x-text="item.quantity + ' x ' + formatRupiah(item.unitPrice)"></span>
                                                    <span class="fw-bold" x-text="formatRupiah(item.quantity * item.unitPrice)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </template>
                                </div>
                                
                                <div style="border-top: 1px dashed #475569; margin: 8px 0;"></div>
                                
                                <!-- Total Pembayaran -->
                                <div style="font-size: 11px; line-height: 1.5;">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total Harga:</span>
                                        <span x-text="completedOrder ? formatRupiah(completedOrder.total) : '0'"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Metode:</span>
                                        <span class="text-uppercase fw-semibold" x-text="completedOrder ? completedOrder.paymentMethod : '-'"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Bayar:</span>
                                        <span x-text="completedOrder ? formatRupiah(completedOrder.cashReceived) : '0'"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kembali:</span>
                                        <span class="fw-bold text-success" x-text="completedOrder ? formatRupiah(completedOrder.changeAmount) : '0'"></span>
                                    </div>
                                </div>
                                
                                <div style="border-top: 1px dashed #475569; margin: 8px 0;"></div>
                                <div class="text-center mt-2" style="font-size: 11px;">
                                    <div>Terima Kasih</div>
                                    <div>Silakan datang kembali</div>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: Wadah Modul Printer & Pengaturan -->
                        <div class="col-md-6 d-flex flex-column justify-content-between">
                            <div>
                                <!-- Wadah Modul Printer Card -->
                                <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="fw-bold d-flex align-items-center gap-2" style="color: #8b211e;">
                                            <i class="bi bi-cpu fs-5"></i> Wadah Modul Printer Struk
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Terkoneksi</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">Nama Printer (Hardware/Driver):</label>
                                        <input type="text" class="form-control form-control-sm" x-model="printerConfig.printerName" @input="savePrinterConfig()" placeholder="cth: POS-58, EPSON TM-T82">
                                        <div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">Tersimpan otomatis di browser kasir</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold mb-1">Modul Cetak Printer:</label>
                                        <select class="form-select form-select-sm" x-model="printerConfig.moduleType" @change="savePrinterConfig()">
                                            <option value="iframe_direct">Direct Browser / Silent (Tetap di Web)</option>
                                            <option value="esc_pos">ESC/POS USB Driver (Wadah Raw POS)</option>
                                            <option value="rawbt">Bluetooth / Android RawBT Module</option>
                                            <option value="custom_api">Local API / QZ Tray Print Daemon</option>
                                        </select>
                                        <div class="text-muted" style="font-size: 0.7rem; margin-top: 2px;">Pilih metode komunikasi dengan printer kasir</div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold mb-1">Lebar Kertas:</label>
                                            <select class="form-select form-select-sm" x-model="printerConfig.paperWidth" @change="savePrinterConfig()">
                                                <option value="58mm">58 mm (Thermal Standar)</option>
                                                <option value="80mm">80 mm (Thermal Lebar)</option>
                                            </select>
                                        </div>
                                        <div class="col-6 d-flex align-items-end">
                                            <div class="form-check form-switch mb-1">
                                                <input class="form-check-input" type="checkbox" id="autoPrintSwitch" x-model="printerConfig.autoPrint" @change="savePrinterConfig()">
                                                <label class="form-check-label small fw-semibold" for="autoPrintSwitch" style="font-size: 0.75rem;">Auto-Cetak</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Aksi Kasir -->
                            <div class="d-flex flex-column gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-lg w-100 fw-bold text-white shadow-sm" style="background-color: #8b211e;" @click="printCurrentReceipt()">
                                    <i class="bi bi-printer me-2"></i> Cetak Struk Sekarang
                                </button>
                                <button type="button" class="btn btn-light border w-100 fw-bold py-2" @click="closeReceiptModal()">
                                    <i class="bi bi-plus-circle me-1"></i> Transaksi Baru (Esc)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pengaturan Printer Struk Terpisah -->
    <div class="modal fade" id="printerSettingsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-gear-fill fs-5" style="color: #8b211e;"></i>
                        <h5 class="modal-title fw-bold mb-0">Pengaturan Wadah Printer Struk</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nama Printer (Hardware/Driver):</label>
                        <input type="text" class="form-control shadow-none" x-model="printerConfig.printerName" @input="savePrinterConfig()" placeholder="cth: POS-58, EPSON TM-T82, Panda POS">
                        <div class="form-text" style="font-size: 0.75rem;">Nama printer fisik yang terpasang di komputer kasir.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Modul Komunikasi Printer:</label>
                        <select class="form-select shadow-none" x-model="printerConfig.moduleType" @change="savePrinterConfig()">
                            <option value="iframe_direct">Direct Browser / Silent (Tetap di Web)</option>
                            <option value="esc_pos">ESC/POS USB Driver (Wadah Raw POS)</option>
                            <option value="rawbt">Bluetooth / Android RawBT Module</option>
                            <option value="custom_api">Local API / QZ Tray Print Daemon</option>
                        </select>
                        <div class="form-text" style="font-size: 0.75rem;">Gunakan 'Direct Browser' untuk mencetak langsung tanpa keluar dari web.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Ukuran Kertas:</label>
                            <select class="form-select shadow-none" x-model="printerConfig.paperWidth" @change="savePrinterConfig()">
                                <option value="58mm">58 mm (Standar)</option>
                                <option value="80mm">80 mm (Besar)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Auto-Cetak:</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="autoPrintSwitchGlobal" x-model="printerConfig.autoPrint" @change="savePrinterConfig()">
                                <label class="form-check-label small" for="autoPrintSwitchGlobal">Cetak otomatis</label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="small fw-bold mb-1 text-muted">File Modul Printer:</div>
                        <code class="small text-dark">public/js/receipt-printer.js</code>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">Anda dapat menyesuaikan integrasi nama modul atau protokol printer langsung di file tersebut.</div>
                    </div>

                    <button type="button" class="btn btn-outline-secondary w-100 fw-bold" @click="testPrint()">
                        <i class="bi bi-printer me-1"></i> Uji Coba Cetak (Test Print)
                    </button>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn text-white w-100 fw-bold" style="background-color: #8b211e;" data-bs-dismiss="modal">Simpan & Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pesanan Meja Masuk (Self-Order) -->
    <div class="modal fade" id="tableOrdersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 py-3" style="background-color: #fdfaf7;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center text-white" style="background-color: #8b211e; width: 38px; height: 38px;">
                            <i class="bi bi-bell-fill fs-6"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark">Pesanan Meja Masuk (Self-Order)</h5>
                            <div class="text-muted small">Kelola status dan respon pesanan langsung dari meja customer</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1.5 shadow-xs"
                            @click="testVoiceNotification()" title="Uji Suara Panggilan dan Bel">
                            <i class="bi bi-volume-up-fill"></i> Test Suara
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Filter Status Pills -->
                    <div class="d-flex gap-2 mb-3 overflow-auto pb-1">
                        <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold"
                            :class="tableOrderFilter === 'all' ? 'text-white' : 'btn-white bg-white text-dark border'"
                            :style="tableOrderFilter === 'all' ? 'background-color: #8b211e;' : ''"
                            @click="tableOrderFilter = 'all'">
                            Semua (<span x-text="tableOrders.length"></span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold"
                            :class="tableOrderFilter === 'menunggu_pembayaran' ? 'text-white' : 'btn-white bg-white text-dark border'"
                            :style="tableOrderFilter === 'menunggu_pembayaran' ? 'background-color: #8b211e;' : ''"
                            @click="tableOrderFilter = 'menunggu_pembayaran'">
                            Menunggu Pembayaran (<span x-text="tableOrders.filter(o => o.status === 'menunggu_pembayaran' || o.status === 'menunggu_konfirmasi').length"></span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold"
                            :class="tableOrderFilter === 'diproses' ? 'text-white' : 'btn-white bg-white text-dark border'"
                            :style="tableOrderFilter === 'diproses' ? 'background-color: #8b211e;' : ''"
                            @click="tableOrderFilter = 'diproses'">
                            Sedang Dimasak (<span x-text="tableOrders.filter(o => o.status === 'diproses').length"></span>)
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 fw-bold"
                            :class="tableOrderFilter === 'disajikan' ? 'text-white' : 'btn-white bg-white text-dark border'"
                            :style="tableOrderFilter === 'disajikan' ? 'background-color: #8b211e;' : ''"
                            @click="tableOrderFilter = 'disajikan'">
                            Sudah Dikirim ke Meja (<span x-text="tableOrders.filter(o => o.status === 'disajikan').length"></span>)
                        </button>
                    </div>

                    <!-- Empty State -->
                    <template x-if="filteredTableOrders.length === 0">
                        <div class="text-center py-5 bg-white rounded-4 border">
                            <div class="rounded-circle bg-light d-inline-flex p-3 mb-2 text-muted">
                                <i class="bi bi-inbox fs-1"></i>
                            </div>
                            <h6 class="fw-bold text-dark">Tidak Ada Pesanan Meja</h6>
                            <p class="text-muted small mb-0">Pesanan yang dikirim oleh customer dari meja akan otomatis muncul di sini.</p>
                        </div>
                    </template>

                    <!-- Order Cards Grid -->
                    <div class="d-flex flex-column gap-3">
                        <template x-for="order in filteredTableOrders" :key="order.id">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge px-2.5 py-1.5 rounded-3 text-white fw-bold fs-6" style="background-color: #8b211e;">
                                            <i class="bi bi-grid-fill me-1"></i> Meja <span x-text="order.meja_nomor"></span>
                                        </span>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <div class="fw-bold text-dark small" x-text="order.nomor_pesanan"></div>
                                                <span class="badge rounded-pill px-2.5 py-1 text-dark fw-bold border" style="background-color: #fef3c7; border-color: #fde68a !important; font-size: 0.78rem;">
                                                    <i class="bi bi-person-fill text-amber-800 me-1"></i> <span x-text="order.nama_pelanggan || 'Pelanggan'"></span>
                                                </span>
                                            </div>
                                            <div class="text-muted d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.7rem;">
                                                <span><i class="bi bi-clock me-1"></i> Pukul <span x-text="order.waktu"></span> WIB</span>
                                                <template x-if="order.catatan_khusus">
                                                    <span class="text-danger fw-semibold">
                                                        <i class="bi bi-chat-left-text-fill me-1"></i>Catatan: "<span x-text="order.catatan_khusus"></span>"
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <!-- Status Badge -->
                                        <template x-if="order.status === 'menunggu_pembayaran' || order.status === 'menunggu_konfirmasi'">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2.5 py-1 rounded-pill fw-bold small d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-hourglass-split"></i> <span>Menunggu Pembayaran (Belum Bayar)</span>
                                            </span>
                                        </template>
                                        <template x-if="order.status === 'diproses'">
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info px-2.5 py-1 rounded-pill fw-bold small d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-fire"></i> <span>Sedang Dimasak <span x-text="order.metode_pembayaran ? '(Lunas ' + order.metode_pembayaran.toUpperCase() + ')' : '(Lunas)'"></span></span>
                                            </span>
                                        </template>
                                        <template x-if="order.status === 'disajikan'">
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success px-2.5 py-1 rounded-pill fw-bold small d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-check2-circle"></i> <span>Sudah Dikirim ke Meja <span x-text="order.metode_pembayaran ? '(' + order.metode_pembayaran.toUpperCase() + ')' : ''"></span></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <div class="card-body p-3 bg-white">
                                    <!-- Items List -->
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <template x-for="item in order.items" :key="item.id">
                                                    <tr class="border-bottom border-light">
                                                        <td class="py-2" style="width: 35px;">
                                                             <span class="badge bg-light text-dark border fw-bold" x-text="item.jumlah + 'x'"></span>
                                                        </td>
                                                        <td class="py-2">
                                                            <div class="fw-bold text-dark small" x-text="item.nama_produk"></div>
                                                            <!-- Modifiers / Toppings -->
                                                            <template x-if="item.modifiers && item.modifiers.length > 0">
                                                                <div class="d-flex flex-wrap gap-1 mt-0.5">
                                                                    <template x-for="mod in item.modifiers" :key="mod.option_id">
                                                                        <span class="badge bg-danger-subtle text-danger small py-0.5 px-1.5 rounded" style="font-size: 0.65rem;" x-text="'+ ' + mod.option_nama"></span>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <!-- Item Note -->
                                                            <template x-if="item.catatan">
                                                                <div class="text-danger fst-italic mt-0.5" style="font-size: 0.72rem;">
                                                                    <i class="bi bi-chat-left-dots me-1"></i><span x-text="'&quot;' + item.catatan + '&quot;'"></span>
                                                                </div>
                                                            </template>
                                                        </td>
                                                        <td class="py-2 text-end fw-bold text-dark small" x-text="formatRupiah(item.subtotal)"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Total and Actions Row -->
                                    <div class="d-flex flex-wrap align-items-center justify-content-between pt-3 mt-2 border-top gap-2">
                                        <div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Total Tagihan:</div>
                                            <div class="fw-black fs-5" style="color: #8b211e;" x-text="formatRupiah(order.total_harga)"></div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <!-- Tombol Putar Suara Notifikasi -->
                                            <button type="button" class="btn btn-sm btn-light border rounded-3 fw-bold px-2.5 py-1.5 d-flex align-items-center gap-1 shadow-2xs"
                                                @click="announceOrder(order)" title="Dengarkan Kembali Panggilan Suara Pesanan Ini">
                                                <i class="bi bi-volume-up-fill text-danger"></i>
                                                <span style="font-size: 0.75rem;">Panggil Suara</span>
                                            </button>
                                            <!-- Tombol Terima Pembayaran jika Belum Bayar -->
                                            <template x-if="order.status === 'menunggu_pembayaran' || order.status === 'menunggu_konfirmasi'">
                                                <button type="button" class="btn btn-sm text-white rounded-3 fw-bold px-3 py-1.5 shadow-sm d-flex align-items-center gap-1.5"
                                                    style="background-color: #8b211e;"
                                                    @click="openPayTableOrderModal(order)">
                                                    <i class="bi bi-credit-card-fill"></i>
                                                    <span>Terima Pembayaran di Kasir</span>
                                                </button>
                                            </template>

                                            <!-- Tombol Mulai Masak (Opsional jika ingin masak duluan) -->
                                            <template x-if="order.status === 'menunggu_pembayaran' || order.status === 'menunggu_konfirmasi'">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 fw-bold px-2.5 py-1.5 d-flex align-items-center gap-1.5"
                                                    title="Mulai masak tanpa menunggu pembayaran di awal"
                                                    @click="updateOrderStatus(order.id, 'diproses')">
                                                    <i class="bi bi-fire"></i>
                                                    <span>Mulai Masak</span>
                                                </button>
                                            </template>

                                            <!-- Tombol Sudah Dikirim jika Sedang Dimasak -->
                                            <template x-if="order.status === 'diproses'">
                                                <button type="button" class="btn btn-sm btn-success text-white rounded-3 fw-bold px-3 py-1.5 shadow-sm d-flex align-items-center gap-1.5"
                                                    @click="updateOrderStatus(order.id, 'disajikan')">
                                                    <i class="bi bi-check2-circle"></i>
                                                    <span>Sudah Dikirim ke Meja</span>
                                                </button>
                                            </template>

                                            <!-- Tombol Selesaikan Pesanan jika Sudah Dikirim -->
                                            <template x-if="order.status === 'disajikan'">
                                                <button type="button" class="btn btn-sm btn-outline-dark rounded-3 fw-bold px-3 py-1.5 shadow-sm d-flex align-items-center gap-1.5"
                                                    @click="updateOrderStatus(order.id, 'selesai')">
                                                    <i class="bi bi-check-all"></i>
                                                    <span>Selesaikan Pesanan</span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pembayaran Pesanan Meja Masuk -->
    <div class="modal fade" id="tableOrderPayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom px-4 py-3">
                    <h6 class="modal-title fw-bold mb-0">Pembayaran Pesanan Meja</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" x-if="payingTableOrder">
                    <div class="text-center mb-3">
                        <div class="badge px-3 py-1 bg-light text-dark border mb-1 fw-bold fs-6">
                            Meja <span x-text="payingTableOrder?.meja_nomor"></span>
                        </div>
                        <div class="text-muted small" x-text="payingTableOrder?.nomor_pesanan"></div>
                        <div class="fw-black fs-3 my-2" style="color: #8b211e;" x-text="formatRupiah(payingTableOrder?.total_harga || 0)"></div>
                    </div>

                    <!-- Payment Method Radio -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Metode Pembayaran</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm flex-fill border fw-bold d-flex align-items-center justify-content-center gap-1"
                                :class="tablePayMethod === 'tunai' ? 'btn-dark' : 'btn-light'"
                                @click="tablePayMethod = 'tunai'; tablePayCashReceived = payingTableOrder?.total_harga || 0">
                                <i class="bi bi-cash"></i> Tunai
                            </button>
                            <button type="button" class="btn btn-sm flex-fill border fw-bold d-flex align-items-center justify-content-center gap-1"
                                :class="tablePayMethod === 'qris' ? 'btn-dark' : 'btn-light'"
                                @click="tablePayMethod = 'qris'; tablePayCashReceived = payingTableOrder?.total_harga || 0">
                                <i class="bi bi-qr-code-scan"></i> QRIS
                            </button>
                            <button type="button" class="btn btn-sm flex-fill border fw-bold d-flex align-items-center justify-content-center gap-1"
                                :class="tablePayMethod === 'debit' ? 'btn-dark' : 'btn-light'"
                                @click="tablePayMethod = 'debit'; tablePayCashReceived = payingTableOrder?.total_harga || 0">
                                <i class="bi bi-bank"></i> Transfer (TF)
                            </button>
                        </div>
                    </div>

                    <!-- Cash Received (if Tunai) -->
                    <div class="mb-3" x-show="tablePayMethod === 'tunai'">
                        <label class="form-label small fw-bold text-muted">Uang Diterima (Rp)</label>
                        <input type="number" class="form-control rounded-3" x-model.number="tablePayCashReceived">
                        <div class="d-flex justify-content-between mt-2 small">
                            <span class="text-muted">Kembalian:</span>
                            <span class="fw-bold text-success" x-text="formatRupiah(Math.max(0, (tablePayCashReceived || 0) - (payingTableOrder?.total_harga || 0)))"></span>
                        </div>
                    </div>

                    <button type="button" class="btn text-white w-100 fw-bold py-2 rounded-3 shadow-sm"
                        style="background-color: #8b211e;"
                        :disabled="isSubmittingTablePay"
                        @click="submitTableOrderPayment()">
                        <span x-text="isSubmittingTablePay ? 'Memproses...' : 'Selesaikan Pembayaran'"></span>
                    </button>
                </div>
            </div>
        </div>
    <!-- Floating Audio Alert saat Pembayaran Masuk (Bantu Buka Izin Audio Browser) -->
    <div x-show="pendingOrderToAnnounce" x-cloak class="position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg" style="z-index: 1085;">
        <button type="button" @click="openTableOrdersModal()" class="btn btn-success rounded-pill px-4 py-2.5 fw-bold d-flex align-items-center gap-2 border border-2 border-white shadow-lg">
            <i class="bi bi-bell-fill fs-5 text-white"></i>
            <span>Pembayaran Meja Masuk! Klik untuk Dengar Suara & Buka</span>
        </button>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/receipt-printer.js') }}"></script>
<script>
window.posSystemActive = true;
document.addEventListener('alpine:init', () => {
    Alpine.data('posSystem', () => ({
        categories: window.posData.categories,
        products: window.posData.products,
        tables: window.posData.tables,
        
        searchQuery: '',
        activeCategoryId: null,
        customerName: '',
        selectedTableId: (window.posData.tables && window.posData.tables.length > 0) ? window.posData.tables[0].id : 1,
        
        cart: [],
        
        // Form Controls
        discountPercent: 0,
        ppnEnabled: true,
        paymentMethod: 'tunai', // tunai, qris, debit
        cashReceived: 0,
        
        loading: false,
        errorMsg: '',
        successMsg: '',
        
        currentTime: '',
        
        modalProduct: null,
        modalSelections: {}, // Format: { groupId: [optId1, optId2] }
        modalCatatan: '',
        editingCartIndex: null,
        modifierModalInstance: null,
        paymentModalInstance: null,
        receiptModalInstance: null,
        printerModalInstance: null,
        
        // Data Struk Transaksi Terakhir & Pengaturan Printer
        completedOrder: null,
        printerConfig: {
            printerName: localStorage.getItem('pos_printer_name') || 'POS-58 Thermal Printer',
            moduleType: localStorage.getItem('pos_printer_module') || 'iframe_direct',
            paperWidth: localStorage.getItem('pos_paper_width') || '58mm',
            autoPrint: localStorage.getItem('pos_auto_print') === 'true'
        },
        
        showToast: false,
        toastMessage: 'Produk berhasil ditambahkan',
        toastType: 'success',
        toastTimeout: null,
        cartDrawerOpen: false,
        
        // Pesanan Meja Masuk (Self-Order)
        tableOrders: [],
        tableOrderFilter: 'all',
        payingTableOrder: null,
        tablePayMethod: 'tunai',
        tablePayCashReceived: 0,
        isSubmittingTablePay: false,
        lastTableOrderCount: 0,
        pendingOrderToAnnounce: null,
        userHasInteracted: false,
        audioCtx: null,
        tableOrdersModalInstance: null,
        tableOrderPayModalInstance: null,
        tablePollInterval: null,

        get filteredTableOrders() {
            if (this.tableOrderFilter === 'all') return this.tableOrders;
            if (this.tableOrderFilter === 'menunggu_pembayaran') {
                return this.tableOrders.filter(o => o.status === 'menunggu_pembayaran' || o.status === 'menunggu_konfirmasi');
            }
            return this.tableOrders.filter(o => o.status === this.tableOrderFilter);
        },
        
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            
            // Wait for Bootstrap to be available
            setTimeout(() => {
                if (typeof bootstrap !== 'undefined') {
                    const modModalEl = document.getElementById('modifierModal');
                    if (modModalEl) {
                        this.modifierModalInstance = new bootstrap.Modal(modModalEl);
                        modModalEl.addEventListener('hidden.bs.modal', () => {
                            this.modalProduct = null;
                            this.modalSelections = {};
                            this.modalCatatan = '';
                            this.editingCartIndex = null;
                        });
                    }

                    const payModalEl = document.getElementById('paymentModal');
                    if (payModalEl) {
                        this.paymentModalInstance = new bootstrap.Modal(payModalEl);
                        payModalEl.addEventListener('shown.bs.modal', () => {
                            if (this.paymentMethod === 'tunai') {
                                document.getElementById('cashInput').focus();
                            }
                        });
                    }

                    const recModalEl = document.getElementById('receiptPreviewModal');
                    if (recModalEl) {
                        this.receiptModalInstance = new bootstrap.Modal(recModalEl);
                    }

                    const prnModalEl = document.getElementById('printerSettingsModal');
                    if (prnModalEl) {
                        this.printerModalInstance = new bootstrap.Modal(prnModalEl);
                    }

                    const tblModalEl = document.getElementById('tableOrdersModal');
                    if (tblModalEl) {
                        this.tableOrdersModalInstance = new bootstrap.Modal(tblModalEl);
                    }

                    const tblPayModalEl = document.getElementById('tableOrderPayModal');
                    if (tblPayModalEl) {
                        this.tableOrderPayModalInstance = new bootstrap.Modal(tblPayModalEl);
                    }

                    // Polling pesanan meja masuk setiap 4 detik
                    this.fetchActiveTableOrders();
                    if (!this.tablePollInterval) {
                        this.tablePollInterval = setInterval(() => {
                            this.fetchActiveTableOrders();
                        }, 4000);
                    }
                }
            }, 100);

            // Listener otomatis meng-unlock audio browser saat kasir melakukan interaksi pertama
            const unlockAudioOnGesture = () => {
                this.userHasInteracted = true;
                if (this.audioCtx && this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
                if ('speechSynthesis' in window && window.speechSynthesis.paused) {
                    window.speechSynthesis.resume();
                }
                if (this.pendingOrderToAnnounce) {
                    const order = this.pendingOrderToAnnounce;
                    this.pendingOrderToAnnounce = null;
                    this.announceOrder(order);
                }
            };
            ['click', 'touchstart', 'keydown'].forEach(evt => {
                window.addEventListener(evt, unlockAudioOnGesture, { passive: true });
            });

            // Global Keyboard Shortcuts
            window.addEventListener('keydown', (e) => {
                // F2: Fokus Search
                if (e.key === 'F2') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
                // F9: Bayar
                else if (e.key === 'F9') {
                    e.preventDefault();
                    if (!document.getElementById('btnBayar').disabled) {
                        this.openPaymentModal();
                    }
                }
                // Esc: Tutup Modal Struk jika sedang terbuka
                else if (e.key === 'Escape') {
                    if (this.completedOrder && this.receiptModalInstance) {
                        this.closeReceiptModal();
                    }
                }
            });
        },
        
        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        },
        
        get filteredProducts() {
            return this.products.filter(p => {
                const matchCat = this.activeCategoryId === null || p.kategori_id === this.activeCategoryId;
                const matchSearch = p.nama.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            });
        },
        
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.unitPrice * item.quantity), 0);
        },

        get totalCartItems() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        get grandTotal() {
            return this.subtotal;
        },

        openCartDrawer() {
            this.cartDrawerOpen = true;
            const cartEl = document.querySelector('.pos-cart');
            const backdropEl = document.getElementById('posCartBackdrop');
            if (cartEl) cartEl.classList.add('show');
            if (backdropEl) backdropEl.classList.add('show');
        },

        closeCartDrawer() {
            this.cartDrawerOpen = false;
            const cartEl = document.querySelector('.pos-cart');
            const backdropEl = document.getElementById('posCartBackdrop');
            if (cartEl) cartEl.classList.remove('show');
            if (backdropEl) backdropEl.classList.remove('show');
        },

        get changeAmount() {
            if (this.paymentMethod !== 'tunai') return 0;
            return (this.cashReceived || 0) - this.grandTotal;
        },

        get isCheckoutDisabled() {
            if (this.cart.length === 0 || this.loading) return true;
            if (this.paymentMethod === 'tunai' && this.changeAmount < 0) return true;
            return false;
        },
        
        formatRupiah(number) {
            return 'Rp ' + Math.round(number).toLocaleString('id-ID');
        },

        getProductImage(product) {
            if (!product) return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80';
            if (product.gambar_url) return product.gambar_url;
            if (product.gambar) {
                if (product.gambar.startsWith('http')) return product.gambar;
                if (product.gambar.startsWith('images/')) return '/' + product.gambar;
                return '/storage/' + product.gambar;
            }
            const name = (product.nama || '').toLowerCase();
            if (name.includes('americano')) return '/images/produk/americano.jpg';
            if (name.includes('latte') && !name.includes('matcha')) return '/images/produk/latte.jpg';
            if (name.includes('cappuccino')) return '/images/produk/cappuccino.jpg';
            if (name.includes('matcha')) return '/images/produk/matcha_latte.jpg';
            if (name.includes('choc') || name.includes('coklat')) return '/images/produk/chocolate.jpg';
            if (name.includes('sandwich')) return '/images/produk/sandwich.jpg';
            if (name.includes('croissant')) return '/images/produk/croissant.jpg';
            if (name.includes('fries') || name.includes('kentang')) return '/images/produk/french_fries.jpg';

            const cat = (product.kategori?.nama || '').toLowerCase();
            if (cat.includes('coffee') && !cat.includes('non')) return '/images/produk/americano.jpg';
            if (cat.includes('non coffee')) return '/images/produk/matcha_latte.jpg';
            if (cat.includes('food') || cat.includes('makanan')) return '/images/produk/sandwich.jpg';
            if (cat.includes('snack')) return '/images/produk/french_fries.jpg';

            return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
        },
        
        handleProductClick(product) {
            if (product.stok <= 0) {
                this.triggerToast('Maaf, stok "' + product.nama + '" sedang habis!', 'warning', 3000);
                return;
            }
            this.openProductModal(product);
        },

        openProductModal(product) {
            if (product.stok <= 0) {
                this.triggerToast('Maaf, stok "' + product.nama + '" sedang habis!', 'warning', 3000);
                return;
            }
            this.editingCartIndex = null;
            this.modalCatatan = '';
            
            // Check if product has active modifier groups
            if (!product.modifier_groups || product.modifier_groups.length === 0) {
                // Direct add to cart
                this.addDirectToCart(product);
                return;
            }
            
            this.modalProduct = product;
            this.modalSelections = {};
            // Init empty arrays for selections
            product.modifier_groups.forEach(g => {
                this.modalSelections[g.id] = [];
            });
            
            // Clear inputs visually
            setTimeout(() => {
                document.querySelectorAll('#modifierModal input[type="checkbox"], #modifierModal input[type="radio"]').forEach(input => input.checked = false);
            }, 10);
            
            this.modifierModalInstance.show();
        },

        openEditCartModal(index) {
            const item = this.cart[index];
            if (!item) return;

            this.editingCartIndex = index;
            this.modalProduct = item.product;
            this.modalCatatan = item.catatan || '';
            this.modalSelections = {};

            // Populate existing selections
            if (item.product.modifier_groups && item.product.modifier_groups.length > 0) {
                item.product.modifier_groups.forEach(g => {
                    const selectedInGroup = (item.selectedOptions || [])
                        .filter(opt => opt.modifier_group_id == g.id)
                        .map(opt => opt.id);
                    this.modalSelections[g.id] = selectedInGroup;
                });
            }

            // Sync input checkmarks in DOM
            setTimeout(() => {
                document.querySelectorAll('#modifierModal input[type="checkbox"], #modifierModal input[type="radio"]').forEach(input => {
                    const val = parseInt(input.value, 10);
                    const isSelected = Object.values(this.modalSelections).some(arr => arr.includes(val));
                    input.checked = isSelected;
                });
            }, 20);

            this.modifierModalInstance.show();
        },
        
        isOptionSelected(groupId, optId) {
            return this.modalSelections[groupId] && this.modalSelections[groupId].includes(optId);
        },
        
        toggleOption(group, opt, event) {
            const isChecked = event.target.checked;
            let currentSelections = this.modalSelections[group.id] || [];
            
            if (group.max_pilihan === 1) {
                // Radio behavior
                if (isChecked) {
                    this.modalSelections[group.id] = [opt.id];
                } else {
                    this.modalSelections[group.id] = [];
                }
            } else {
                // Checkbox behavior
                if (isChecked) {
                    if (currentSelections.length >= group.max_pilihan) {
                        event.target.checked = false; // Prevent checking
                        alert('Maksimal ' + group.max_pilihan + ' pilihan untuk ' + group.nama);
                        return;
                    }
                    currentSelections.push(opt.id);
                } else {
                    currentSelections = currentSelections.filter(id => id !== opt.id);
                }
                this.modalSelections[group.id] = currentSelections;
            }
        },
        
        addDirectToCart(product) {
            if (product.stok <= 0) {
                this.triggerToast('Maaf, stok "' + product.nama + '" sedang habis!', 'warning', 3000);
                return;
            }
            this.addToCartInternal(product, [], '');
        },

        saveModalItem() {
            // Validate selections
            if (this.modalProduct && this.modalProduct.modifier_groups) {
                for (const group of this.modalProduct.modifier_groups) {
                    const selections = this.modalSelections[group.id] || [];
                    if (group.wajib_diisi && selections.length === 0) {
                        alert('Harap isi ' + group.nama);
                        return;
                    }
                    if (selections.length > 0 && selections.length < group.min_pilihan) {
                        alert('Minimal ' + group.min_pilihan + ' pilihan untuk ' + group.nama);
                        return;
                    }
                }
            }
            
            // Collect all selected options
            let selectedOptions = [];
            if (this.modalProduct && this.modalProduct.modifier_groups) {
                for (const groupId in this.modalSelections) {
                    const group = this.modalProduct.modifier_groups.find(g => g.id == groupId);
                    if (group) {
                        const optIds = this.modalSelections[groupId];
                        for (const optId of optIds) {
                            const opt = group.options.find(o => o.id == optId);
                            if (opt) selectedOptions.push(opt);
                        }
                    }
                }
            }

            const note = (this.modalCatatan || '').trim();

            if (this.editingCartIndex !== null && this.cart[this.editingCartIndex]) {
                // Mode Edit: Update item yang sudah ada di keranjang
                const optIds = selectedOptions.map(o => o.id).sort((a,b) => a-b);
                const cartId = this.modalProduct.id + '-' + optIds.join('-') + (note ? '-' + note : '');
                let addons = selectedOptions.reduce((sum, opt) => sum + opt.harga_tambahan, 0);
                let unitPrice = this.modalProduct.harga + addons;

                this.cart[this.editingCartIndex].cartId = cartId;
                this.cart[this.editingCartIndex].selectedOptions = selectedOptions;
                this.cart[this.editingCartIndex].unitPrice = unitPrice;
                this.cart[this.editingCartIndex].catatan = note;

                this.editingCartIndex = null;
            } else {
                // Mode Tambah Baru
                this.addToCartInternal(this.modalProduct, selectedOptions, note);
            }
            
            this.modifierModalInstance.hide();
        },
        
        addToCartFromModal() {
            this.saveModalItem();
        },
        
        addToCartInternal(product, selectedOptions, note = '') {
            if (product.stok <= 0) {
                this.triggerToast('Maaf, stok "' + product.nama + '" sedang habis!', 'warning', 3000);
                return;
            }

            // Calculate cartId (product_id + sorted option ids + note)
            const optIds = selectedOptions.map(o => o.id).sort((a,b) => a-b);
            const cartId = product.id + '-' + optIds.join('-') + (note ? '-' + note : '');
            
            // Calculate unit price based on options
            let addons = selectedOptions.reduce((sum, opt) => sum + opt.harga_tambahan, 0);
            let unitPrice = product.harga + addons;
            
            // Find existing
            const existingIndex = this.cart.findIndex(i => i.cartId === cartId);
            if (existingIndex >= 0) {
                if (this.cart[existingIndex].quantity + 1 > product.stok) {
                    this.triggerToast('Stok tidak mencukupi! Sisa stok ' + product.nama + ': ' + product.stok, 'warning', 3000);
                    return;
                }
                this.cart[existingIndex].quantity += 1;
            } else {
                this.cart.push({
                    cartId: cartId,
                    product: product,
                    selectedOptions: selectedOptions,
                    quantity: 1,
                    unitPrice: unitPrice,
                    catatan: note,
                    showNote: false
                });
            }
            
            // Show toast
            this.triggerToast('Produk berhasil ditambahkan', 'success', 2500);
        },
        
        updateQuantity(index, delta) {
            const item = this.cart[index];
            if (delta > 0) {
                if (item.product && item.quantity + delta > item.product.stok) {
                    this.triggerToast('Batas stok tercapai! Sisa stok ' + item.product.nama + ': ' + item.product.stok, 'warning', 3000);
                    return;
                }
                item.quantity += delta;
            } else if (item.quantity + delta > 0) {
                item.quantity += delta;
            } else {
                this.removeFromCart(index);
            }
        },
        
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        
        clearCart() {
            if (confirm('Hapus semua isi keranjang?')) {
                this.cart = [];
                this.errorMsg = '';
                this.successMsg = '';
                this.discountPercent = 0;
                this.cashReceived = 0;
            }
        },

        openPaymentModal() {
            if (this.cart.length === 0) return;
            this.closeCartDrawer();
            // Pre-fill cash if it's currently 0 or less than grandTotal
            if (this.paymentMethod === 'tunai' && this.cashReceived < this.grandTotal) {
                this.cashReceived = this.grandTotal;
            }
            this.paymentModalInstance.show();
        },
        
        async checkout(cetakResi = false) {
            if (this.isCheckoutDisabled) return;
            
            this.loading = true;
            this.errorMsg = '';
            this.successMsg = '';
            
            // Auto pick selected table or first table
            const defaultTableId = this.tables.length > 0 ? this.tables[0].id : 1;
            const targetTableId = this.selectedTableId || defaultTableId;
            const custName = (this.customerName && this.customerName.trim()) ? this.customerName.trim() : 'Pelanggan Umum';
            
            let fullCatatan = custName + ' (Via ' + this.paymentMethod.toUpperCase();
            if (this.paymentMethod === 'tunai') {
                fullCatatan += ' - Rp ' + this.cashReceived;
            }
            fullCatatan += ')';
            
            // Build payload
            const payload = {
                meja_id: targetTableId,
                catatan: fullCatatan,
                payment_method: this.paymentMethod,
                cash_received: this.cashReceived || this.grandTotal,
                items: this.cart.map(item => ({
                    produk_id: item.product.id,
                    jumlah: item.quantity,
                    catatan: item.catatan,
                    modifiers: item.selectedOptions.map(o => o.id)
                }))
            };
            
            try {
                const response = await fetch(window.posData.checkoutUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.posData.csrfToken
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    this.paymentModalInstance.hide();
                    this.closeCartDrawer();

                    // Simpan data untuk preview struk langsung di web
                    const selectedTableObj = this.tables.find(t => t.id == targetTableId);
                    const tableName = selectedTableObj ? (selectedTableObj.name || ('Meja ' + selectedTableObj.table_number)) : 'Meja -';
                    
                    const now = new Date();
                    const formattedDate = now.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + 
                                          now.toLocaleTimeString('id-ID', { hour12: false, hour: '2-digit', minute: '2-digit' });

                    const currentCart = JSON.parse(JSON.stringify(this.cart));
                    const currentTotal = this.grandTotal;
                    const currentMethod = this.paymentMethod;
                    const currentReceived = currentMethod === 'tunai' ? (this.cashReceived || this.grandTotal) : this.grandTotal;
                    const currentChange = currentMethod === 'tunai' ? Math.max(0, (this.cashReceived || this.grandTotal) - this.grandTotal) : 0;

                    this.completedOrder = {
                        pesanan_id: data.data.pesanan_id,
                        nomor_pesanan: data.data.nomor_pesanan,
                        customerName: custName,
                        tableName: tableName,
                        cashierName: '{{ Auth::user()->name }}',
                        items: currentCart,
                        total: currentTotal,
                        paymentMethod: currentMethod,
                        cashReceived: currentReceived,
                        changeAmount: currentChange,
                        time: formattedDate
                    };

                    this.successMsg = data.message + ' (Nomor: ' + data.data.nomor_pesanan + ')';
                    this.cart = []; // Reset cart
                    this.customerName = '';
                    this.discountPercent = 0;
                    this.cashReceived = 0;

                    // Tampilkan Modal Preview Struk TETAP DI WEB SENDIRI (tanpa buka tab baru)
                    if (this.receiptModalInstance) {
                        this.receiptModalInstance.show();
                    }

                    // Jika kasir memilih "Simpan & Cetak resi" atau autoPrint aktif
                    if (cetakResi || this.printerConfig.autoPrint) {
                        setTimeout(() => {
                            this.printCurrentReceipt();
                        }, 400);
                    }
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => { this.successMsg = ''; }, 5000);
                } else {
                    this.errorMsg = data.message || 'Terjadi kesalahan saat memproses.';
                    this.paymentModalInstance.hide();
                }
            } catch (error) {
                this.errorMsg = 'Koneksi gagal atau server error.';
                this.paymentModalInstance.hide();
            } finally {
                this.loading = false;
            }
        },

        openPrinterSettingsModal() {
            if (this.printerModalInstance) {
                this.printerModalInstance.show();
            }
        },

        savePrinterConfig() {
            if (window.ReceiptPrinter) {
                window.ReceiptPrinter.saveConfig(this.printerConfig);
            }
        },

        printCurrentReceipt() {
            if (!this.completedOrder) return;
            if (window.ReceiptPrinter) {
                window.ReceiptPrinter.printReceipt(this.completedOrder, this.printerConfig);
            }
        },

        closeReceiptModal() {
            if (this.receiptModalInstance) {
                this.receiptModalInstance.hide();
            }
            this.completedOrder = null;
            setTimeout(() => {
                const searchEl = document.getElementById('searchInput');
                if (searchEl) searchEl.focus();
            }, 300);
        },

        triggerToast(msg = 'Produk berhasil ditambahkan', type = 'success', duration = 2500) {
            if (msg) this.toastMessage = msg;
            this.toastType = type;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.showToast = true;
            this.toastTimeout = setTimeout(() => { this.showToast = false; }, duration);
        },

        getAnnouncedOrders() {
            try {
                const s = sessionStorage.getItem('pos_announced_orders');
                return s ? new Set(JSON.parse(s)) : new Set();
            } catch (e) {
                return new Set();
            }
        },

        markOrderAnnounced(orderId) {
            try {
                const s = this.getAnnouncedOrders();
                s.add(orderId);
                sessionStorage.setItem('pos_announced_orders', JSON.stringify(Array.from(s)));
            } catch (e) {}
        },

        playChime() {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                if (!this.audioCtx) {
                    this.audioCtx = new AudioContext();
                }
                const ctx = this.audioCtx;
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const now = ctx.currentTime;
                
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now); // D5
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.15); // A5
                gain2.gain.setValueAtTime(0.35, now + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.15);
                osc2.stop(now + 0.6);
            } catch (e) {
                console.log('Audio chime error:', e);
            }
        },

        announceOrder(order) {
            if (!order) return;
            try {
                this.playChime();

                if (!('speechSynthesis' in window)) return;

                const meja = order.meja_nomor || order.meja_id || '';
                const nama = order.nama_pelanggan || 'Pelanggan';

                let itemsText = '';
                if (order.items && order.items.length > 0) {
                    itemsText = order.items.map(item => `${item.jumlah} ${item.nama_produk}`).join(', ');
                }

                const isPaid = (order.status_pembayaran === 'dibayar' || order.status === 'diproses');
                let speechText = isPaid
                    ? `Pesanan sudah dibayar dari Meja ${meja}, atas nama ${nama}`
                    : `Pesanan belum dibayar dari Meja ${meja}, atas nama ${nama}`;
                if (itemsText) {
                    speechText += `, memesan: ${itemsText}.`;
                }

                setTimeout(() => {
                    try {
                        window.speechSynthesis.resume();
                        window.speechSynthesis.cancel();

                        const utterance = new SpeechSynthesisUtterance(speechText);
                        utterance.lang = 'id-ID';
                        utterance.rate = 0.95;
                        utterance.pitch = 1.05;

                        const voices = window.speechSynthesis.getVoices();
                        const idVoice = voices.find(v => (v.lang === 'id-ID' || v.lang.startsWith('id') || v.lang.toLowerCase().includes('indonesia')));
                        if (idVoice) {
                            utterance.voice = idVoice;
                        }

                        utterance.onerror = (event) => {
                            console.warn('Speech error event:', event.error);
                            if (event.error === 'not-allowed') {
                                this.pendingOrderToAnnounce = order;
                            }
                        };

                        window.speechSynthesis.speak(utterance);
                    } catch (err) {
                        console.log('Speech synthesis speak error:', err);
                    }
                }, 400);
            } catch (e) {
                console.log('announceOrder failed:', e);
            }
        },

        testVoiceNotification() {
            this.playChime();
            if ('speechSynthesis' in window) {
                setTimeout(() => {
                    try {
                        window.speechSynthesis.resume();
                        window.speechSynthesis.cancel();
                        const utterance = new SpeechSynthesisUtterance("Uji coba suara notifikasi POS Dynasty berhasil. Pesanan meja masuk yang sudah dibayar akan disuarakan otomatis.");
                        utterance.lang = 'id-ID';
                        utterance.rate = 0.95;
                        const voices = window.speechSynthesis.getVoices();
                        const idVoice = voices.find(v => (v.lang === 'id-ID' || v.lang.startsWith('id') || v.lang.toLowerCase().includes('indonesia')));
                        if (idVoice) utterance.voice = idVoice;
                        window.speechSynthesis.speak(utterance);
                    } catch (err) {
                        console.log('Speech error:', err);
                    }
                }, 400);
            }
            this.triggerToast('Suara notifikasi dan bel aktif!', 'success');
        },

        async fetchActiveTableOrders() {
            try {
                const res = await fetch(window.posData.pesananAktifUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.posData.csrfToken
                    }
                });
                if (res.ok) {
                    const json = await res.json();
                    const newOrders = json.data || [];
                    this.tableOrders = newOrders;

                    // Suara otomatis HANYA berbunyi ketika customer SUDAH MEMBAYAR (status_pembayaran === 'dibayar' atau status === 'diproses')
                    const announced = this.getAnnouncedOrders();
                    const unannouncedPaid = newOrders.filter(o => {
                        const isPaid = (o.status_pembayaran === 'dibayar' || o.status === 'diproses');
                        return isPaid && !announced.has(o.id);
                    });

                    if (unannouncedPaid.length > 0) {
                        const targetOrder = unannouncedPaid[0];
                        // Tandai sudah diumumkan agar tidak berulang setiap 4 detik
                        unannouncedPaid.forEach(o => this.markOrderAnnounced(o.id));

                        this.pendingOrderToAnnounce = targetOrder;
                        this.announceOrder(targetOrder);
                        this.triggerToast(`Pembayaran Masuk! Meja ${targetOrder.meja_nomor} (${targetOrder.nama_pelanggan}) Lunas QRIS/Online!`, 'success', 5000);
                    }

                    this.lastTableOrderCount = newOrders.length;
                }
            } catch (e) {
                console.log('Error fetching active table orders:', e);
            }
        },

        openTableOrdersModal() {
            this.userHasInteracted = true;
            if (this.pendingOrderToAnnounce) {
                const o = this.pendingOrderToAnnounce;
                this.pendingOrderToAnnounce = null;
                this.announceOrder(o);
            }
            this.fetchActiveTableOrders();
            this.tableOrdersModalInstance?.show();
        },

        async updateOrderStatus(orderId, newStatus) {
            try {
                const res = await fetch(`${window.posData.ubahStatusBaseUrl}/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.posData.csrfToken
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    this.triggerToast();
                    await this.fetchActiveTableOrders();
                } else {
                    alert(json.message || 'Gagal mengubah status pesanan');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi saat mengubah status pesanan.');
            }
        },

        openPayTableOrderModal(order) {
            this.payingTableOrder = order;
            this.tablePayMethod = 'tunai';
            this.tablePayCashReceived = order.total_harga;
            this.tableOrdersModalInstance?.hide();
            this.tableOrderPayModalInstance?.show();
        },

        async submitTableOrderPayment() {
            if (!this.payingTableOrder) return;
            this.isSubmittingTablePay = true;
            try {
                const res = await fetch(`${window.posData.bayarPesananBaseUrl}/${this.payingTableOrder.id}/bayar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.posData.csrfToken
                    },
                    body: JSON.stringify({
                        payment_method: this.tablePayMethod,
                        cash_received: this.tablePayCashReceived || this.payingTableOrder.total_harga
                    })
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    this.tableOrderPayModalInstance?.hide();
                    
                    this.completedOrder = {
                        pesanan_id: json.data.pesanan_id,
                        nomor_pesanan: json.data.nomor_pesanan,
                        customerName: this.payingTableOrder.nama_pelanggan || this.payingTableOrder.catatan || ('Pelanggan Meja ' + this.payingTableOrder.meja_nomor),
                        tableName: this.payingTableOrder.meja_nama || ('Meja ' + this.payingTableOrder.meja_nomor),
                        cashierName: '{{ Auth::user()->name }}',
                        items: this.payingTableOrder.items.map(item => ({
                            product: { nama: item.nama_produk },
                            quantity: item.jumlah,
                            unitPrice: item.harga,
                            catatan: item.catatan,
                            selectedOptions: (item.modifiers || []).map(m => ({ id: m.option_id, nama: m.option_nama, harga: m.harga_tambahan }))
                        })),
                        subtotal: this.payingTableOrder.total_harga,
                        discountPercent: 0,
                        discountAmount: 0,
                        ppnEnabled: false,
                        taxPB1: 0,
                        total: this.payingTableOrder.total_harga,
                        paymentMethod: this.tablePayMethod,
                        cashReceived: this.tablePayCashReceived || this.payingTableOrder.total_harga,
                        changeAmount: json.data.kembalian || 0,
                        time: new Date().toLocaleString('id-ID')
                    };

                    if (this.receiptModalInstance) {
                        this.receiptModalInstance.show();
                    }

                    if (this.printerConfig.autoPrint && window.ReceiptPrinter) {
                        window.ReceiptPrinter.printReceipt(this.completedOrder, this.printerConfig);
                    }

                    this.payingTableOrder = null;
                    await this.fetchActiveTableOrders();
                } else {
                    alert(json.message || 'Pembayaran gagal diproses');
                }
            } catch (e) {
                alert('Terjadi kesalahan saat memproses pembayaran.');
            } finally {
                this.isSubmittingTablePay = false;
            }
        },

        testPrint() {
            const testOrder = {
                pesanan_id: 0,
                nomor_pesanan: 'TEST-PRINTER',
                customerName: 'Test Pelanggan',
                tableName: 'Meja Test',
                cashierName: '{{ Auth::user()->name }}',
                items: [{
                    product: { nama: 'Test Print Struk POS' },
                    quantity: 1,
                    unitPrice: 20000,
                    selectedOptions: [{ id: 1, nama: 'Normal Sugar' }]
                }],
                total: 20000,
                paymentMethod: 'tunai',
                cashReceived: 50000,
                changeAmount: 30000,
                time: new Date().toLocaleString('id-ID')
            };
            if (window.ReceiptPrinter) {
                window.ReceiptPrinter.printReceipt(testOrder, this.printerConfig);
            }
        }
    }));
});
</script>
@endpush
@endsection
