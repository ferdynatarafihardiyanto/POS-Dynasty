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
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Manajemen Karyawan & Hak Akses</h4>
            <div class="text-muted small">Kelola data staf kafe, akun kasir, administrator, dan hak akses login</div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="border rounded px-3 py-1 text-center bg-light">
                <div class="small text-muted" style="font-size: 0.7rem;">WAKTU</div>
                <div class="fw-bold" id="currentTimeHeader">--:--:-- WIB</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=8b211e&color=fff" class="rounded-circle" width="40" height="40" alt="Avatar">
                <div>
                    <div class="fw-bold fs-6 lh-1">{{ Auth::user()->name }}</div>
                    <div class="text-danger small fw-semibold">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 flex-grow-1 overflow-auto" style="background-color: #fcfcfc;">
        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div class="fw-semibold">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div class="fw-semibold">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-1"></i> Terjadi kesalahan input:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter & Add Button -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <form action="{{ route('admin.karyawan.index') }}" method="GET" class="d-flex gap-2" style="width: 320px;">
                <div class="position-relative flex-grow-1">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" class="form-control ps-5 border shadow-sm" placeholder="Cari nama atau username..." value="{{ request('search') }}">
                </div>
                @if(request('search'))
                    <a href="{{ route('admin.karyawan.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center px-3" title="Reset filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>

            <button class="btn text-white rounded-3 px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
                <i class="bi bi-person-plus-fill"></i> Tambah Karyawan
            </button>
        </div>

        <!-- Table Data -->
        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-karyawan mb-0">
                    <thead>
                        <tr>
                            <th>Nama Karyawan</th>
                            <th>Username / Email Login</th>
                            <th>Peran / Hak Akses</th>
                            <th>Status Akun</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->role === 'admin' ? '8b211e' : '4b5563' }}&color=fff" class="rounded-circle" width="34" height="34" alt="{{ $user->name }}">
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            @if($user->id === Auth::id())
                                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">(Anda)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="font-monospace text-dark bg-light px-2 py-1 rounded" style="font-size: 0.8rem;">{{ $user->email }}</code>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Super Admin
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-badge me-1"></i> Kasir
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                                        <i class="bi bi-check-circle me-1"></i> Aktif
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-2" data-bs-toggle="modal" data-bs-target="#modalEditKaryawan{{ $user->id }}" title="Edit Karyawan">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        @if($user->id !== Auth::id())
                                            <form action="{{ route('admin.karyawan.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}? Akun ini tidak akan bisa login lagi.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Hapus Karyawan">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                    Belum ada data karyawan yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $users->total() }} karyawan
            </div>
            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div class="modal fade" id="modalTambahKaryawan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Tambah Karyawan Baru</h5>
                    <div class="text-muted small">Buat akun untuk kasir atau pengelola kafe</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.karyawan.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 pt-4 pb-2">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required value="{{ old('name') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username / Email Login <span class="text-danger">*</span></label>
                        <input type="text" name="email" class="form-control" placeholder="Contoh: budiksr atau budi@cafe.test" required value="{{ old('email') }}">
                        <div class="text-muted" style="font-size: 0.72rem; margin-top: 4px;">Dapat diisi username bebas atau alamat email yang digunakan saat login.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peran / Hak Akses <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="kasir" {{ old('role') === 'kasir' ? 'selected' : '' }}>Kasir (Hanya Akses POS & Kasir)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Akses Seluruh Sistem Backoffice)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 4 karakter" required minlength="4">
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark flex-grow-1" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4 flex-grow-1" style="background-color: #8b211e;">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Karyawan -->
@foreach($users as $user)
<div class="modal fade" id="modalEditKaryawan{{ $user->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark">Edit Data Karyawan</h5>
                    <div class="text-muted small">Ubah informasi akun dan kata sandi</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('admin.karyawan.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body px-4 pt-4 pb-2">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username / Email Login <span class="text-danger">*</span></label>
                        <input type="text" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peran / Hak Akses <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="kasir" {{ old('role', $user->role) === 'kasir' ? 'selected' : '' }}>Kasir (Hanya Akses POS & Kasir)</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator (Akses Seluruh Sistem Backoffice)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-muted fw-normal">(Kosongkan jika tidak ingin diubah)</span></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="4">
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

@push('scripts')
<script>
    function updateHeaderTime() {
        const el = document.getElementById('currentTimeHeader');
        if (el) {
            const now = new Date();
            el.innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        }
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);
</script>
@endpush
@endsection
