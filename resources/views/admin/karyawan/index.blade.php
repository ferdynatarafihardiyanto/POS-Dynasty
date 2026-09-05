@extends('layouts.pos')

@push('styles')
<style>
    .table-karyawan th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
        color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 12px 16px;
    }
    .table-karyawan td {
        vertical-align: middle; font-size: 0.85rem; padding: 12px 16px; border-bottom: 1px solid #f3f4f6;
    }
    .form-label {
        font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem;
    }
    .form-control, .form-select {
        font-size: 0.85rem; padding: 0.6rem 1rem; border-radius: 8px; border: 1px solid #d1d5db;
    }
    .form-control:focus, .form-select:focus {
        border-color: #8b211e; box-shadow: 0 0 0 0.25rem rgba(139, 33, 30, 0.1);
    }
    
    .permission-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 16px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .permission-card:hover {
        border-color: #8b211e;
        background-color: #fffaf9;
    }
    .permission-checkbox:checked + .permission-card {
        border-color: #8b211e;
        background-color: #fdf5f5;
    }
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Karyawan & Hak Akses</h4>
            <div class="text-muted small">Kelola data pegawai dan atur modul mana saja yang bisa mereka buka</div>
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
            <div class="position-relative" style="width: 300px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control ps-5 border shadow-sm" placeholder="Cari nama karyawan...">
            </div>
            <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#modalKaryawan">
                <i class="bi bi-person-plus-fill"></i> Tambah Karyawan
            </button>
        </div>

        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-karyawan mb-0">
                    <thead>
                        <tr>
                            <th>Nama Karyawan</th>
                            <th>Username (Login)</th>
                            <th>Peran / Jabatan</th>
                            <th>Status Akun</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name=Budi+Santoso&background=random" class="rounded-circle" width="32" height="32">
                                    <span class="fw-bold text-dark">Budi Santoso</span>
                                </div>
                            </td>
                            <td class="text-muted">budiksr</td>
                            <td><span class="badge bg-secondary rounded-pill px-3 fw-normal">Kasir</span></td>
                            <td><span class="badge bg-success rounded-pill px-3 fw-normal">Aktif</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#modalKaryawan"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name=Cafe+Admin&background=random" class="rounded-circle" width="32" height="32">
                                    <span class="fw-bold text-dark">Cafe Admin (Anda)</span>
                                </div>
                            </td>
                            <td class="text-muted">admin@cafe.test</td>
                            <td><span class="badge bg-danger rounded-pill px-3 fw-normal">Super Admin</span></td>
                            <td><span class="badge bg-success rounded-pill px-3 fw-normal">Aktif</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm text-primary" data-bs-toggle="modal" data-bs-target="#modalKaryawan"><i class="bi bi-pencil"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Karyawan -->
<div class="modal fade" id="modalKaryawan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Detail Karyawan & Hak Akses</h5>
                    <div class="text-muted small">Atur informasi login dan modul yang boleh diakses</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="#" method="POST">
                <div class="modal-body px-4 pt-4 pb-2">
                    
                    <!-- Info Karyawan -->
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Informasi Akun</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="Budi Santoso">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Peran (Role Name) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="Kasir Depan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username (Untuk Login) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="budiksr">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-muted fw-normal">(Kosongkan jika tidak diubah)</span></label>
                            <input type="password" class="form-control" placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Checklist Hak Akses -->
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Pengaturan Hak Akses (Permissions)</h6>
                    <div class="text-muted small mb-3">Centang modul yang boleh dibuka oleh karyawan ini.</div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="d-flex align-items-center p-3 border rounded-3 h-100" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm" style="width:1.5em; height:1.5em;" checked>
                                <div>
                                    <div class="fw-bold text-dark small">POS Kasir & Transaksi</div>
                                    <div class="text-muted" style="font-size:0.7rem;">Menerima pesanan dan pembayaran</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="d-flex align-items-center p-3 border rounded-3 h-100" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm" style="width:1.5em; height:1.5em;">
                                <div>
                                    <div class="fw-bold text-dark small">Laporan Keuangan</div>
                                    <div class="text-muted" style="font-size:0.7rem;">Melihat laba rugi dan ringkasan penjualan</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="d-flex align-items-center p-3 border rounded-3 h-100" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm" style="width:1.5em; height:1.5em;">
                                <div>
                                    <div class="fw-bold text-dark small">Gudang (Barang & Stok)</div>
                                    <div class="text-muted" style="font-size:0.7rem;">Menambah barang, bahan baku, dan stok opname</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="d-flex align-items-center p-3 border rounded-3 h-100" style="cursor: pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
                                <input type="checkbox" class="form-check-input mt-0 me-3 shadow-sm" style="width:1.5em; height:1.5em;">
                                <div>
                                    <div class="fw-bold text-dark small">Pengaturan & Master Data</div>
                                    <div class="text-muted" style="font-size:0.7rem;">Mengubah meja, profil kafe, dan karyawan lain</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn text-white rounded-3 fw-bold py-2 px-5" style="background-color: #8b211e;">Simpan Karyawan</button>
                </div>
            </form>
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
