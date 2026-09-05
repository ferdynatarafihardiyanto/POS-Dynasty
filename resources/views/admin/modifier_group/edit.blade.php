@extends('layouts.pos')

@push('styles')
<style>
    .table-opsi th {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }
    .table-opsi td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    
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
    .btn-action-delete {
        background: none; border: none; color: #ef4444; transition: opacity 0.2s;
    }
    .btn-action-delete:hover { opacity: 0.7; }
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
        @if($errors->any())
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626; max-width: 400px; margin-left: auto;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="fw-bold small">Terdapat kesalahan input data.</div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('admin.modifier-groups.index') }}" class="text-decoration-none text-muted small fw-bold mb-1 d-block"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Varian</a>
                <h5 class="fw-bold mb-0">Atur Opsi: {{ $modifier_group->nama }}</h5>
            </div>
        </div>

        <div class="row g-4">
                    <!-- Form Edit Group -->
                    <div class="col-md-5">
                        <div class="bg-white border rounded-4 shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-4">Detail Grup Varian</h6>
                            <form action="{{ route('admin.modifier-groups.update', $modifier_group->id) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label">Nama Grup</label>
                                    <input type="text" name="nama" value="{{ old('nama', $modifier_group->nama) }}" required class="form-control">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Min Pilihan</label>
                                        <input type="number" name="min_pilihan" value="{{ old('min_pilihan', $modifier_group->min_pilihan) }}" required class="form-control" min="0">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Max Pilihan</label>
                                        <input type="number" name="max_pilihan" value="{{ old('max_pilihan', $modifier_group->max_pilihan) }}" required class="form-control" min="1">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">Tipe Pilihan</label>
                                    <select name="tipe" class="form-select">
                                        <option value="single" {{ (old('tipe', $modifier_group->tipe) == 'single') ? 'selected' : '' }}>Single (Pilih 1)</option>
                                        <option value="multiple" {{ (old('tipe', $modifier_group->tipe) == 'multiple') ? 'selected' : '' }}>Multiple (Bisa banyak)</option>
                                    </select>
                                </div>
                                
                                <div class="d-flex flex-column gap-3 mb-4 border-top pt-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold small">Wajib Diisi?</div>
                                            <div class="text-muted" style="font-size:0.7rem;">Pelanggan harus memilih ini</div>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="wajib_diisi" value="1" {{ old('wajib_diisi', $modifier_group->wajib_diisi) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold small">Status Aktif</div>
                                            <div class="text-muted" style="font-size:0.7rem;">Grup varian ini bisa digunakan</div>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="aktif" value="1" {{ old('aktif', $modifier_group->aktif) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                        </div>
                                    </div>
                                </div>

                                <button class="btn text-white w-100 fw-bold rounded-3 py-2" style="background-color: #8b211e;">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>

                    <!-- Management Options -->
                    <div class="col-md-7">
                        <div class="bg-white border rounded-4 shadow-sm p-4 mb-4">
                            <h6 class="fw-bold mb-3">Tambah Opsi Baru</h6>
                            <form action="{{ route('admin.modifier-options.store') }}" method="POST" class="d-flex gap-2 align-items-end">
                                @csrf
                                <input type="hidden" name="modifier_group_id" value="{{ $modifier_group->id }}">
                                <div class="flex-grow-1">
                                    <label class="form-label" style="font-size:0.7rem;">Nama Opsi</label>
                                    <input type="text" name="nama" placeholder="misal: Extra Boba" required class="form-control">
                                </div>
                                <div style="width: 130px;">
                                    <label class="form-label" style="font-size:0.7rem;">Harga (+)</label>
                                    <input type="number" name="harga_tambahan" placeholder="0" required class="form-control">
                                </div>
                                <div>
                                    <button class="btn text-white fw-bold rounded-3" style="background-color: #16a34a; padding: 0.6rem 1rem;"><i class="bi bi-plus"></i> Tambah</button>
                                </div>
                            </form>
                        </div>

                        <div class="bg-white border rounded-4 shadow-sm overflow-hidden">
                            <table class="table table-hover table-opsi mb-0">
                                <thead style="background-color: #f9fafb;">
                                    <tr>
                                        <th>Nama Opsi</th>
                                        <th>Harga (+)</th>
                                        <th style="width: 100px;">Status</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modifier_group->options as $opt)
                                    <tr>
                                        <form action="{{ route('admin.modifier-options.update', $opt->id) }}" method="POST" id="form-update-{{ $opt->id }}">
                                            @csrf @method('PUT')
                                            <td><input type="text" name="nama" value="{{ $opt->nama }}" required class="form-control form-control-sm px-2"></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                                    <input type="number" name="harga_tambahan" value="{{ $opt->harga_tambahan }}" required class="form-control form-control-sm border-start-0 ps-0">
                                                </div>
                                            </td>
                                            <td>
                                                <select name="aktif" class="form-select form-select-sm" onchange="document.getElementById('form-update-{{ $opt->id }}').submit()">
                                                    <option value="1" {{ $opt->aktif ? 'selected' : '' }}>Aktif</option>
                                                    <option value="0" {{ !$opt->aktif ? 'selected' : '' }}>Nonaktif</option>
                                                </select>
                                            </td>
                                        </form>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-light border text-primary me-1" onclick="document.getElementById('form-update-{{ $opt->id }}').submit()" title="Simpan Opsi"><i class="bi bi-check-lg"></i></button>
                                            <form action="{{ route('admin.modifier-options.destroy', $opt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus opsi ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if($modifier_group->options->count() == 0)
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">Belum ada opsi di grup ini. Silakan tambah opsi di atas.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
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
