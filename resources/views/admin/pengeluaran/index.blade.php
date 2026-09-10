@extends('layouts.pos')

@push('styles')
<style>
    .total-card {
        border: 1px solid #8b211e;
        border-radius: 12px;
        background-color: #fdf5f5;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .table-pengeluaran th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }
    .table-pengeluaran td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .badge-kategori {
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        color: white;
    }
    
    .kat-listrik { background-color: #b45309; }
    .kat-gaji { background-color: #16a34a; }
    .kat-perlengkapan { background-color: #7e22ce; }
    .kat-sewa { background-color: #2563eb; }
    
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
    
    .upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100px;
        width: 100px;
        background-color: #f9fafb;
        cursor: pointer;
        transition: all 0.2s;
    }
    .upload-box:hover {
        border-color: #8b211e;
        background-color: #fffaf9;
    }
</style>
@endpush

@section('content')
<div class="pos-main">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-3 p-md-4 bg-white border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center" id="openSidebarBtn" title="Buka Navigasi" style="width: 38px; height: 38px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">Pengeluaran Toko</h4>
                <div class="text-muted small">Catat dan kelola pengeluaran operasional</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="border rounded px-3 py-1 text-center bg-light">
                <div class="small text-muted" style="font-size: 0.7rem;">WAKTU</div>
                <div class="fw-bold" id="currentTimeHeader">--:--:-- WIB</div>
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
        
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" role="alert" style="background-color: #dcfce7; color: #16a34a; max-width: 400px; margin-left: auto;">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div class="fw-bold small">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" role="alert" style="max-width: 400px; margin-left: auto;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="fw-bold small">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <button class="btn btn-white bg-white border shadow-sm rounded-3 px-4 d-flex align-items-center gap-2 fw-bold text-dark" onclick="window.print()">
                <i class="bi bi-printer"></i> Cetak Pengeluaran
            </button>
            <button class="btn text-white rounded-3 px-4 d-flex align-items-center gap-2 fw-bold shadow-sm" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahPengeluaranModal">
                <i class="bi bi-plus-lg"></i> Tambah Pengeluaran
            </button>
        </div>

        <!-- Total Card -->
        <div class="total-card shadow-sm">
            <div>
                <div class="text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; color: #8b211e !important;">Total Pengeluaran</div>
                <div class="fw-bold" style="font-size: 2.2rem; color: #8b211e;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="badge rounded-3 py-2 px-3 fw-bold" style="background-color: #fce8e8; color: #8b211e; border: 1px solid #fca5a5; font-size: 0.85rem;">
                    {{ count($pengeluarans) }} Pengeluaran tercatat
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="d-flex justify-content-between mb-4 gap-3">
            <div class="d-flex gap-2">
                <select class="form-select rounded-3 shadow-sm border bg-white text-dark py-2" style="width: 180px;">
                    <option value="">Semua kategori</option>
                    <option value="Listrik">Listrik</option>
                    <option value="Gaji">Gaji</option>
                    <option value="Perlengkapan">Perlengkapan</option>
                    <option value="Sewa">Sewa</option>
                </select>
                <button class="btn btn-white border bg-white rounded-3 shadow-sm px-4 d-flex align-items-center gap-2">
                    <i class="bi bi-calendar3"></i> <span class="fw-bold text-dark small">Filter periode</span>
                </button>
            </div>
            
            <div class="position-relative" style="width: 350px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control rounded-3 ps-5 shadow-sm border py-2" placeholder="Cari deskripsi pengeluaran...">
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-pengeluaran mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori Pengeluaran</th>
                            <th>Deskripsi</th>
                            <th>Nominal</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengeluarans as $p)
                        <tr>
                            <td class="text-muted">{{ date('d/m/Y H:i', strtotime($p->tanggal)) }}</td>
                            <td>
                                @php
                                    $catClass = 'kat-listrik';
                                    if(strtolower($p->kategori) == 'gaji') $catClass = 'kat-gaji';
                                    if(strtolower($p->kategori) == 'perlengkapan') $catClass = 'kat-perlengkapan';
                                    if(strtolower($p->kategori) == 'sewa') $catClass = 'kat-sewa';
                                @endphp
                                <span class="badge-kategori {{ $catClass }}">{{ $p->kategori }}</span>
                            </td>
                            <td class="text-dark">{{ $p->deskripsi }}</td>
                            <td class="fw-bold" style="color: #8b211e;">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                            <td class="text-muted">{{ $p->pengguna }}</td>
                            <td>
                                <button class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-muted small mt-3">
            Menampilkan 1 - {{ count($pengeluarans) }} dari {{ count($pengeluarans) }} data
        </div>
    </div>

    <!-- Modal Tambah Pengeluaran -->
    <div class="modal fade" id="tambahPengeluaranModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Pengeluaran Baru</h5>
                        <div class="text-muted small">Catat pengeluaran operasional toko</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.pengeluaran.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="datetime-local" class="form-control" name="tanggal" required value="{{ date('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="kategori" required>
                                <option value="" selected disabled>Pilih kategori</option>
                                <option value="Listrik">Listrik</option>
                                <option value="Gaji">Gaji</option>
                                <option value="Perlengkapan">Perlengkapan</option>
                                <option value="Sewa">Sewa</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea class="form-control" name="deskripsi" rows="2" placeholder="Deskripsi singkat pengeluaran (opsional)"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nominal pengeluaran (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="nominal" placeholder="Rp 0" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-2">Lampiran <span class="text-muted fw-normal">(opsional)</span></label>
                            <div class="d-flex align-items-center gap-3">
                                <label class="upload-box text-center p-2">
                                    <input type="file" class="d-none" name="lampiran">
                                    <div>
                                        <i class="bi bi-cloud-arrow-up text-muted fs-4"></i>
                                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">Klik untuk upload</div>
                                    </div>
                                </label>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1.5;">
                                    Format: JPG, PNG, atau PDF<br>
                                    Ukuran maksimal: 2MB
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark flex-grow-1" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4 flex-grow-1" style="background-color: #8b211e;">
                            Simpan
                        </button>
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
