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
        <div class="d-flex justify-content-between mb-4">
            <div class="d-flex gap-2">
                <select class="form-select border shadow-sm">
                    <option>Semua Tipe</option>
                    <option>Stok Masuk (Pembelian)</option>
                    <option>Stok Keluar (Penjualan)</option>
                    <option>Penyesuaian (Opname)</option>
                </select>
                <input type="date" class="form-control border shadow-sm">
            </div>
            <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#catatStokModal">
                <i class="bi bi-box-arrow-in-down"></i> Catat Mutasi Stok
            </button>
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
                            <th>Qty</th>
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
                                {{ $r->produk ? $r->produk->nama : ($r->bahanBaku ? $r->bahanBaku->nama : '-') }}
                            </td>
                            <td class="fw-bold {{ $r->jenis == 'keluar' || $r->jumlah < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $r->jenis == 'keluar' || $r->jumlah < 0 ? '-' : '+' }}{{ abs($r->jumlah) }} 
                                {{ $r->produk ? 'Pcs' : ($r->bahanBaku ? $r->bahanBaku->satuan : '') }}
                            </td>
                            <td class="text-muted">{{ $r->keterangan ?? '-' }}</td>
                            <td>{{ $r->user ? $r->user->name : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat mutasi stok</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
                
                <form action="{{ route('admin.stok.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tipe Item <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipe_item" id="tipeItemSelect" required onchange="toggleItemSelect()">
                                <option value="produk">Produk (Barang Jadi)</option>
                                <option value="bahan_baku">Bahan Baku</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="produkContainer">
                            <label class="form-label fw-bold small text-muted">Pilih Produk <span class="text-danger">*</span></label>
                            <select class="form-select" name="item_id_produk" id="produkSelect">
                                @foreach($produks as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3 d-none" id="bahanBakuContainer">
                            <label class="form-label fw-bold small text-muted">Pilih Bahan Baku <span class="text-danger">*</span></label>
                            <select class="form-select" name="item_id_bahan" id="bahanSelect">
                                @foreach($bahanBakus as $bb)
                                    <option value="{{ $bb->id }}">{{ $bb->nama }} ({{ $bb->satuan }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Hidden input to hold the actual selected item_id -->
                        <input type="hidden" name="item_id" id="actualItemId">

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Tipe Mutasi <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipe" required>
                                    <option value="masuk">Masuk (+)</option>
                                    <option value="keluar">Keluar (-)</option>
                                    <option value="penyesuaian">Penyesuaian (Opname)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Qty <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="qty" placeholder="0" required min="1">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Keterangan / Referensi</label>
                            <textarea class="form-control" name="keterangan" rows="2" placeholder="Contoh: Pembelian dari Supplier A"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4" style="background-color: #8b211e;" onclick="syncItemId()">Simpan</button>
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
    }
    
    function syncItemId() {
        const type = document.getElementById('tipeItemSelect').value;
        const actualInput = document.getElementById('actualItemId');
        if (type === 'produk') {
            actualInput.value = document.getElementById('produkSelect').value;
        } else {
            actualInput.value = document.getElementById('bahanSelect').value;
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        toggleItemSelect();
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
