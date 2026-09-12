@extends('layouts.pos')

@push('styles')
<style>
    .table-custom th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
        color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 12px 16px;
    }
    .table-custom td {
        vertical-align: middle; font-size: 0.85rem; padding: 12px 16px; border-bottom: 1px solid #f3f4f6;
    }
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <div class="pos-web-header d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Mutasi Stok</h4>
            <div class="text-muted small">Riwayat pergerakan stok (masuk & keluar)</div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="border rounded px-3 py-1 text-center bg-light">
                <div class="small text-muted" style="font-size: 0.7rem;">WAKTU</div>
                <div class="fw-bold" id="currentTimeHeader">--:--:-- WIB</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" class="rounded-circle" width="40" height="40">
                <div>
                    <div class="fw-bold fs-6 lh-1">{{ Auth::user()->name }}</div>
                    <div class="text-danger small">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 flex-grow-1 overflow-auto" style="background-color: #fcfcfc;">
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
                <h5 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1px; text-decoration: underline;">LAPORAN MUTASI & PERUBAHAN STOK</h5>
                <div class="small">
                    Tipe Mutasi: <strong>{{ request('tipe') ? ucfirst(request('tipe')) : 'Semua Tipe' }}</strong> &nbsp;|&nbsp; 
                    Tanggal: <strong>{{ request('tanggal') ? \Carbon\Carbon::parse(request('tanggal'))->translatedFormat('d F Y') : 'Semua Riwayat' }}</strong>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm no-print" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm no-print" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terjadi kesalahan input:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
            <form method="GET" action="{{ route('admin.stok.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
                <select class="form-select border shadow-sm" name="tipe" onchange="this.form.submit()" style="min-width: 180px;">
                    <option value="">Semua Tipe Mutasi</option>
                    <option value="masuk" {{ request('tipe') == 'masuk' ? 'selected' : '' }}>Stok Masuk (Pembelian)</option>
                    <option value="keluar" {{ request('tipe') == 'keluar' ? 'selected' : '' }}>Stok Keluar (Penjualan)</option>
                    <option value="penyesuaian" {{ request('tipe') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                </select>
                <input type="date" class="form-control border shadow-sm" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" style="width: auto;">
                <div class="input-group shadow-sm" style="width: 220px;">
                    <input type="text" class="form-control border-end-0 border" name="search" value="{{ request('search') }}" placeholder="Cari item/ref...">
                    <button class="btn btn-outline-secondary border-start-0 border bg-white" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                @if(request('tipe') || request('tanggal') || request('search'))
                    <a href="{{ route('admin.stok.index') }}" class="btn btn-light border text-muted shadow-sm" title="Reset filter">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                @endif
            </form>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn text-white rounded-3 px-4 d-flex align-items-center gap-2 fw-bold shadow-sm" style="background-color: #8b211e;" onclick="window.print()">
                    <i class="bi bi-printer"></i> Cetak Laporan
                </button>
                <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#catatStokModal">
                    <i class="bi bi-box-arrow-in-down"></i> Catat Mutasi Stok
                </button>
            </div>
        </div>

        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Referensi</th>
                            <th>Tipe</th>
                            <th>Item</th>
                            <th>Qty Mutasi</th>
                            <th>Keterangan</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayats as $r)
                        <tr>
                            <td class="text-muted">{{ $r->created_at->format('d M Y H:i') }}</td>
                            <td class="fw-bold">{{ $r->referensi ?? '-' }}</td>
                            <td>
                                @if($r->jenis == 'masuk')
                                    <span class="badge bg-success rounded-pill px-3">Masuk</span>
                                @elseif($r->jenis == 'keluar')
                                    <span class="badge bg-danger rounded-pill px-3">Keluar</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Penyesuaian</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">
                                @if($r->produk)
                                    <i class="bi bi-cup-hot text-muted me-1"></i> {{ $r->produk->nama }}
                                @elseif($r->bahanBaku)
                                    <i class="bi bi-box-seam text-muted me-1"></i> {{ $r->bahanBaku->nama }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold {{ $r->jenis == 'keluar' || $r->jumlah < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $r->jenis == 'keluar' || $r->jumlah < 0 ? '-' : '+' }}{{ (float)abs($r->jumlah) }} 
                                    {{ $r->produk ? ($r->produk->satuan ?? 'Pcs') : ($r->bahanBaku ? $r->bahanBaku->satuan : '') }}
                                </span>
                                <div class="text-muted small" style="font-size: 0.72rem;">
                                    Stok: {{ (float)$r->stok_sebelum }} &rarr; {{ (float)$r->stok_sesudah }}
                                </div>
                            </td>
                            <td class="text-muted">{{ $r->keterangan ?? '-' }}</td>
                            <td>
                                @if($r->user)
                                    <span class="badge bg-light text-dark border">{{ $r->user->name }}</span>
                                @else
                                    <span class="text-muted small">Sistem / POS</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada riwayat mutasi stok.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lembar Tanda Tangan Formal (Hanya Saat Print) -->
        <div class="print-only mt-4" style="page-break-inside: avoid;">
            <div class="row mt-5 pt-3">
                <div class="col-6 text-center">
                    <div class="small">Mengetahui / Penanggung Jawab Gudang,</div>
                    <div style="height: 65px;"></div>
                    <div class="fw-bold">( _______________________ )</div>
                </div>
                <div class="col-6 text-center">
                    <div class="small">Malang, {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}<br>Petugas Stok / Kasir,</div>
                    <div style="height: 50px;"></div>
                    <div class="fw-bold">( {{ Auth::user()->name }} )</div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3 no-print">
            <div class="text-muted small">
                Menampilkan total {{ $riwayats->total() }} riwayat mutasi
            </div>
            <div>
                {{ $riwayats->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Catat Stok -->
    <div class="modal fade" id="catatStokModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Catat Mutasi Stok</h5>
                        <div class="text-muted small">Tambahkan pencatatan stok masuk atau keluar secara manual</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.stok.store') }}" method="POST" id="formCatatStok" onsubmit="syncItemId()">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tipe Item <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none" name="tipe_item" id="tipeItemSelect" required onchange="toggleItemSelect()">
                                <option value="bahan_baku" selected>Bahan Baku</option>
                                <option value="produk">Produk (Barang Jadi)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="bahanBakuContainer">
                            <label class="form-label fw-bold small text-muted">Pilih Bahan Baku <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none" id="bahanSelect" onchange="syncItemId()">
                                @foreach($bahanBakus as $bb)
                                    <option value="{{ $bb->id }}" data-satuan="{{ $bb->satuan }}">{{ $bb->nama }} (Satuan: {{ $bb->satuan }}) - Stok: {{ (float)$bb->stok }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="produkContainer">
                            <label class="form-label fw-bold small text-muted">Pilih Produk <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none" id="produkSelect" onchange="syncItemId()">
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}" data-satuan="{{ $p->satuan ?? 'Pcs' }}">{{ $p->nama }} - Stok: {{ $p->stok }} {{ $p->satuan ?? 'Pcs' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden input to hold the actual selected item_id -->
                        <input type="hidden" name="item_id" id="actualItemId">

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Tipe Mutasi <span class="text-danger">*</span></label>
                                <select class="form-select shadow-none" name="tipe" required>
                                    <option value="masuk">Masuk (+)</option>
                                    <option value="keluar">Keluar (-)</option>
                                    <option value="penyesuaian">Penyesuaian (Opname)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">
                                    Qty <span class="badge bg-light text-danger border ms-1" id="labelSatuanDynamic">Gram</span> <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" step="any" min="0.01" class="form-control shadow-none" name="qty" id="inputQty" placeholder="0" required>
                                    <span class="input-group-text bg-light fw-bold text-muted" id="addonSatuanDynamic">Gram</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Keterangan / Referensi</label>
                            <textarea class="form-control shadow-none" name="keterangan" rows="2" placeholder="Contoh: Pembelian dari Supplier / Rusak / Kadaluarsa"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4" style="background-color: #8b211e;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleItemSelect() {
        const type = document.getElementById('tipeItemSelect').value;
        const pContainer = document.getElementById('produkContainer');
        const bContainer = document.getElementById('bahanBakuContainer');
        
        if (type === 'produk') {
            pContainer.classList.remove('d-none');
            bContainer.classList.add('d-none');
        } else {
            pContainer.classList.add('d-none');
            bContainer.classList.remove('d-none');
        }
        syncItemId();
    }
    
    function syncItemId() {
        const type = document.getElementById('tipeItemSelect').value;
        const actualInput = document.getElementById('actualItemId');
        const labelSatuan = document.getElementById('labelSatuanDynamic');
        const addonSatuan = document.getElementById('addonSatuanDynamic');
        let currentUnit = 'Pcs';

        if (type === 'produk') {
            const pEl = document.getElementById('produkSelect');
            if (pEl && pEl.selectedIndex >= 0 && pEl.options[pEl.selectedIndex]) {
                actualInput.value = pEl.value;
                currentUnit = pEl.options[pEl.selectedIndex].getAttribute('data-satuan') || 'Pcs';
            } else {
                actualInput.value = '';
            }
        } else {
            const bEl = document.getElementById('bahanSelect');
            if (bEl && bEl.selectedIndex >= 0 && bEl.options[bEl.selectedIndex]) {
                actualInput.value = bEl.value;
                currentUnit = bEl.options[bEl.selectedIndex].getAttribute('data-satuan') || 'Gram';
            } else {
                actualInput.value = '';
            }
        }

        if (labelSatuan) labelSatuan.innerText = currentUnit;
        if (addonSatuan) addonSatuan.innerText = currentUnit;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        toggleItemSelect();
        syncItemId();
    });

    function updateHeaderTime() {
        const now = new Date();
        const headerEl = document.getElementById('currentTimeHeader');
        if (headerEl) {
            headerEl.innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        }
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);
</script>
@endpush
@endsection
