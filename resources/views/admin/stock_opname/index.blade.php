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
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Stock Opname</h4>
            <div class="text-muted small">Penyesuaian stok fisik dan sistem (Audit Gudang)</div>
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
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
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
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terjadi kesalahan input:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="alert mb-4 rounded-3 d-flex align-items-center border-0 shadow-sm" style="background-color: #f0f9ff; color: #0369a1;">
            <i class="bi bi-info-circle-fill me-3 fs-5"></i>
            <div>
                <div class="fw-bold small">Cara Melakukan Stock Opname</div>
                <div style="font-size: 0.8rem;">Pilih barang/bahan baku, isi jumlah fisik sebenarnya yang ada di gudang, sistem akan otomatis menghitung selisih dan menyesuaikan stok.</div>
            </div>
        </div>

        <div class="bg-white rounded-4 border shadow-sm p-4 mb-4">
            <form action="{{ route('admin.stock_opname.store') }}" method="POST" id="formStockOpname" onsubmit="syncOpnameItemId()">
                @csrf
                <div class="row align-items-end mb-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Tipe Item <span class="text-danger">*</span></label>
                        <select class="form-select shadow-none" name="tipe_item" id="tipeItemOpname" required onchange="toggleOpnameSelect()">
                            <option value="bahan_baku" selected>Bahan Baku</option>
                            <option value="produk">Produk (Barang Jadi)</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="bahanOpnameContainer">
                        <label class="form-label small fw-bold text-muted">Pilih Bahan Baku <span class="text-danger">*</span></label>
                        <select class="form-select shadow-none" id="bahanOpnameSelect" onchange="updateStokSistem()">
                            <option value="" disabled selected>Pilih bahan...</option>
                            @foreach($bahanBakus as $bb)
                                <option value="{{ $bb->id }}" data-stok="{{ (float)$bb->stok }}" data-satuan="{{ $bb->satuan }}">{{ $bb->nama }} (Satuan: {{ $bb->satuan }}) - Stok: {{ (float)$bb->stok }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-none" id="produkOpnameContainer">
                        <label class="form-label small fw-bold text-muted">Pilih Produk <span class="text-danger">*</span></label>
                        <select class="form-select shadow-none" id="produkOpnameSelect" onchange="updateStokSistem()">
                            <option value="" disabled selected>Pilih produk...</option>
                            @foreach($produks as $p)
                                <option value="{{ $p->id }}" data-stok="{{ $p->stok }}" data-satuan="{{ $p->satuan ?? 'Pcs' }}">{{ $p->nama }} - Stok: {{ $p->stok }} {{ $p->satuan ?? 'Pcs' }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <input type="hidden" name="item_id" id="actualOpnameItemId">

                    <div class="col-md-2 mt-3">
                        <label class="form-label small fw-bold text-muted">Stok Sistem</label>
                        <div class="input-group">
                            <input type="text" id="stokSistemDisplay" class="form-control bg-light shadow-none" value="0" readonly>
                            <span class="input-group-text bg-light text-muted small opnameUnitText">Gram</span>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3">
                        <label class="form-label small fw-bold text-muted">
                            Fisik Nyata <span class="badge bg-light text-danger border ms-1 opnameUnitText">Gram</span> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" step="any" class="form-control border-primary shadow-none" name="stok_fisik" placeholder="0" required min="0">
                            <span class="input-group-text bg-light fw-bold text-muted opnameUnitText">Gram</span>
                        </div>
                    </div>
                    <div class="col-md-8 mt-3">
                        <label class="form-label small fw-bold text-muted">Alasan / Catatan Penyesuaian</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control shadow-none" name="keterangan" placeholder="Contoh: Audit mingguan / Tumpah / Kadaluarsa">
                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #8b211e;">Sesuaikan</button>
                        </div>
                    </div>
                </div>
            </form>

            <hr class="text-muted opacity-25 my-4">

            <h6 class="fw-bold mb-3 text-dark">Riwayat Opname Terakhir</h6>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Opname</th>
                            <th>Item</th>
                            <th>Sistem</th>
                            <th>Fisik</th>
                            <th>Selisih</th>
                            <th>Keterangan</th>
                            <th>Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opnames as $op)
                            @foreach($op->details as $dtl)
                            <tr>
                                <td class="text-muted">{{ $op->tanggal ? $op->tanggal->format('d M Y') : '-' }}</td>
                                <td class="fw-bold">{{ $op->nomor_opname ?? '-' }}</td>
                                <td class="fw-bold text-dark">
                                    @if($dtl->produk)
                                        <i class="bi bi-cup-hot text-muted me-1"></i> {{ $dtl->produk->nama }}
                                    @elseif($dtl->bahanBaku)
                                        <i class="bi bi-box-seam text-muted me-1"></i> {{ $dtl->bahanBaku->nama }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ (float)$dtl->stok_sistem }} 
                                    <span class="text-muted small">{{ $dtl->produk ? ($dtl->produk->satuan ?? 'Pcs') : ($dtl->bahanBaku ? $dtl->bahanBaku->satuan : '') }}</span>
                                </td>
                                <td>
                                    {{ (float)$dtl->stok_fisik }} 
                                    <span class="text-muted small">{{ $dtl->produk ? ($dtl->produk->satuan ?? 'Pcs') : ($dtl->bahanBaku ? $dtl->bahanBaku->satuan : '') }}</span>
                                </td>
                                <td class="{{ $dtl->selisih < 0 ? 'text-danger' : ($dtl->selisih > 0 ? 'text-success' : 'text-muted') }} fw-bold">
                                    {{ $dtl->selisih > 0 ? '+' : '' }}{{ (float)$dtl->selisih }}
                                    <span class="small">{{ $dtl->produk ? ($dtl->produk->satuan ?? 'Pcs') : ($dtl->bahanBaku ? $dtl->bahanBaku->satuan : '') }}</span>
                                </td>
                                <td class="text-muted">{{ $dtl->keterangan ?? '-' }}</td>
                                <td>
                                    @if($op->user)
                                        <span class="badge bg-light text-dark border">{{ $op->user->name }}</span>
                                    @else
                                        <span class="text-muted small">Admin</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat stock opname</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Menampilkan total {{ $opnames->total() }} riwayat opname
                </div>
                <div>
                    {{ $opnames->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleOpnameSelect() {
        const type = document.getElementById('tipeItemOpname').value;
        const pContainer = document.getElementById('produkOpnameContainer');
        const bContainer = document.getElementById('bahanOpnameContainer');
        
        if (type === 'produk') {
            pContainer.classList.remove('d-none');
            bContainer.classList.add('d-none');
        } else {
            pContainer.classList.add('d-none');
            bContainer.classList.remove('d-none');
        }
        updateStokSistem();
    }
    
    function updateStokSistem() {
        const type = document.getElementById('tipeItemOpname').value;
        let selectEl = null;
        if (type === 'produk') {
            selectEl = document.getElementById('produkOpnameSelect');
        } else {
            selectEl = document.getElementById('bahanOpnameSelect');
        }
        
        const option = selectEl && selectEl.selectedIndex >= 0 ? selectEl.options[selectEl.selectedIndex] : null;
        let unit = type === 'produk' ? 'Pcs' : 'Gram';
        if (option && option.value) {
            document.getElementById('stokSistemDisplay').value = option.getAttribute('data-stok') || 0;
            unit = option.getAttribute('data-satuan') || unit;
        } else {
            document.getElementById('stokSistemDisplay').value = 0;
        }

        document.querySelectorAll('.opnameUnitText').forEach(el => el.innerText = unit);
        syncOpnameItemId();
    }
    
    function syncOpnameItemId() {
        const type = document.getElementById('tipeItemOpname').value;
        const actualInput = document.getElementById('actualOpnameItemId');
        if (type === 'produk') {
            actualInput.value = document.getElementById('produkOpnameSelect').value;
        } else {
            actualInput.value = document.getElementById('bahanOpnameSelect').value;
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        toggleOpnameSelect();
        updateStokSistem();
        syncOpnameItemId();
    });

    function updateHeaderTime() {
        const now = new Date();
        document.getElementById('currentTimeHeader').innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);
</script>
@endpush
@endsection
