@extends('layouts.pos')

@push('styles')
<style>
    /* Table Styling */
    .table-kategori th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }
    .table-kategori td {
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
    .btn-action-edit { color: #4b5563; }
    .btn-action-delete { color: #ef4444; }
    .btn-action:hover { opacity: 0.7; }
    
    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    .form-control {
        font-size: 0.85rem;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        border: 1px solid #d1d5db;
    }
    .form-control:focus {
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
                <h5 class="fw-bold mb-1">Kategori Produk</h5>
                        <div class="text-muted small">Kelola klasifikasi barang dan menu</div>
                    </div>
                    <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahKategoriModal">
                        <i class="bi bi-plus-lg"></i> Tambah Kategori
                    </button>
                </div>

                <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-kategori mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoris as $k)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $k->nama }}</td>
                                    <td class="text-muted">{{ Str::limit($k->deskripsi ?? '-', 50) }}</td>
                                    <td>
                                        @if($k->aktif)
                                            <span class="badge-status status-active">Aktif</span>
                                        @else
                                            <span class="badge-status status-inactive">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn-action btn-action-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editKategoriModal{{ $k->id }}"><i class="bi bi-pencil"></i></button>
                                        <form action="{{ route('admin.kategori.destroy', $k->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada kategori terdaftar</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Menampilkan total {{ $kategoris->total() }} kategori
                    </div>
                    <div>
                        {{ $kategoris->links('pagination::bootstrap-5') }}
                    </div>
                </div>
    </div>

    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="tambahKategoriModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Kategori</h5>
                        <div class="text-muted small">Buat kategori baru untuk produk Anda</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.kategori.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Makanan, Minuman Dingin" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="deskripsi" rows="3" placeholder="Penjelasan singkat..."></textarea>
                        </div>
                        <div class="mb-1 d-flex align-items-center gap-3">
                            <label class="form-label mb-0">Status Aktif</label>
                            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                                <input class="form-check-input ms-0" type="checkbox" role="switch" name="aktif" value="1" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark flex-grow-1" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4 flex-grow-1" style="background-color: #8b211e;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Edit Kategori -->
    @foreach($kategoris as $k)
    <div class="modal fade" id="editKategoriModal{{ $k->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Edit Kategori</h5>
                        <div class="text-muted small">Ubah informasi kategori</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.kategori.update', $k->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" value="{{ $k->nama }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="deskripsi" rows="3">{{ $k->deskripsi }}</textarea>
                        </div>
                        <div class="mb-1 d-flex align-items-center gap-3">
                            <label class="form-label mb-0">Status Aktif</label>
                            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                                <input class="form-check-input ms-0" type="checkbox" role="switch" name="aktif" value="1" {{ $k->aktif ? 'checked' : '' }} style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark flex-grow-1" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4 flex-grow-1" style="background-color: #8b211e;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
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
