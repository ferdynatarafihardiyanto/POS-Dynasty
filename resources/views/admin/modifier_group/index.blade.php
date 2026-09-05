@extends('layouts.pos')

@push('styles')
<style>
    /* Table Styling */
    .table-varian th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }
    .table-varian td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    .badge-status {
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .status-active { background-color: #dcfce7; color: #16a34a; }
    .status-inactive { background-color: #fee2e2; color: #dc2626; }
    
    .btn-action {
        background: none;
        border: none;
        padding: 5px 8px;
        font-size: 1rem;
        transition: opacity 0.2s;
    }
    .btn-action-edit { color: #3b82f6; }
    .btn-action-delete { color: #ef4444; }
    .btn-action:hover { opacity: 0.7; }
    
    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    .form-control, .form-select {
        font-size: 0.85rem;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
    }
    .form-control:focus, .form-select:focus {
        border-color: #8b211e;
        box-shadow: 0 0 0 0.25rem rgba(139, 33, 30, 0.1);
    }
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Pengaturan</h4>
            <div class="text-muted small">Konfigurasi sistem, master data, dan meja</div>
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

    <!-- Main Content -->
    <div class="p-4 flex-grow-1 overflow-auto" style="background-color: #fcfcfc;">
        
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #dcfce7; color: #16a34a; max-width: 400px; margin-left: auto;">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div class="fw-bold small">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626; max-width: 400px; margin-left: auto;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="fw-bold small">{{ session('error') }}</div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Manajemen Varian & Topping</h5>
                        <div class="text-muted small">Kelola opsi tambahan, ukuran, atau tingkat kemanisan produk</div>
                    </div>
                    <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahGrupModal">
                        <i class="bi bi-plus-lg"></i> Tambah Grup Varian
                    </button>
                </div>

                <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-varian mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Grup</th>
                                    <th>Tipe Pilihan</th>
                                    <th>Wajib Diisi?</th>
                                    <th>Status</th>
                                    <th class="text-center">Jml Opsi</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groups as $g)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $g->nama }}</td>
                                    <td class="text-muted">{{ ucfirst($g->tipe) }} <br><span class="small" style="font-size:0.7rem;">(Min: {{ $g->min_pilihan }}, Max: {{ $g->max_pilihan }})</span></td>
                                    <td>
                                        @if($g->wajib_diisi)
                                            <span class="badge bg-danger rounded-pill px-3">Ya</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3">Tidak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($g->aktif)
                                            <span class="badge-status status-active">Aktif</span>
                                        @else
                                            <span class="badge-status status-inactive">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $g->options_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.modifier-groups.edit', $g->id) }}" class="btn-action btn-action-edit" title="Atur Opsi & Edit"><i class="bi bi-gear"></i></a>
                                        <form action="{{ route('admin.modifier-groups.destroy', $g->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus grup varian ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada grup varian terdaftar</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Menampilkan total {{ $groups->total() }} data
                    </div>
                    <div>
                        {{ $groups->links('pagination::bootstrap-5') }}
                    </div>
                </div>
    </div>

    <!-- Modal Tambah Grup Varian -->
    <div class="modal fade" id="tambahGrupModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Grup Varian</h5>
                        <div class="text-muted small">Buat kelompok varian baru (misal: Ukuran Gelas, Tingkat Kemanisan)</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.modifier-groups.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label">Nama Grup <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Pilihan Topping, Sugar Level" required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipe Pilihan <span class="text-danger">*</span></label>
                                <select name="tipe" class="form-select" required>
                                    <option value="single">Single (Pilih 1)</option>
                                    <option value="multiple">Multiple (Bisa banyak)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Pilihan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="min_pilihan" value="0" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Pilihan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="max_pilihan" value="1" min="1" required>
                            </div>
                        </div>

                        <div class="d-flex gap-4 mb-4">
                            <div class="d-flex align-items-center gap-3 border rounded-3 p-3 flex-grow-1">
                                <div>
                                    <div class="fw-bold small mb-1">Wajib Diisi?</div>
                                    <div class="text-muted" style="font-size:0.7rem;">Haruskah pelanggan memilih ini?</div>
                                </div>
                                <div class="form-check form-switch m-0 ms-auto">
                                    <input class="form-check-input" type="checkbox" role="switch" name="wajib_diisi" value="1" style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 border rounded-3 p-3 flex-grow-1">
                                <div>
                                    <div class="fw-bold small mb-1">Status Aktif</div>
                                    <div class="text-muted" style="font-size:0.7rem;">Apakah grup varian ini aktif?</div>
                                </div>
                                <div class="form-check form-switch m-0 ms-auto">
                                    <input class="form-check-input" type="checkbox" role="switch" name="aktif" value="1" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-5" style="background-color: #8b211e;">Simpan & Lanjut Atur Opsi</button>
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
