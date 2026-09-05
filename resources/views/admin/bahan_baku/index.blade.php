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
    .badge-stock {
        padding: 4px 10px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .stock-safe { background-color: #dcfce7; color: #16a34a; }
    .stock-low { background-color: #fee2e2; color: #dc2626; }
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <!-- Header -->
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

    <!-- Main Content -->
    <div class="p-4 flex-grow-1 overflow-auto" style="background-color: #fcfcfc;">
        
        <!-- Flash Message Alerts -->
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #dcfce7; color: #16a34a;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div class="fw-bold small">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626;">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div class="fw-bold small">{{ session('error') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626;">
                <ul class="mb-0 small fw-bold">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Daftar Bahan Baku</h5>
                <div class="text-muted small">Total {{ $bahanBakus->total() }} bahan terdaftar untuk resep & stok</div>
            </div>
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
                            <th>Harga Modal / Beli</th>
                            <th>Satuan</th>
                            <th>Min. Stok</th>
                            <th>Stok Saat Ini</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahanBakus as $bb)
                        <tr>
                            <td class="text-muted fw-bold">BB-{{ str_pad($bb->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="fw-bold text-dark">{{ $bb->nama }}</td>
                            <td class="fw-semibold">Rp {{ number_format($bb->harga_beli, 0, ',', '.') }}</td>
                            <td>{{ $bb->satuan }}</td>
                            <td class="text-muted">{{ (float)$bb->stok_minimum }} {{ $bb->satuan }}</td>
                            <td>
                                @if($bb->stok <= $bb->stok_minimum)
                                    <span class="badge-stock stock-low">
                                        <i class="bi bi-exclamation-triangle me-1"></i> {{ (float)$bb->stok }} {{ $bb->satuan }} (Menipis)
                                    </span>
                                @else
                                    <span class="badge-stock stock-safe">
                                        <i class="bi bi-check2 me-1"></i> {{ (float)$bb->stok }} {{ $bb->satuan }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-sm text-primary p-1 me-1" title="Ubah Bahan" data-bs-toggle="modal" data-bs-target="#editBahanModal{{ $bb->id }}">
                                    <i class="bi bi-pencil fs-6"></i>
                                </button>
                                <!-- Tombol Hapus -->
                                <form action="{{ route('admin.bahan-baku.destroy', $bb->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus bahan baku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger p-1" title="Hapus Bahan"><i class="bi bi-trash fs-6"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Bahan Baku -->
                        <div class="modal fade" id="editBahanModal{{ $bb->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-4 shadow">
                                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark">Ubah Bahan Baku</h5>
                                            <div class="text-muted small">Edit detail BB-{{ str_pad($bb->id, 3, '0', STR_PAD_LEFT) }} - {{ $bb->nama }}</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    
                                    <form action="{{ route('admin.bahan-baku.update', $bb->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 pt-4 pb-2">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Nama Bahan Baku <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control shadow-none" name="nama" value="{{ old('nama', $bb->nama) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Satuan <span class="text-danger">*</span></label>
                                                <select class="form-select shadow-none" name="satuan" required>
                                                    <option value="" disabled>-- Pilih Satuan --</option>
                                                    @if(isset($satuans))
                                                        @foreach($satuans as $st)
                                                            <option value="{{ $st->nama }}" {{ old('satuan', $bb->satuan) == $st->nama ? 'selected' : '' }}>
                                                                {{ $st->nama }} {{ $st->keterangan ? '('.$st->keterangan.')' : '' }}
                                                            </option>
                                                        @endforeach
                                                        @if(!$satuans->contains('nama', $bb->satuan) && $bb->satuan)
                                                            <option value="{{ $bb->satuan }}" selected>{{ $bb->satuan }} (Kustom)</option>
                                                        @endif
                                                    @else
                                                        <option value="{{ $bb->satuan }}" selected>{{ $bb->satuan }}</option>
                                                    @endif
                                                </select>
                                                <div class="form-text small text-muted mt-1">
                                                    Kelola satuan di <a href="{{ route('admin.satuan.index') }}" target="_blank" class="text-decoration-none fw-semibold" style="color: #8b211e;">Pengaturan > Satuan Unit</a>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Harga Beli / Modal (Rp) <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control shadow-none" name="harga_beli" value="{{ old('harga_beli', $bb->harga_beli) }}" required min="0" step="any">
                                            </div>
                                            <div class="row g-3 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-bold small text-muted">Stok Saat Ini <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control shadow-none" name="stok" value="{{ old('stok', $bb->stok) }}" required min="0" step="any">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-bold small text-muted">Minimum Stok <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control shadow-none" name="stok_minimum" value="{{ old('stok_minimum', $bb->stok_minimum) }}" required min="0" step="any">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                                            <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4" style="background-color: #8b211e;">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada bahan baku terdaftar. Klik tombol <strong>+ Tambah Bahan</strong> untuk menambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
                Menampilkan total {{ $bahanBakus->total() }} bahan baku
            </div>
            <div>
                {{ $bahanBakus->links('pagination::bootstrap-5') }}
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
                            <input type="text" class="form-control shadow-none" name="nama" placeholder="Contoh: Biji Kopi Arabica" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Satuan <span class="text-danger">*</span></label>
                            <select class="form-select shadow-none" name="satuan" required>
                                <option value="" selected disabled>-- Pilih Satuan --</option>
                                @if(isset($satuans))
                                    @foreach($satuans as $st)
                                        <option value="{{ $st->nama }}" {{ old('satuan') == $st->nama ? 'selected' : '' }}>
                                            {{ $st->nama }} {{ $st->keterangan ? '('.$st->keterangan.')' : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text small text-muted mt-1">
                                Ingin tambah satuan baru? Atur di <a href="{{ route('admin.satuan.index') }}" target="_blank" class="text-decoration-none fw-semibold" style="color: #8b211e;">Pengaturan > Satuan Unit</a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Harga Beli / Modal (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control shadow-none" name="harga_beli" placeholder="Rp 0" required min="0" step="any">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Stok Awal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control shadow-none" name="stok" placeholder="0" required min="0" step="any">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Minimum Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control shadow-none" name="stok_minimum" placeholder="0" required min="0" step="any">
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
        const headerEl = document.getElementById('currentTimeHeader');
        if (headerEl) {
            headerEl.innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
        }
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);
</script>
@endpush
@endsection
