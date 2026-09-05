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
            <h4 class="mb-0 fw-bold text-dark">Bahan Baku</h4>
            <div class="text-muted small">Kelola stok bahan mentah (raw materials)</div>
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
        <div class="d-flex justify-content-end mb-4">
            <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahBahanModal">
                <i class="bi bi-plus-lg"></i> Tambah Bahan
            </button>
        </div>

        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Stok Saat Ini</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanBakus as $bb)
                        <tr>
                            <td class="text-muted fw-bold">BB-{{ str_pad($bb->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-bold text-dark">{{ $bb->nama }}</td>
                            <td>-</td>
                            <td>{{ $bb->satuan }}</td>
                            <td class="fw-bold {{ $bb->stok <= $bb->stok_minimum ? 'text-danger' : '' }}">{{ $bb->stok }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm text-primary"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.bahan-baku.destroy', $bb->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus bahan baku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada bahan baku</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Bahan -->
    <div class="modal fade" id="tambahBahanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Bahan Baku</h5>
                        <div class="text-muted small">Masukkan detail bahan mentah</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.bahan-baku.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Bahan Baku <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Biji Kopi Arabica" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="satuan" placeholder="Contoh: Gram, ml, Pcs" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Harga Beli / Modal (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="harga_beli" placeholder="Rp 0" required min="0">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Stok Awal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok" placeholder="0" required min="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Minimum Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok_minimum" placeholder="0" required min="0">
                            </div>
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
    function updateHeaderTime() {
        const now = new Date();
        document.getElementById('currentTimeHeader').innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);
</script>
@endpush
@endsection
