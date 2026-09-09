@extends('layouts.pos')

@push('styles')
<style>
    /* Settings Sidebar Styles */
    .settings-link {
        display: block;
        padding: 14px 16px;
        color: #4b5563;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s;
        font-size: 0.85rem;
    }
    .settings-link:hover {
        background-color: #f9fafb;
        color: #8b211e;
    }
    .settings-link.active {
        background-color: #fdf5f5;
        color: #8b211e;
        border-left: 4px solid #8b211e;
    }

    /* Styling for Daftar Barang Page */
    .table-barang th {
        font-size: 0.75rem;
        text-transform: capitalize;
        color: #4b5563;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
        white-space: nowrap;
    }
    .table-barang td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
    }
    .badge-status-aktif {
        background-color: #dcfce7;
        color: #16a34a;
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .badge-status-nonaktif {
        background-color: #fee2e2;
        color: #dc2626;
        padding: 4px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
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
    .upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        display: flex;
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
            <!-- Hamburger Button untuk Sidebar di Mobile/Tablet -->
            <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center" id="openSidebarBtn" title="Buka Menu" style="width: 38px; height: 38px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">Daftar Barang</h4>
                <div class="text-muted small">Kelola data barang & inventori</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
            <button class="btn btn-light rounded-circle position-relative p-2 border">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>
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
            <button class="btn btn-white bg-white border shadow-sm rounded-3 px-4 d-flex align-items-center gap-2 fw-bold text-dark">
                <i class="bi bi-download"></i> Ekspor PDF
            </button>
            <button class="btn text-white rounded-3 px-4 d-flex align-items-center gap-2 fw-bold shadow-sm" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahBarangModal">
                <i class="bi bi-plus-lg"></i> Tambah Barang
            </button>
        </div>

        <!-- Warning Alert -->
        <div class="alert mb-4 rounded-3 d-flex align-items-center" style="background-color: #fffbeb; border: 1px solid #fde68a; color: #b45309;">
            <i class="bi bi-box-seam me-3 fs-5"></i>
            <span class="fw-bold small">1 barang memiliki stok di bawah minimum</span>
        </div>

        <!-- Filters & Search -->
        <form action="{{ route('admin.produk.index') }}" method="GET" class="d-flex justify-content-between mb-4 gap-3">
            <div style="width: 200px;">
                <select name="kategori" class="form-select rounded-3 shadow-sm border bg-white text-dark py-2" onchange="this.form.submit()">
                    <option value="">Semua kategori</option>
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ request('kategori') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="position-relative flex-grow-1 d-flex gap-2">
                <div class="position-relative flex-grow-1">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-3 ps-5 shadow-sm border py-2" placeholder="Cari nama atau kode barang (Cth: BRG-001)...">
                </div>
                <button type="submit" class="btn text-white rounded-3 shadow-sm px-4" style="background-color: #8b211e;">Cari</button>
                @if(request('search') || request('kategori'))
                    <a href="{{ route('admin.produk.index') }}" class="btn btn-light border rounded-3 shadow-sm px-3"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-barang mb-0">
                    <thead>
                        <tr>
                            <th>Kode barang</th>
                            <th>Nama barang</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Harga beli</th>
                            <th>Harga jual</th>
                            <th>Stok</th>
                            <th>Min. stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $p)
                        <tr>
                            <td class="text-muted fw-bold">BRG-{{ str_pad($p->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-bold text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    @if($p->gambar_url)
                                        <img src="{{ $p->gambar_url }}" alt="{{ $p->nama }}" class="rounded-2 border object-fit-cover shadow-sm" style="width: 38px; height: 38px; flex-shrink: 0;" onerror="this.onerror=null;this.src='/images/produk/americano.jpg';">
                                    @else
                                        <div class="rounded-2 border bg-light d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 38px; height: 38px; flex-shrink: 0;">
                                            <i class="bi bi-cup-hot" style="font-size: 1.1rem; color: #8b211e;"></i>
                                        </div>
                                    @endif
                                    <span>{{ $p->nama }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ $p->kategori->nama ?? '-' }}</td>
                            <td class="text-muted">Pcs</td> <!-- Mock data per design -->
                            <td class="text-muted">Rp {{ number_format($p->hpp, 0, ',', '.') }}</td>
                            <td class="fw-bold text-dark">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="fw-bold {{ $p->stok < 20 ? 'text-danger' : 'text-dark' }}">{{ $p->stok }}</td>
                            <td class="text-muted">20</td> <!-- Mock Min Stok -->
                            <td>
                                @if($p->aktif)
                                    <span class="badge-status-aktif">Aktif</span>
                                @else
                                    <span class="badge-status-nonaktif">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn-action btn-action-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editBarangModal{{ $p->id }}"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.produk.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">Belum ada barang terdaftar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination & Info -->
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $produks->firstItem() ?? 0 }} - {{ $produks->lastItem() ?? 0 }} dari {{ $produks->total() }} data
            </div>
            <div>
                {{ $produks->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Barang -->
    <div class="modal fade" id="tambahBarangModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Barang Baru</h5>
                        <div class="text-muted small">Masukkan detail barang baru</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" placeholder="Contoh: Barang A" required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode barang <span class="text-danger">*</span></label>
                                <!-- Form Name dibiarkan tapi akan di-ignore di logic backend sementara ini -->
                                <input type="text" class="form-control" name="kode_barang" placeholder="Contoh: BRG-001"> 
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Barcode <span class="text-muted fw-normal">(opsional)</span></label>
                                <input type="text" class="form-control" name="barcode" placeholder="Contoh: 00000000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi singkat barang (opsional)"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-2">Gambar produk <span class="text-muted fw-normal">(opsional)</span></label>
                            <div class="d-flex align-items-center gap-3">
                                <label class="upload-box text-center p-2" for="gambar_upload_tambah" style="cursor: pointer; position: relative; width: 84px; height: 84px; border: 1.5px dashed #ccc; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; background-color: #fafafa;">
                                    <input type="file" class="d-none" id="gambar_upload_tambah" name="gambar" accept="image/png, image/jpeg, image/gif" onchange="previewProductImage(this, 'preview_tambah', 'placeholder_tambah')">
                                    <div id="placeholder_tambah" class="d-flex flex-column align-items-center">
                                        <i class="bi bi-image text-muted fs-4"></i>
                                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">Klik untuk upload</div>
                                    </div>
                                    <img id="preview_tambah" src="" alt="Preview" class="d-none w-100 h-100 object-fit-cover" style="position: absolute; top: 0; left: 0;">
                                </label>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1.5;">
                                    Format: JPG, PNG, atau GIF<br>
                                    Ukuran maksimal: 2MB<br>
                                    Rasio: 1:1 (persegi)
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori_id" required>
                                    <option value="" selected disabled>Pilih kategori</option>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select" name="satuan">
                                    <option value="Pcs" selected>Pcs</option>
                                    @if(isset($satuans))
                                        @foreach($satuans as $st)
                                            @if($st->nama !== 'Pcs')
                                                <option value="{{ $st->nama }}">{{ $st->nama }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Harga beli / HPP (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="hpp" placeholder="Rp 0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga jual (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="harga" placeholder="Rp 0" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Stok awal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok" placeholder="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Minimum stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="min_stok" placeholder="0">
                            </div>
                        </div>

                        <div class="mb-4 border rounded p-3 bg-light">
                            <label class="form-label mb-2 d-block fw-bold text-dark"><i class="bi bi-ui-radios me-1"></i> Varian / Topping (Opsional)</label>
                            <div class="text-muted small mb-3">Pilih grup varian yang berlaku untuk barang ini</div>
                            @if(isset($modifierGroups) && $modifierGroups->count() > 0)
                                <div class="row g-2">
                                    @foreach($modifierGroups as $mg)
                                    <div class="col-md-6">
                                        <div class="form-check form-switch bg-white border p-2 rounded shadow-sm d-flex align-items-center">
                                            <input class="form-check-input ms-1 me-3 mt-0" type="checkbox" role="switch" name="modifier_groups[]" value="{{ $mg->id }}" id="mg_create_{{ $mg->id }}" style="cursor: pointer;">
                                            <label class="form-check-label mb-0 small" for="mg_create_{{ $mg->id }}" style="cursor: pointer;">
                                                <span class="fw-bold d-block">{{ $mg->nama }}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">{{ $mg->is_required ? 'Wajib' : 'Opsional' }} • {{ $mg->options_count ?? $mg->options->count() }} Opsi</span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small text-center py-2 border border-dashed rounded bg-white">Belum ada Grup Varian yang dibuat di Pengaturan</div>
                            @endif
                        </div>

                        <div class="mb-1 d-flex align-items-center gap-3">
                            <label class="form-label mb-0">Status aktif</label>
                            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                                <input class="form-check-input ms-0" type="checkbox" role="switch" name="aktif" value="1" checked style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-5" style="background-color: #8b211e;">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Edit Barang -->
    @foreach($produks as $p)
    <div class="modal fade" id="editBarangModal{{ $p->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Edit Barang</h5>
                        <div class="text-muted small">Ubah detail barang</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.produk.update', $p->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 pt-4 pb-2">
                        
                        <div class="mb-3">
                            <label class="form-label">Nama barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" value="{{ $p->nama }}" required>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kode barang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode_barang" value="BRG-{{ str_pad($p->id, 3, '0', STR_PAD_LEFT) }}"> 
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Barcode <span class="text-muted fw-normal">(opsional)</span></label>
                                <input type="text" class="form-control" name="barcode" placeholder="Contoh: 00000000">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea class="form-control" name="deskripsi" rows="3">{{ $p->deskripsi }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label mb-2">Gambar produk <span class="text-muted fw-normal">(opsional)</span></label>
                            <div class="d-flex align-items-center gap-3">
                                <label class="upload-box text-center p-2" for="gambar_upload_edit_{{ $p->id }}" style="cursor: pointer; position: relative; width: 84px; height: 84px; border: 1.5px dashed #ccc; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden; background-color: #fafafa;">
                                    <input type="file" class="d-none" id="gambar_upload_edit_{{ $p->id }}" name="gambar" accept="image/png, image/jpeg, image/gif" onchange="previewProductImage(this, 'preview_edit_{{ $p->id }}', 'placeholder_edit_{{ $p->id }}')">
                                    <div id="placeholder_edit_{{ $p->id }}" class="d-flex flex-column align-items-center {{ $p->gambar ? 'd-none' : '' }}">
                                        <i class="bi bi-image text-muted fs-4"></i>
                                        <div class="small text-muted mt-1" style="font-size: 0.7rem;">Klik untuk upload</div>
                                    </div>
                                    <img id="preview_edit_{{ $p->id }}" src="{{ $p->gambar ? asset('storage/' . $p->gambar) : '' }}" alt="Preview" class="{{ $p->gambar ? '' : 'd-none' }} w-100 h-100 object-fit-cover" style="position: absolute; top: 0; left: 0;">
                                </label>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1.5;">
                                    Format: JPG, PNG, atau GIF<br>
                                    Ukuran maksimal: 2MB<br>
                                    Rasio: 1:1 (persegi)
                                    @if($p->gambar)
                                        <div class="text-success fw-bold mt-1"><i class="bi bi-check-circle-fill"></i> Foto produk aktif</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori_id" required>
                                    <option value="" disabled>Pilih kategori</option>
                                    @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}" {{ $p->kategori_id == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                <select class="form-select" name="satuan">
                                    <option value="Pcs" selected>Pcs</option>
                                    @if(isset($satuans))
                                        @foreach($satuans as $st)
                                            @if($st->nama !== 'Pcs')
                                                <option value="{{ $st->nama }}">{{ $st->nama }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Harga beli / HPP (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="hpp" value="{{ $p->hpp }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga jual (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="harga" value="{{ $p->harga }}" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Stok awal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok" value="{{ $p->stok }}" readonly title="Stok hanya bisa diubah via penyesuaian stok / transaksi">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Minimum stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="min_stok" value="20">
                            </div>
                        </div>

                        <div class="mb-4 border rounded p-3 bg-light">
                            <label class="form-label mb-2 d-block fw-bold text-dark"><i class="bi bi-ui-radios me-1"></i> Varian / Topping (Opsional)</label>
                            <div class="text-muted small mb-3">Pilih grup varian yang berlaku untuk barang ini</div>
                            @if(isset($modifierGroups) && $modifierGroups->count() > 0)
                                <div class="row g-2">
                                    @foreach($modifierGroups as $mg)
                                    <div class="col-md-6">
                                        <div class="form-check form-switch bg-white border p-2 rounded shadow-sm d-flex align-items-center">
                                            <input class="form-check-input ms-1 me-3 mt-0" type="checkbox" role="switch" name="modifier_groups[]" value="{{ $mg->id }}" id="mg_edit_{{ $p->id }}_{{ $mg->id }}" {{ $p->modifierGroups->contains('id', $mg->id) ? 'checked' : '' }} style="cursor: pointer;">
                                            <label class="form-check-label mb-0 small" for="mg_edit_{{ $p->id }}_{{ $mg->id }}" style="cursor: pointer;">
                                                <span class="fw-bold d-block">{{ $mg->nama }}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">{{ $mg->is_required ? 'Wajib' : 'Opsional' }} • {{ $mg->options_count ?? $mg->options->count() }} Opsi</span>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted small text-center py-2 border border-dashed rounded bg-white">Belum ada Grup Varian yang dibuat di Pengaturan</div>
                            @endif
                        </div>

                        <div class="mb-1 d-flex align-items-center gap-3">
                            <label class="form-label mb-0">Status aktif</label>
                            <div class="form-check form-switch m-0 p-0 d-flex align-items-center">
                                <input class="form-check-input ms-0" type="checkbox" role="switch" name="aktif" value="1" {{ $p->aktif ? 'checked' : '' }} style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-5" style="background-color: #8b211e;">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
    // Live Clock functionality (vanilla JS for the header)
    function updateHeaderTime() {
        const now = new Date();
        document.getElementById('currentTimeHeader').innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);

    // Image preview handler for modal upload
    function previewProductImage(input, previewId, placeholderId) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file gambar maksimal adalah 2MB');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewEl = document.getElementById(previewId);
                if (previewEl) {
                    previewEl.src = e.target.result;
                    previewEl.classList.remove('d-none');
                }
                if (placeholderId) {
                    const placeholderEl = document.getElementById(placeholderId);
                    if (placeholderEl) {
                        placeholderEl.classList.add('d-none');
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush
@endsection
