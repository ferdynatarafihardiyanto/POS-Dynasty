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
        transaksis: @json($transaksis)
    };
</script>

<div class="pos-main" x-data="transaksiSystem()">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-3 p-md-4 bg-white border-bottom flex-wrap gap-2">
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
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon icon-red">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Transaksi</div>
                        <div class="fs-4 fw-bold" style="color: #8b211e;">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
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
                        <div class="fs-4 fw-bold" style="color: #c2410c;">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
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
                        <div class="fs-4 fw-bold" style="color: #15803d;">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="d-flex justify-content-between mb-4">
            <div class="dropdown">
                <button class="btn btn-white border bg-white rounded-3 shadow-sm px-4 d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar3 text-danger"></i> 
                    <span class="fw-bold text-dark">
                        @if(!isset($periode) || $periode == 'all') Semua Waktu
                        @elseif($periode == 'today') Hari Ini
                        @elseif($periode == 'week') Minggu Ini
                        @elseif($periode == 'month') Bulan Ini
                        @elseif($periode == 'year') Tahun Ini
                        @endif
                    </span>
                </button>
                <ul class="dropdown-menu shadow-sm border-0 mt-2 rounded-3">
                    <li><a class="dropdown-item py-2 {{ (!isset($periode) || $periode == 'all') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index') }}">Semua Waktu</a></li>
                    <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'today') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'today']) }}">Hari Ini</a></li>
                    <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'week') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'week']) }}">Minggu Ini</a></li>
                    <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'month') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'month']) }}">Bulan Ini</a></li>
                    <li><a class="dropdown-item py-2 {{ (isset($periode) && $periode == 'year') ? 'active bg-danger text-white' : '' }}" href="{{ route('admin.transaksi.index', ['periode' => 'year']) }}">Tahun Ini</a></li>
                </ul>
            </div>
            <div class="position-relative w-50">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control rounded-3 ps-5 shadow-sm border" placeholder="Cari nama atau kode barang..." x-model="searchQuery">
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
                            <th>Aksi</th>
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
                                <td>
                                    <button class="btn-action" @click="openDetail(trx)"><i class="bi bi-eye"></i></button>
                                    <button class="btn-action" @click="window.open('/admin/transaksi/' + trx.id + '/print', '_blank')"><i class="bi bi-printer"></i></button>
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
        
        <div class="text-muted small mt-3" x-text="`Menampilkan ${filteredTransaksis.length > 0 ? 1 : 0} - ${filteredTransaksis.length} dari ${filteredTransaksis.length} data`"></div>
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
                    <button type="button" class="btn text-white rounded-3 fw-bold py-2 flex-grow-1" style="background-color: #8b211e;" @click="window.open('/admin/transaksi/' + selectedTrx.id + '/print', '_blank')">
                        <i class="bi bi-printer me-2"></i> Cetak ulang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('transaksiSystem', () => ({
        transaksis: window.transaksiData.transaksis,
        searchQuery: '',
        currentTime: '',
        selectedTrx: null,
        detailModalInstance: null,
        
        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            
            setTimeout(() => {
                const modalEl = document.getElementById('detailModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    this.detailModalInstance = new bootstrap.Modal(modalEl);
                    modalEl.addEventListener('hidden.bs.modal', () => {
                        this.selectedTrx = null;
                    });
                }
            }, 100);
        },
        
        updateTime() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        },
        
        get filteredTransaksis() {
            if (!this.searchQuery) return this.transaksis;
            const q = this.searchQuery.toLowerCase();
            return this.transaksis.filter(t => {
                return t.nomor_pesanan.toLowerCase().includes(q) || 
                       (t.catatan && t.catatan.toLowerCase().includes(q));
            });
        },
        
        formatRupiah(number) {
            return 'Rp ' + Math.round(number).toLocaleString('id-ID');
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
            if (!trx.detail_pesanan) return 0;
            return trx.detail_pesanan.reduce((sum, item) => sum + item.jumlah, 0);
        },
        
        getPaymentMethod(trx) {
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
        
        // Perhitungan Breakdown dari Total (Mock)
        getCashReceived(trx) {
            if (this.getPaymentMethod(trx) !== 'Tunai') {
                return trx.total_harga; 
            }
            
            // Extract from catatan if we saved it like: "Nama (Via TUNAI - Rp 50000)"
            if (trx.catatan) {
                const match = trx.catatan.match(/- Rp (\d+)\)/);
                if (match && match[1]) {
                    return parseInt(match[1]);
                }
            }
            
            // If not found in catatan (old transactions), assume Uang Pas
            return trx.total_harga;
        },
        
        getChange(trx) {
            return this.getCashReceived(trx) - trx.total_harga;
        }
    }));
});
</script>
@endpush
@endsection
