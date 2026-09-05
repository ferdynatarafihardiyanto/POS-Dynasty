@extends('layouts.pos')

@push('styles')
<style>
    /* Ubah warna toggle switch Bootstrap menjadi Dark Red */
    .form-check-input:checked {
        background-color: #8b211e !important;
        border-color: #8b211e !important;
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
        csrfToken: '{{ csrf_token() }}'
    };
</script>

<div class="d-flex flex-grow-1 overflow-hidden position-relative" x-data="posSystem()">
    
    <!-- Toast Notification -->
    <div class="position-absolute top-0 end-0 p-3" style="z-index: 1050; padding-right: 380px !important;">
        <div class="toast align-items-center bg-white border-0 shadow" :class="showToast ? 'show' : 'hide'" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 50px; transition: opacity 0.3s; opacity: showToast ? 1 : 0;">
            <div class="d-flex">
                <div class="toast-body fw-bold d-flex align-items-center gap-2 px-4 py-2" style="color: #8b211e;">
                    <i class="bi bi-check-circle-fill"></i> Produk berhasil ditambahkan
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pos-main">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
            <div>
                <h4 class="mb-0 fw-bold">Kasir</h4>
                <div class="text-danger fw-bold small">TOKO SUMBER REZEKI</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Printer Status & Quick Settings Button -->
                <button type="button" class="btn btn-light rounded-pill px-3 py-1 border d-flex align-items-center gap-2 shadow-sm" @click="openPrinterSettingsModal()" title="Pengaturan Printer Struk">
                    <i class="bi bi-printer-fill" style="color: #8b211e;"></i>
                    <div class="text-start d-none d-md-block" style="line-height: 1.1;">
                        <div class="fw-bold text-dark" style="font-size: 0.75rem;" x-text="printerConfig.printerName || 'Printer Thermal'"></div>
                        <div class="text-success" style="font-size: 0.65rem;"><i class="bi bi-circle-fill" style="font-size: 6px;"></i> Modul Siap</div>
                    </div>
                </button>

                <button class="btn btn-light rounded-circle position-relative p-2 border">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </button>
                <div class="border rounded px-3 py-1 text-center bg-light">
                    <div class="small text-muted" style="font-size: 0.7rem;">WAKTU</div>
                    <div class="fw-bold" x-text="currentTime">--:--:-- WIB</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="rounded-circle" width="40" height="40" alt="Avatar">
                    <div>
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
            <div class="d-flex gap-2 overflow-auto pb-2" style="white-space: nowrap;">
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
        <div class="flex-grow-1 overflow-auto p-4 pt-0">
            <div class="row g-3">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden cursor-pointer" style="cursor:pointer;" @click="openProductModal(product)">
                            <div class="position-relative bg-light" style="height: 150px;">
                                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80" class="w-100 h-100 object-fit-cover" alt="Product">
                                <span class="position-absolute top-0 end-0 m-2 badge bg-dark opacity-75 rounded-pill px-3 py-2 text-uppercase" style="font-size: 0.7rem;" x-text="product.kategori ? product.kategori.nama : 'Lainnya'"></span>
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <h6 class="fw-bold mb-1 text-truncate" x-text="product.nama"></h6>
                                <div class="small text-muted mb-2 text-uppercase" x-text="'BRG-' + product.id.toString().padStart(3, '0')"></div>
                                
                                <div class="mt-auto d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold" style="color: #8b211e;" x-text="formatRupiah(product.harga)"></div>
                                        <div class="small text-muted" style="font-size:0.75rem;" x-text="'Stok: ' + product.stok"></div>
                                    </div>
                                    <button class="btn rounded-circle text-white shadow-sm d-flex align-items-center justify-content-center" style="background-color: #8b211e; border-color: #8b211e; width: 32px; height: 32px; padding:0;" @click.stop="openProductModal(product)">
                                        <i class="bi bi-plus fs-5"></i>
                                    </button>
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

    <!-- Right Cart Sidebar -->
    <div class="pos-cart shadow">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Ringkasan Pembayaran</h5>
            <span class="badge rounded-pill" style="background-color: #fde8e8; color: #8b211e;" x-text="cart.length + ' Item'" x-show="cart.length > 0"></span>
        </div>
        
        <!-- Pelanggan & Meja Info Bar -->
        <div class="p-3 border-bottom bg-white">
            <div class="row g-2">
                <div class="col-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 shadow-none" placeholder="Pelanggan (Opsional)" x-model="customerName" style="font-size: 0.8rem;">
                    </div>
                </div>
                <div class="col-5">
                    <select class="form-select form-select-sm bg-light shadow-none fw-semibold" x-model="selectedTableId" style="font-size: 0.8rem;">
                        <template x-for="tbl in tables" :key="tbl.id">
                            <option :value="tbl.id" x-text="tbl.name || ('Meja ' + tbl.table_number)"></option>
                        </template>
                    </select>
                </div>
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
                            </div>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm p-1 text-secondary" @click="item.showNote = !item.showNote" title="Catatan Item">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm p-1 text-danger" style="color: #ef4444 !important;" @click="removeFromCart(index)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Catatan Item Input -->
                        <div class="mt-2" x-show="item.showNote" x-transition>
                            <input type="text" class="form-control form-control-sm shadow-none" placeholder="Catatan untuk item ini..." x-model="item.catatan" style="font-size: 0.75rem;">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-4 bg-white border-top">
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

    <!-- Modifier Modal -->
    <div class="modal fade" id="modifierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" x-text="modalProduct ? modalProduct.nama : ''"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <template x-if="modalProduct && modalProduct.modifier_groups">
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
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-lg w-100 rounded-3 text-white fw-bold" style="background-color: #8b211e;" @click="addToCartFromModal()">Tambahkan ke Pesanan</button>
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
                                    <i class="bi bi-credit-card" :style="paymentMethod === 'debit' ? 'color:#8b211e;' : 'color:#6c757d;'"></i> 
                                    <span class="fw-bold ms-1" :style="paymentMethod === 'debit' ? 'color:#8b211e; font-size:0.8rem;' : 'color:#6c757d; font-size:0.8rem; font-weight: normal !important;'">Debit</span>
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
                                            <option value="iframe_direct">🖨️ Direct Browser / Silent (Tetap di Web)</option>
                                            <option value="esc_pos">⚡ ESC/POS USB Driver (Wadah Raw POS)</option>
                                            <option value="rawbt">📱 Bluetooth / Android RawBT Module</option>
                                            <option value="custom_api">🌐 Local API / QZ Tray Print Daemon</option>
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
                            <option value="iframe_direct">🖨️ Direct Browser / Silent (Tetap di Web)</option>
                            <option value="esc_pos">⚡ ESC/POS USB Driver (Wadah Raw POS)</option>
                            <option value="rawbt">📱 Bluetooth / Android RawBT Module</option>
                            <option value="custom_api">🌐 Local API / QZ Tray Print Daemon</option>
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
</div>

@push('scripts')
<script src="{{ asset('js/receipt-printer.js') }}"></script>
<script>
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
        toastTimeout: null,
        
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
                }
            }, 100);

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

        get grandTotal() {
            return this.subtotal;
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
        
        openProductModal(product) {
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
                document.querySelectorAll('#modifierModal input').forEach(input => input.checked = false);
            }, 10);
            
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
            this.addToCartInternal(product, []);
        },
        
        addToCartFromModal() {
            // Validate selections
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
            
            // Collect all selected options
            let selectedOptions = [];
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
            
            this.addToCartInternal(this.modalProduct, selectedOptions);
            this.modifierModalInstance.hide();
        },
        
        addToCartInternal(product, selectedOptions) {
            // Calculate cartId (product_id + sorted option ids)
            const optIds = selectedOptions.map(o => o.id).sort((a,b) => a-b);
            const cartId = product.id + '-' + optIds.join('-');
            
            // Calculate unit price based on options
            let addons = selectedOptions.reduce((sum, opt) => sum + opt.harga_tambahan, 0);
            let unitPrice = product.harga + addons;
            
            // Find existing
            const existingIndex = this.cart.findIndex(i => i.cartId === cartId);
            if (existingIndex >= 0) {
                this.cart[existingIndex].quantity += 1;
            } else {
                this.cart.push({
                    cartId: cartId,
                    product: product,
                    selectedOptions: selectedOptions,
                    quantity: 1,
                    unitPrice: unitPrice,
                    catatan: '',
                    showNote: false
                });
            }
            
            // Show toast
            this.showToast = true;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => { this.showToast = false; }, 2500);
        },
        
        updateQuantity(index, delta) {
            const item = this.cart[index];
            if (item.quantity + delta > 0) {
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
