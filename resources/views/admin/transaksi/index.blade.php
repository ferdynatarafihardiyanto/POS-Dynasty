@extends('layouts.pos')

@push('styles')
<style>
    /* Styling for Transaksi Page */
    .stat-card {
        border: 1px solid #eaeaea;
        border-radius: 12px;
        background-color: #fff;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .icon-red { background-color: #fce8e8; color: #8b211e; }
    .icon-orange { background-color: #ffedd5; color: #d97706; }
    .icon-green { background-color: #dcfce7; color: #16a34a; }
    
    .table-transaksi th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 2px solid #f3f4f6;
        padding: 12px 16px;
    }
    .table-transaksi td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    .badge-method {
        padding: 5px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .method-tunai { background-color: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .method-qris { background-color: #ffedd5; color: #ea580c; border: 1px solid #fed7aa; }
    .method-debit { background-color: #e0e7ff; color: #4f46e5; border: 1px solid #c7d2fe; }
    
    .badge-status {
        padding: 5px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .status-lunas { background-color: #dcfce7; color: #16a34a; }
    
    .btn-action {
        color: #8b211e;
        background: none;
        border: none;
        padding: 5px;
        font-size: 1.1rem;
        transition: opacity 0.2s;
    }
    .btn-action:hover {
        opacity: 0.7;
    }
</style>
@endpush

@section('content')
<script>
    window.transaksiData = {
        transaksis: @json($transaksis),
        initialMetode: '{{ $metode ?? 'all' }}',
        periode: '{{ $periode ?? 'all' }}'
    };
</script>

<div class="pos-main" x-data="transaksiSystem()">
    <!-- Header -->
    <div class="pos-web-header d-flex align-items-center justify-content-between p-3 p-md-4 bg-white border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center" id="openSidebarBtn" title="Buka Navigasi" style="width: 38px; height: 38px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">Riwayat Transaksi</h4>
                <div class="text-muted small">Lihat dan kelola riwayat penjualan</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
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

    <!-- Main Content -->
    <div class="p-4 overflow-auto flex-grow-1" style="background-color: #fcfcfc;">
        
        <!-- Minimalist Paper Report Layout (Hanya Muncul Saat Print / Cetak Kertas) -->
        <div class="print-only mb-4">
            <div class="d-flex justify-content-between align-items-end pb-2 mb-3" style="border-bottom: 2px solid #000;">
                <div>
                    <h3 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px;">KEDAI DYNASTY</h3>
                    <div class="text-muted" style="font-size: 8.5pt;">Sistem Informasi Kasir & Manajemen Toko</div>
                </div>
                <div class="text-end" style="font-size: 8.5pt;">
                    <div><strong>Waktu Cetak:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</div>
                    <div><strong>Petugas:</strong> {{ Auth::user()->name }} ({{ ucfirst(Auth::user()->role) }})</div>
                </div>
            </div>
            <div class="text-center my-3">
                <h5 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1px; text-decoration: underline;">LAPORAN RIWAYAT TRANSAKSI</h5>
                <div class="small">
                    Periode: <span x-text="currentPeriodLabel"></span> &nbsp;|&nbsp; Filter Pembayaran: <strong x-text="currentMethodLabel"></strong>
                </div>
            </div>

            <!-- Tabel Ringkasan Finansial Formal -->
            <table class="table table-bordered mb-4 mt-3">
                <thead>
                    <tr>
                        <th class="text-center">Total Transaksi</th>
                        <th class="text-center">Total Penjualan (Omset)</th>
                        <th class="text-center">Total Laba Kotor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="text-center fw-bold" style="font-size: 11pt;">
                        <td x-text="currentTotalTransaksi + ' Transaksi'"></td>
                        <td x-text="currentTotalPenjualan"></td>
                        <td x-text="currentLabaKotor"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Stats Cards (Web View Only) -->
        <div class="row g-4 mb-4 no-print">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-red">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Transaksi</div>
                        <div class="fs-4 fw-bold" style="color: #8b211e;" x-text="currentTotalTransaksi">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-orange">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Penjualan</div>
                        <div class="fs-4 fw-bold" style="color: #c2410c;" x-text="currentTotalPenjualan">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-green">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Laba Kotor</div>
                        <div class="fs-4 fw-bold" style="color: #15803d;" x-text="currentLabaKotor">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search (Web View Only) -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Dropdown Filter Periode -->
                <div class="dropdown">
                    <button class="btn btn-white border bg-white rounded-3 shadow-sm px-3 py-2 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-calendar3 text-danger"></i> 
                        <span class="fw-bold text-dark small" x-text="currentPeriodLabel">
                            @if(!isset($periode) || $periode == 'all') Semua Waktu
                            @elseif($periode == 'today') Hari Ini
                            @elseif($periode == 'week') Minggu Ini
                            @elseif($periode == 'month') Bulan Ini
                            @elseif($periode == 'year') Tahun Ini
                            @endif
                        </span>
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0 mt-2 rounded-3">
                        <li><a class="dropdown-item py-2 {{ (!isset($periode) || $periode == 'all') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['metode' => $metode ?? 'all']) }}">Semua Waktu</a></li>
                        <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'today') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'today', 'metode' => $metode ?? 'all']) }}">Hari Ini</a></li>
                        <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'week') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'week', 'metode' => $metode ?? 'all']) }}">Minggu Ini</a></li>
                        <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'month') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'month', 'metode' => $metode ?? 'all']) }}">Bulan Ini</a></li>
                        <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'year') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'year', 'metode' => $metode ?? 'all']) }}">Tahun Ini</a></li>
                    </ul>
                </div>

                <!-- Dropdown Filter Metode Pembayaran (Task 3: QRIS / Cash / Transfer) -->
                <div class="dropdown">
                    <button class="btn btn-white border bg-white rounded-3 shadow-sm px-3 py-2 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-wallet2 text-danger"></i>
                        <span class="fw-bold text-dark small" x-text="currentMethodLabel">Semua Metode</span>
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0 mt-2 rounded-3">
                        <li>
                            <button type="button" class="dropdown-item py-2" :class="selectedPaymentMethod === 'all' ? 'active bg-danger text-white' : ''" @click="setPaymentMethod('all')">
                                <i class="bi bi-collection me-2"></i> Semua Metode
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-2" :class="selectedPaymentMethod === 'cash' ? 'active bg-danger text-white' : ''" @click="setPaymentMethod('cash')">
                                <i class="bi bi-cash me-2"></i> Tunai (Cash)
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-2" :class="selectedPaymentMethod === 'qris' ? 'active bg-danger text-white' : ''" @click="setPaymentMethod('qris')">
                                <i class="bi bi-qr-code me-2"></i> QRIS
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-2" :class="selectedPaymentMethod === 'transfer' ? 'active bg-danger text-white' : ''" @click="setPaymentMethod('transfer')">
                                <i class="bi bi-credit-card-2-front me-2"></i> Debit / Transfer
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Search & Cetak Laporan Button -->
            <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end" style="max-width: 500px;">
                <div class="position-relative flex-grow-1">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" class="form-control rounded-3 ps-5 shadow-sm border py-2" placeholder="Cari no. pesanan / pemesan..." x-model="searchQuery">
                </div>
                <button type="button" class="btn text-white rounded-3 px-3 py-2 d-flex align-items-center gap-2 fw-bold shadow-sm text-nowrap" style="background-color: #8b211e;" onclick="window.print()">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-4 border shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover table-transaksi mb-0">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal/Jam</th>
                            <th>Kasir</th>
                            <th>Total Item</th>
                            <th>Total Pembayaran</th>
                            <th>Metode Bayar</th>
                            <th>Status</th>
                            <th class="no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="trx in filteredTransaksis" :key="trx.id">
                            <tr>
                                <td class="fw-bold text-danger" x-text="trx.nomor_pesanan"></td>
                                <td>
                                    <div x-text="formatDate(trx.created_at)"></div>
                                    <div x-text="formatTime(trx.created_at)"></div>
                                </td>
                                <td>
                                    <div x-text="getKasirName(trx)"></div>
                                </td>
                                <td x-text="getTotalItems(trx)"></td>
                                <td class="fw-bold" x-text="formatRupiah(trx.total_harga)"></td>
                                <td>
                                    <span class="badge-method" :class="getPaymentClass(trx)" x-text="getPaymentMethod(trx)"></span>
                                </td>
                                <td>
                                    <span class="badge-status status-lunas">Lunas</span>
                                </td>
                                <td class="no-print">
                                    <button class="btn-action" @click="openDetail(trx)" title="Detail"><i class="bi bi-eye"></i></button>
                                    <button class="btn-action" @click="openReceiptModal(trx)" title="Lihat & Cetak Struk Thermal"><i class="bi bi-printer"></i></button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredTransaksis.length === 0">
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi ditemukan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-muted small mt-3 no-print" x-text="`Menampilkan ${filteredTransaksis.length > 0 ? 1 : 0} - ${filteredTransaksis.length} dari ${filteredTransaksis.length} data`"></div>

        <!-- Lembar Tanda Tangan Formal (Hanya Saat Print) -->
        <div class="print-only mt-4" style="page-break-inside: avoid;">
            <div class="row mt-5 pt-3">
                <div class="col-6 text-center">
                    <div class="small">Mengetahui / Penanggung Jawab,</div>
                    <div style="height: 65px;"></div>
                    <div class="fw-bold">( _______________________ )</div>
                </div>
                <div class="col-6 text-center">
                    <div class="small">Malang, {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}<br>Petugas / Kasir,</div>
                    <div style="height: 50px;"></div>
                    <div class="fw-bold">( {{ Auth::user()->name }} )</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Detail Transaksi</h5>
                    <button type="button" class="btn-close rounded-circle bg-light p-2" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
                
                <div class="modal-body pt-3 pb-0">
                    <template x-if="selectedTrx">
                        <div>
                            <!-- Info Box -->
                            <div class="rounded-3 border p-3 mb-4 bg-white" style="box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted small">No. Transaksi</div>
                                        <div class="fw-bold" x-text="selectedTrx.nomor_pesanan"></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Tanggal/Jam</div>
                                        <div class="fw-bold" x-text="formatDate(selectedTrx.created_at) + ' ' + formatTime(selectedTrx.created_at)"></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Kasir</div>
                                        <div class="fw-bold" x-text="getKasirName(selectedTrx)"></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted small">Status</div>
                                        <div><span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-1">Lunas</span></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-muted small fw-bold mb-2 text-uppercase" style="letter-spacing: 0.5px;">Item Transaksi</div>
                            
                            <!-- Items List -->
                            <div class="d-flex flex-column gap-2 mb-4">
                                <template x-for="item in selectedTrx.detail_pesanan" :key="item.id">
                                    <div class="border rounded-3 p-3 bg-white">
                                        <div class="d-flex justify-content-between mb-1">
                                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                <span x-text="item.nama_produk"></span>
                                                <template x-if="item.modifiers_snapshot">
                                                    <span>
                                                        <template x-for="mod in JSON.parse(item.modifiers_snapshot)" :key="mod.option_id">
                                                            <span x-text="' + ' + mod.option_nama"></span>
                                                        </template>
                                                    </span>
                                                </template>
                                            </div>
                                            <div class="fw-bold text-dark" x-text="formatRupiah(item.subtotal)"></div>
                                        </div>
                                        <div class="text-muted small" x-text="formatRupiah(item.harga) + ' x ' + item.jumlah"></div>
                                        <template x-if="item.catatan">
                                            <div class="small mt-1 text-danger fst-italic">
                                                <i class="bi bi-chat-left-text me-1"></i>Catatan: <span x-text="item.catatan"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Summary -->
                            <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
                                <div class="text-muted small">Sub total</div>
                                <div class="fw-bold small" x-text="formatRupiah(selectedTrx.total_harga)"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <div class="text-muted small fw-bold text-uppercase">Total Pembayaran</div>
                                <div class="fw-bold text-danger fs-4 lh-1" style="color: #8b211e !important;" x-text="formatRupiah(selectedTrx.total_harga)"></div>
                            </div>
                            
                            <!-- Payment Info -->
                            <div class="rounded-3 p-3 mb-4" style="background-color: #fafafa; border: 1px solid #f0f0f0;">
                                <div class="text-muted small fw-bold mb-3 text-uppercase">Pembayaran</div>
                                <div class="d-flex justify-content-between mb-2">
                                    <div class="small text-muted" x-text="getPaymentMethod(selectedTrx)"></div>
                                    <div class="fw-bold small" x-text="formatRupiah(getCashReceived(selectedTrx))"></div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div class="small fw-bold text-success">Kembalian</div>
                                    <div class="fw-bold small text-success" x-text="formatRupiah(getChange(selectedTrx))"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 flex-column flex-sm-row gap-2">
                    <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border" data-bs-dismiss="modal" style="width: 140px;">
                        Tutup
                    </button>
                    <button type="button" class="btn text-white rounded-3 fw-bold py-2 flex-grow-1" style="background-color: #8b211e;" @click="openReceiptModal(selectedTrx)">
                        <i class="bi bi-receipt me-2"></i> Pratinjau & Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Thermal Receipt Modal (Hitam Putih Sesuai Scan Kertas Kasir & Customer) -->
    <div class="modal fade" id="thermalReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden position-relative bg-dark">
                
                <!-- Header Modal -->
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom border-secondary bg-dark text-white">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-printer text-warning"></i>
                        <span class="font-monospace fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Struk Pembayaran (Hitam Putih)</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Receipt Container Area -->
                <div class="p-3 py-4 bg-black d-flex flex-column align-items-center overflow-auto" style="max-height: 78vh;">
                    
                    <template x-if="receiptTrx">
                        <div id="thermalReceiptModalCapture" class="w-100 d-flex flex-column align-items-center bg-transparent" style="max-width: 320px;">
                            <!-- Top Paper Tear Sawtooth Edge (Gerigi Kertas Thermal) -->
                            <div class="w-100" style="margin-bottom: -1px; overflow: hidden; line-height: 0;">
                                <svg viewBox="0 0 240 10" preserveAspectRatio="none" style="width: 100%; height: 10px; fill: #ffffff;">
                                    <path d="M0,10 L5,0 L10,10 L15,0 L20,10 L25,0 L30,10 L35,0 L40,10 L45,0 L50,10 L55,0 L60,10 L65,0 L70,10 L75,0 L80,10 L85,0 L90,10 L95,0 L100,10 L105,0 L110,10 L115,0 L120,10 L125,0 L130,10 L135,0 L140,10 L145,0 L150,10 L155,0 L160,10 L165,0 L170,10 L175,0 L180,10 L185,0 L190,10 L195,0 L200,10 L205,0 L210,10 L215,0 L220,10 L225,0 L230,10 L235,0 L240,10 Z"></path>
                                </svg>
                            </div>

                            <!-- Printable Paper Sheet (Authentic Thermal 58mm Monokrom) -->
                            <div id="thermalReceiptPaperArea" class="bg-white text-dark shadow-sm w-100" style="padding: 18px 22px; box-sizing: border-box; font-family: 'Courier New', Courier, Consolas, Monaco, monospace; font-size: 11px; line-height: 1.35; color: #000000 !important;">
                                
                                <!-- Logo Toko Hitam Putih (Jika Ada) -->
                                <template x-if="storeLogo && storeProfile.cetakLogoStruk !== false">
                                    <div class="text-center pb-2">
                                        <img :src="storeLogo" alt="Logo Toko" class="mx-auto" style="max-height: 55px; max-width: 75px; object-fit: contain; filter: grayscale(100%) contrast(180%) brightness(85%); -webkit-filter: grayscale(100%) contrast(180%) brightness(85%); mix-blend-mode: multiply;">
                                    </div>
                                </template>

                                <!-- Header Toko -->
                                <div class="text-center pb-2">
                                    <div class="fw-bold text-uppercase" style="font-size: 14px; letter-spacing: 0.5px;" x-text="storeProfile.namaToko || 'Dynasty Cafe'"></div>
                                    <template x-if="storeProfile.slogan">
                                        <div class="fw-bold text-uppercase mt-1" style="font-size: 10px;" x-text="storeProfile.slogan"></div>
                                    </template>
                                    <div class="mt-1" style="font-size: 10px; line-height: 1.3;" x-text="storeProfile.alamat || 'Jl. Jambangan Kebon Agung No. 12 B, Surabaya'"></div>
                                    <template x-if="storeProfile.telepon">
                                        <div style="font-size: 9.5px;" x-text="'Telp/WA: ' + storeProfile.telepon"></div>
                                    </template>
                                </div>

                                <!-- Dashed Line -->
                                <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                                <!-- Metadata Transaksi -->
                                <div style="font-size: 11px;">
                                    <div class="d-flex justify-content-between">
                                        <span>Pembeli</span>
                                        <span class="fw-bold" x-text="receiptTrx.nama_pelanggan || (receiptTrx.meja ? 'Meja ' + (receiptTrx.meja.table_number || receiptTrx.meja.id) : 'Pelanggan')"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pembayaran</span>
                                        <span x-text="getPaymentMethod(receiptTrx)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Tanggal</span>
                                        <span x-text="formatDate(receiptTrx.created_at) + ' ' + formatTime(receiptTrx.created_at)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>No Struk</span>
                                        <span class="fw-bold" x-text="receiptTrx.nomor_pesanan"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kasir</span>
                                        <span x-text="getKasirName(receiptTrx)"></span>
                                    </div>
                                </div>

                                <!-- Dashed Line -->
                                <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                                <!-- Items Pesanan -->
                                <div class="py-1">
                                    <template x-for="item in (receiptTrx.detail_pesanan || [])" :key="item.id">
                                        <div class="mb-2">
                                            <div class="fw-bold text-dark" x-text="item.nama_produk"></div>
                                            <div class="d-flex justify-content-between">
                                                <span x-text="formatNumber(item.harga) + ' x ' + item.jumlah"></span>
                                                <span class="fw-bold" x-text="formatNumber(item.subtotal)"></span>
                                            </div>
                                            <template x-if="item.modifiers_snapshot">
                                                <div style="font-size: 9.5px; color: #333; padding-left: 6px;">
                                                    <template x-for="mod in JSON.parse(item.modifiers_snapshot)" :key="mod.option_id">
                                                        <div x-text="'+ ' + mod.option_nama"></div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="item.catatan">
                                                <div style="font-size: 9.5px; color: #444; font-style: italic; padding-left: 6px;" x-text="'* ' + item.catatan"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                <!-- Dashed Line -->
                                <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                                <!-- Total -->
                                <div style="font-size: 11px;">
                                    <div class="d-flex justify-content-between fw-bold" style="font-size: 11.5px;">
                                        <span x-text="'TOTAL ' + getTotalItems(receiptTrx) + ' QTY'"></span>
                                        <span x-text="formatNumber(receiptTrx.total_harga)"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span x-text="'Bayar (' + getPaymentMethod(receiptTrx) + ')'"></span>
                                        <span x-text="formatNumber(getCashReceived(receiptTrx))"></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Kembali</span>
                                        <span x-text="formatNumber(getChange(receiptTrx))"></span>
                                    </div>
                                </div>

                                <!-- Dashed Line -->
                                <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                                <!-- WiFi Section Dinamis -->
                                <div style="font-size: 10px; line-height: 1.35; padding-top: 2px;">
                                    <template x-if="storeProfile.wifiList && storeProfile.wifiList.length > 0">
                                        <div>
                                            <template x-for="w in storeProfile.wifiList" :key="w.ssid">
                                                <div>
                                                    <span x-text="'Wifi : ' + w.ssid"></span><br>
                                                    <span x-text="'Pass : ' + w.password"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!storeProfile.wifiList || storeProfile.wifiList.length === 0">
                                        <div>
                                            <div>Wifi : Dynasty Cafe Free (Lt. 1)</div>
                                            <div>Pass : kedaidynasty123</div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Pesan Footer -->
                                <div class="text-center pt-2.5" style="font-size: 10px; white-space: pre-line; line-height: 1.3;" x-text="storeProfile.pesanFooterStruk || 'Terima kasih atas kunjungan Anda!\nSilakan datang kembali.'"></div>

                                <!-- Footer Link / Sosmed -->
                                <div class="text-center pt-2 pb-1 fw-bold" style="font-size: 11px;" x-text="storeProfile.sosmed || '@dynastycafe.id'"></div>

                            </div>

                            <!-- Bottom Paper Tear Sawtooth Edge (Gerigi Kertas Thermal) -->
                            <div class="w-100" style="margin-top: -1px; overflow: hidden; line-height: 0; transform: rotate(180deg);">
                                <svg viewBox="0 0 240 10" preserveAspectRatio="none" style="width: 100%; height: 10px; fill: #ffffff;">
                                    <path d="M0,10 L5,0 L10,10 L15,0 L20,10 L25,0 L30,10 L35,0 L40,10 L45,0 L50,10 L55,0 L60,10 L65,0 L70,10 L75,0 L80,10 L85,0 L90,10 L95,0 L100,10 L105,0 L110,10 L115,0 L120,10 L125,0 L130,10 L135,0 L140,10 L145,0 L150,10 L155,0 L160,10 L165,0 L170,10 L175,0 L180,10 L185,0 L190,10 L195,0 L200,10 L205,0 L210,10 L215,0 L220,10 L225,0 L230,10 L235,0 L240,10 Z"></path>
                                </svg>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- Footer Modal Controls -->
                <div class="p-3 bg-dark border-top border-secondary d-flex gap-2">
                    <button type="button" class="btn btn-warning text-dark fw-bold flex-grow-1 py-2 font-monospace d-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="font-size: 0.8rem;" @click="downloadReceiptPng()">
                        <i class="bi bi-download"></i> Unduh Struk (PNG)
                    </button>
                    <button type="button" class="btn btn-light fw-bold py-2 px-3 font-monospace d-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="font-size: 0.8rem;" @click="printReceiptDirectly()">
                        <i class="bi bi-printer-fill"></i> Cetak Struk
                    </button>
                    <button type="button" class="btn btn-secondary fw-semibold px-3 py-2" data-bs-dismiss="modal" style="font-size: 0.8rem;">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('transaksiSystem', () => ({
        transaksis: window.transaksiData.transaksis,
        selectedPaymentMethod: window.transaksiData.initialMetode || 'all',
        currentPeriod: window.transaksiData.periode || 'all',
        searchQuery: '',
        currentTime: '',
        selectedTrx: null,
        detailModalInstance: null,
        
        // Receipt Modal State
        receiptTrx: null,
        receiptModalInstance: null,
        storeProfile: {
            namaToko: 'Dynasty Cafe',
            slogan: 'Authentic Coffee & Eatery',
            telepon: '0812-3456-7890',
            sosmed: '@dynastycafe.id',
            alamat: 'Jl. Jambangan Kebon Agung No. 12 B, Surabaya',
            pesanFooterStruk: "Terima kasih atas kunjungan Anda!\nSilakan datang kembali.",
            cetakLogoStruk: true,
            wifiList: [
                { ssid: 'Dynasty Cafe Free (Lt. 1)', password: 'kedaidynasty123' },
                { ssid: 'Dynasty Cafe VIP (Lt. 2)', password: 'dynastyvip88' }
            ]
        },
        storeLogo: null,
        
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            this.loadStoreProfile();
            
            setTimeout(() => {
                const modalEl = document.getElementById('detailModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    this.detailModalInstance = new bootstrap.Modal(modalEl);
                    modalEl.addEventListener('hidden.bs.modal', () => {
                        this.selectedTrx = null;
                    });
                }

                const receiptEl = document.getElementById('thermalReceiptModal');
                if (receiptEl && typeof bootstrap !== 'undefined') {
                    this.receiptModalInstance = new bootstrap.Modal(receiptEl);
                }
            }, 100);
        },
        
        loadStoreProfile() {
            try {
                const saved = localStorage.getItem('dynasty_store_profile');
                if (saved) {
                    this.storeProfile = Object.assign({}, this.storeProfile, JSON.parse(saved));
                }
                const savedLogo = localStorage.getItem('dynasty_logo_data');
                if (savedLogo) {
                    this.storeLogo = savedLogo;
                }
            } catch(e) {}

            fetch('/api/profil-toko')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        this.storeProfile = Object.assign({}, this.storeProfile, res.data);
                        if (res.data.logo_url) {
                            this.storeLogo = res.data.logo_url;
                        } else if (res.data.logo_data) {
                            this.storeLogo = res.data.logo_data;
                        }
                    }
                })
                .catch(() => {});
        },

        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        },

        setPaymentMethod(method) {
            this.selectedPaymentMethod = method;
        },
        
        get currentMethodLabel() {
            if (this.selectedPaymentMethod === 'cash') return 'Tunai (Cash)';
            if (this.selectedPaymentMethod === 'qris') return 'QRIS';
            if (this.selectedPaymentMethod === 'transfer') return 'Debit / Transfer';
            return 'Semua Metode';
        },

        get currentPeriodLabel() {
            if (this.currentPeriod === 'today') return 'Hari Ini';
            if (this.currentPeriod === 'week') return 'Minggu Ini';
            if (this.currentPeriod === 'month') return 'Bulan Ini';
            if (this.currentPeriod === 'year') return 'Tahun Ini';
            return 'Semua Waktu';
        },
        
        get filteredTransaksis() {
            let list = this.transaksis;

            // Filter Metode Pembayaran
            if (this.selectedPaymentMethod !== 'all') {
                list = list.filter(t => {
                    const method = this.getPaymentMethod(t).toLowerCase();
                    if (this.selectedPaymentMethod === 'cash') {
                        return method.includes('tunai') || method.includes('cash');
                    } else if (this.selectedPaymentMethod === 'qris') {
                        return method.includes('qris');
                    } else if (this.selectedPaymentMethod === 'transfer') {
                        return method.includes('debit') || method.includes('transfer') || method.includes('va');
                    }
                    return true;
                });
            }

            // Filter Search Query
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                list = list.filter(t => {
                    const noTrx = (t.nomor_pesanan || '').toLowerCase();
                    const catatan = (t.catatan || '').toLowerCase();
                    const kasir = this.getKasirName(t).toLowerCase();
                    return noTrx.includes(q) || catatan.includes(q) || kasir.includes(q);
                });
            }

            return list;
        },

        get currentTotalTransaksi() {
            return this.filteredTransaksis.length;
        },

        get currentTotalPenjualan() {
            const sum = this.filteredTransaksis.reduce((acc, t) => acc + (parseFloat(t.total_harga) || 0), 0);
            return 'Rp ' + Math.round(sum).toLocaleString('id-ID');
        },

        get currentLabaKotor() {
            let totalLaba = 0;
            this.filteredTransaksis.forEach(t => {
                if (t.detail_pesanan) {
                    t.detail_pesanan.forEach(d => {
                        const hpp = parseFloat(d.hpp) || 0;
                        const subtotal = parseFloat(d.subtotal) || 0;
                        const qty = parseFloat(d.jumlah) || 1;
                        totalLaba += (subtotal - (hpp * qty));
                    });
                }
            });
            return 'Rp ' + Math.round(totalLaba).toLocaleString('id-ID');
        },
        
        formatRupiah(number) {
            return 'Rp ' + Math.round(number).toLocaleString('id-ID');
        },

        formatNumber(num) {
            return (Math.round(num) || 0).toLocaleString('en-US');
        },
        
        formatDate(dateString) {
            const d = new Date(dateString);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        
        formatTime(dateString) {
            const d = new Date(dateString);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
        },
        
        getKasirName(trx) {
            if (!trx) return 'Kasir POS';
            if (trx.nama_pelanggan && !trx.nama_pelanggan.startsWith('Pelanggan Meja')) {
                return trx.nama_pelanggan;
            }
            let catatan = trx.catatan || '';
            if (catatan.includes('Pemesan:')) {
                let parts = catatan.split('Pemesan:')[1].split('|')[0].trim();
                if (parts) return parts;
            }
            if (trx.meja) {
                return 'Meja ' + (trx.meja.table_number || trx.meja.id);
            }
            return 'Kasir POS';
        },
        
        getTotalItems(trx) {
            if (!trx || !trx.detail_pesanan) return 0;
            return trx.detail_pesanan.reduce((sum, item) => sum + item.jumlah, 0);
        },
        
        getPaymentMethod(trx) {
            if (!trx) return 'Tunai';
            if (trx.pembayaran && trx.pembayaran.metode_pembayaran) {
                const m = trx.pembayaran.metode_pembayaran.toLowerCase();
                if (m === 'qris') return 'QRIS';
                if (m === 'transfer') return 'Debit / VA';
                if (m === 'cash') return 'Tunai';
                return trx.pembayaran.metode_pembayaran.toUpperCase();
            }
            if (!trx.catatan) return 'Tunai';
            if (trx.catatan.toUpperCase().includes('QRIS')) return 'QRIS';
            if (trx.catatan.toUpperCase().includes('DEBIT') || trx.catatan.toUpperCase().includes('TRANSFER')) return 'Debit';
            return 'Tunai';
        },
        
        getPaymentClass(trx) {
            const method = this.getPaymentMethod(trx).toLowerCase();
            if (method.includes('qris')) return 'method-qris';
            if (method.includes('debit') || method.includes('transfer') || method.includes('va')) return 'method-debit';
            return 'method-tunai';
        },
        
        openDetail(trx) {
            this.selectedTrx = trx;
            this.detailModalInstance.show();
        },

        openReceiptModal(trx) {
            this.receiptTrx = trx;
            if (this.detailModalInstance) {
                this.detailModalInstance.hide();
            }
            if (!this.receiptModalInstance) {
                const el = document.getElementById('thermalReceiptModal');
                if (el && typeof bootstrap !== 'undefined') {
                    this.receiptModalInstance = new bootstrap.Modal(el);
                }
            }
            if (this.receiptModalInstance) {
                this.receiptModalInstance.show();
            }
        },

        downloadReceiptPng() {
            const target = document.getElementById('thermalReceiptModalCapture');
            if (!target) return;
            
            const trxNum = (this.receiptTrx?.nomor_pesanan || 'struk').replace(/[^a-zA-Z0-9-_]/g, '');
            const fileName = `Struk-${trxNum}.png`;

            if (typeof html2canvas !== 'undefined') {
                html2canvas(target, {
                    scale: 2.5,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false
                }).then(canvas => {
                    const dataUrl = canvas.toDataURL('image/png');
                    const a = document.createElement('a');
                    a.href = dataUrl;
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }).catch(err => {
                    console.error('Error capture:', err);
                    alert('Gagal mendownload struk PNG: ' + err.message);
                });
            } else {
                alert('Library html2canvas belum siap.');
            }
        },

        printReceiptDirectly() {
            const printArea = document.getElementById('thermalReceiptPaperArea');
            if (!printArea) return;
            
            let iframe = document.getElementById('adminThermalPrintIframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'adminThermalPrintIframe';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                document.body.appendChild(iframe);
            }
            
            const content = `<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Struk Thermal - ${this.receiptTrx?.nomor_pesanan || 'Dynasty'}</title>
                <style>
                    body {
                        font-family: 'Courier New', Courier, Consolas, Monaco, monospace;
                        width: 58mm;
                        margin: 0 auto;
                        padding: 2mm 3mm;
                        font-size: 11px;
                        line-height: 1.25;
                        color: #000;
                        background: #fff;
                    }
                    img {
                        filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                        -webkit-filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                        mix-blend-mode: multiply;
                    }
                    @media print {
                        body { width: 58mm; margin: 0; padding: 2mm; }
                        @page { size: 58mm auto; margin: 0; }
                    }
                </style>
            </head>
            <body>
                ${printArea.innerHTML}
            </body>
            </html>`;

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(content);
            doc.close();
            setTimeout(() => {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch(e) {}
            }, 300);
        },
        
        getCashReceived(trx) {
            if (!trx) return 0;
            if (this.getPaymentMethod(trx) !== 'Tunai') {
                return trx.total_harga; 
            }
            
            if (trx.catatan) {
                const match = trx.catatan.match(/- Rp (\d+)\)/);
                if (match && match[1]) {
                    return parseInt(match[1]);
                }
            }
            
            return trx.total_harga;
        },
        
        getChange(trx) {
            if (!trx) return 0;
            return this.getCashReceived(trx) - trx.total_harga;
        }
    }));
});
</script>
@endpush
@endsection
