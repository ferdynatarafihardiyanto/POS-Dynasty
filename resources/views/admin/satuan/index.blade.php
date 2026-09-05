@extends('layouts.pos')

@push('styles')
<style>
    .table-satuan th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
        color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 12px 16px;
    }
    .table-satuan td {
        vertical-align: middle; font-size: 0.85rem; padding: 12px 16px; border-bottom: 1px solid #f3f4f6;
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
        background: none; border: none; padding: 5px 8px; font-size: 1rem; transition: opacity 0.2s;
    }
    .btn-action-edit { color: #4b5563; }
    .btn-action-delete { color: #ef4444; }
    .btn-action:hover { opacity: 0.7; }
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Pengaturan</h4>
            <div class="text-muted small">Kelola master satuan unit barang & bahan baku</div>
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
        
        <!-- Alerts -->
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
                <h5 class="fw-bold mb-1">Master Satuan Unit</h5>
                <div class="text-muted small">Satuan ini otomatis muncul di dropdown Bahan Baku & Produk</div>
            </div>
            <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahSatuanModal">
                <i class="bi bi-plus-lg"></i> Tambah Satuan
            </button>
        </div>

        <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
            <div class="table-responsive">
                <table class="table table-hover table-satuan mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">No</th>
                            <th>Nama Satuan</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($satuans as $index => $st)
                        <tr>
                            <td class="text-muted fw-bold">{{ $satuans->firstItem() + $index }}</td>
                            <td class="fw-bold text-dark fs-6">{{ $st->nama }}</td>
                            <td class="text-muted">{{ $st->keterangan ?? '-' }}</td>
                            <td>
                                @if($st->aktif)
                                    <span class="badge-status status-active"><i class="bi bi-check-circle me-1"></i> Aktif</span>
                                @else
                                    <span class="badge-status status-inactive"><i class="bi bi-x-circle me-1"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn-action btn-action-edit" title="Edit Satuan" data-bs-toggle="modal" data-bs-target="#editSatuanModal{{ $st->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.satuan.destroy', $st->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus satuan {{ $st->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Satuan -->
                        <div class="modal fade" id="editSatuanModal{{ $st->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-4 shadow">
                                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark">Ubah Satuan Unit</h5>
                                            <div class="text-muted small">Edit informasi satuan: {{ $st->nama }}</div>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('admin.satuan.update', $st->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 pt-4 pb-2">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Nama Satuan <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control shadow-none" name="nama" value="{{ old('nama', $st->nama) }}" required placeholder="Contoh: Gram, ml, Kg, Cup">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Keterangan</label>
                                                <input type="text" class="form-control shadow-none" name="keterangan" value="{{ old('keterangan', $st->keterangan) }}" placeholder="Contoh: Satuan berat bubuk / kopi">
                                            </div>
                                            <div class="mb-3 form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="aktifSwitch{{ $st->id }}" name="aktif" value="1" {{ $st->aktif ? 'checked' : '' }}>
                                                <label class="form-check-label small fw-semibold" for="aktifSwitch{{ $st->id }}">Status Aktif (Tampil di Dropdown)</label>
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-tag fs-1 d-block mb-2 opacity-50"></i>
                                Belum ada satuan unit terdaftar. Klik tombol <strong>+ Tambah Satuan</strong> untuk menambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted small">
                Menampilkan total {{ $satuans->total() }} satuan unit
            </div>
            <div>
                {{ $satuans->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- Modal Tambah Satuan -->
    <div class="modal fade" id="tambahSatuanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Satuan Unit</h5>
                        <div class="text-muted small">Tambahkan satuan baru untuk bahan baku atau produk</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.satuan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nama Satuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control shadow-none" name="nama" required placeholder="Contoh: Gram, ml, Kg, Cup, Pcs">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Keterangan</label>
                            <input type="text" class="form-control shadow-none" name="keterangan" placeholder="Contoh: Satuan berat bubuk / volume cairan">
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="aktifSwitchNew" name="aktif" value="1" checked>
                            <label class="form-check-label small fw-semibold" for="aktifSwitchNew">Status Aktif (Tampil di Dropdown)</label>
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
