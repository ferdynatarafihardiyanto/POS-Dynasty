@extends('layouts.pos')

@push('styles')
<style>
    /* Table Styling */
    .table-meja th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
    }
    .table-meja td {
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
    .status-available { background-color: #dcfce7; color: #16a34a; }
    .status-occupied { background-color: #fee2e2; color: #dc2626; }
    
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
    .btn-action-print { color: #3b82f6; }
    
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
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626; max-width: 450px; margin-left: auto;">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div class="fw-bold small">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 py-2 px-3 shadow-sm border-0" style="background-color: #fee2e2; color: #dc2626; max-width: 450px; margin-left: auto;">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div class="fw-bold small">{{ $errors->first() }}</div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Manajemen Meja</h5>
                        <div class="text-muted small">Kelola ketersediaan meja dan cetak QR Code pesanan</div>
                    </div>
                    <button class="btn text-white rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahMejaModal">
                        <i class="bi bi-plus-lg"></i> Tambah Meja
                    </button>
                </div>

                <div class="bg-white rounded-4 border shadow-sm overflow-hidden mb-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-meja mb-0">
                            <thead>
                                <tr>
                                    <th>No. Meja</th>
                                    <th>Nama / Deskripsi</th>
                                    <th>Token QR</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mejas as $m)
                                <tr>
                                    <td class="fw-bold text-dark">Meja {{ $m->table_number }}</td>
                                    <td class="text-muted">{{ $m->name ?? '-' }}</td>
                                    <td class="text-muted font-monospace small bg-light rounded px-2">{{ $m->qr_token }}</td>
                                    <td class="text-end">
                                        <button class="btn-action btn-action-print" title="Cetak QR" onclick="printQR('{{ $m->table_number }}', '{{ url('/?qr_token=' . $m->qr_token) }}')"><i class="bi bi-qr-code"></i></button>
                                        <button type="button" class="btn-action btn-action-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editMejaModal{{ $m->id }}"><i class="bi bi-pencil"></i></button>
                                        <form action="{{ route('admin.meja.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus meja ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada data meja</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Menampilkan total {{ $mejas->total() }} meja
                    </div>
                    <div>
                        {{ $mejas->links('pagination::bootstrap-5') }}
                    </div>
                </div>
    </div>

    <!-- Modal Tambah Meja -->
    <div class="modal fade" id="tambahMejaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Meja Baru</h5>
                        <div class="text-muted small">Masukkan detail meja</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.meja.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="active">
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label">Nomor Meja <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="table_number" placeholder="Contoh: 01, VIP-1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Meja (Opsional)</label>
                            <input type="text" class="form-control" name="name" placeholder="Contoh: Meja Depan Jendela">
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

    <!-- Modals Edit Meja -->
    @foreach($mejas as $m)
    <div class="modal fade" id="editMejaModal{{ $m->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Edit Meja</h5>
                        <div class="text-muted small">Ubah informasi meja</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.meja.update', $m->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="{{ $m->status ?? 'active' }}">
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label">Nomor Meja <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="table_number" value="{{ $m->table_number }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Meja (Opsional)</label>
                            <input type="text" class="form-control" name="name" value="{{ $m->name }}">
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

    function printQR(tableNumber, url) {
        const qrApi = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(url)}`;
        
        const printWindow = window.open('', '_blank', 'width=400,height=500');
        printWindow.document.write(`
            <html>
            <head>
                <title>Cetak QR Meja ${tableNumber}</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                        margin: 0;
                        text-align: center;
                    }
                    .qr-container {
                        border: 2px dashed #000;
                        padding: 30px;
                        border-radius: 15px;
                        display: inline-block;
                    }
                    h1 {
                        margin-top: 0;
                        font-size: 28px;
                        margin-bottom: 10px;
                    }
                    .url-text {
                        margin-top: 20px;
                        font-size: 14px;
                        color: #555;
                    }
                </style>
            </head>
            <body>
                <div class="qr-container">
                    <h1>Meja ${tableNumber}</h1>
                    <img src="${qrApi}" alt="QR Code Meja ${tableNumber}" onload="window.print(); window.close();" />
                    <div class="url-text">Scan untuk memesan</div>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endpush
@endsection
