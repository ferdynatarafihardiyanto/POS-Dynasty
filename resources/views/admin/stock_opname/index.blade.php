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
        <div class="alert mb-4 rounded-3 d-flex align-items-center border-0 shadow-sm" style="background-color: #f0f9ff; color: #0369a1;">
            <i class="bi bi-info-circle-fill me-3 fs-5"></i>
            <div>
                <div class="fw-bold small">Cara Melakukan Stock Opname</div>
                <div style="font-size: 0.8rem;">Pilih barang, isi jumlah fisik sebenarnya yang ada di gudang, sistem akan menghitung selisih dan membuat penyesuaian.</div>
            </div>
        </div>

        <div class="bg-white rounded-4 border shadow-sm p-4">
            <form action="{{ route('admin.stock_opname.store') }}" method="POST">
                @csrf
                <div class="row align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Tipe Item <span class="text-danger">*</span></label>
                        <select class="form-select" name="tipe_item" id="tipeItemOpname" required onchange="toggleOpnameSelect()">
                            <option value="produk">Produk (Barang Jadi)</option>
                            <option value="bahan_baku">Bahan Baku</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="produkOpnameContainer">
                        <label class="form-label small fw-bold text-muted">Pilih Produk <span class="text-danger">*</span></label>
                        <select class="form-select" name="item_id_produk" id="produkOpnameSelect" onchange="updateStokSistem()">
                            <option value="" disabled selected>Pilih produk...</option>
                            @foreach($produks as $p)
                                <option value="{{ $p->id }}" data-stok="{{ $p->stok }}">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4 d-none" id="bahanOpnameContainer">
                        <label class="form-label small fw-bold text-muted">Pilih Bahan Baku <span class="text-danger">*</span></label>
                        <select class="form-select" name="item_id_bahan" id="bahanOpnameSelect" onchange="updateStokSistem()">
                            <option value="" disabled selected>Pilih bahan...</option>
                            @foreach($bahanBakus as $bb)
                                <option value="{{ $bb->id }}" data-stok="{{ $bb->stok }}">{{ $bb->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <input type="hidden" name="item_id" id="actualOpnameItemId">

                    <div class="col-md-2 mt-3">
                        <label class="form-label small fw-bold text-muted">Stok Sistem</label>
                        <input type="text" id="stokSistemDisplay" class="form-control bg-light" value="0" readonly>
                    </div>
                    <div class="col-md-2 mt-3">
                        <label class="form-label small fw-bold text-muted">Fisik Nyata <span class="text-danger">*</span></label>
                        <input type="number" class="form-control border-primary" name="stok_fisik" placeholder="0" required min="0">
                    </div>
                    <div class="col-md-8 mt-3">
                        <label class="form-label small fw-bold text-muted">Alasan Penyesuaian</label>
                        <div class="d-flex gap-2">
                            <input type="text" class="form-control" name="keterangan" placeholder="Contoh: Barang tumpah/rusak">
                            <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #8b211e;" onclick="syncOpnameItemId()">Sesuaikan</button>
                        </div>
                    </div>
                </div>
            </form>

            <hr class="text-muted opacity-25 mb-4">

            <h6 class="fw-bold mb-3">Riwayat Opname Terakhir</h6>
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
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
                                <td class="text-muted">{{ $op->tanggal->format('d M Y') }}</td>
                                <td class="fw-bold text-dark">
                                    {{ $dtl->produk ? $dtl->produk->nama : ($dtl->bahanBaku ? $dtl->bahanBaku->nama : '-') }}
                                </td>
                                <td>{{ $dtl->stok_sistem }}</td>
                                <td>{{ $dtl->stok_fisik }}</td>
                                <td class="{{ $dtl->selisih < 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                    {{ $dtl->selisih > 0 ? '+' : '' }}{{ $dtl->selisih }}
                                </td>
                                <td class="text-muted">{{ $dtl->keterangan ?? '-' }}</td>
                                <td>{{ $op->user ? $op->user->name : '-' }}</td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat stock opname</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
        
        const option = selectEl.options[selectEl.selectedIndex];
        if (option && option.value) {
            document.getElementById('stokSistemDisplay').value = option.getAttribute('data-stok');
        } else {
            document.getElementById('stokSistemDisplay').value = 0;
        }
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
