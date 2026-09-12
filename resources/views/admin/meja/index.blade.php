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
                                        <button type="button" class="btn-action btn-action-print text-primary" title="Lihat & Unduh QR Meja" onclick="showQRModal('{{ $m->table_number }}', '{{ $m->qr_token }}', '{{ addslashes($m->name ?? '') }}')">
                                            <i class="bi bi-qr-code fs-5"></i>
                                        </button>
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
    <!-- Modal Preview QR Meja -->
    <div class="modal fade" id="qrPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                    <span class="badge bg-danger-subtle text-danger px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.78rem;">
                        <i class="bi bi-qr-code-scan me-1"></i> QR Code Meja Pelanggan
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body text-center px-4 pt-3 pb-2">
                    <div class="p-3 bg-light rounded-4 border mb-3 text-center position-relative">
                        <div class="text-uppercase fw-bold text-danger tracking-wider mb-1" style="font-size: 0.75rem; letter-spacing: 1.5px;">☕ DYNASTY CAFE</div>
                        <h3 class="fw-bolder text-dark mb-0" id="modalTableTitle">Meja --</h3>
                        <p class="text-muted small mb-3" id="modalTableSubtitle">Scan QR untuk melihat menu & memesan</p>
                        
                        <div class="p-3 bg-white rounded-3 d-inline-block border shadow-sm mb-2">
                            <img id="modalQrImage" src="" alt="QR Code Meja" class="img-fluid" style="width: 210px; height: 210px; object-fit: contain;">
                        </div>
                        
                        <div class="text-muted small fw-medium mt-1">Arahkan kamera smartphone ke QR Code ini</div>
                    </div>
                    
                    <div class="input-group mb-2 shadow-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-link-45deg"></i></span>
                        <input type="text" class="form-control font-monospace small bg-white border-start-0 text-truncate" id="modalQrUrl" readonly style="font-size: 0.75rem;">
                        <button class="btn btn-outline-secondary btn-sm px-3 fw-semibold" type="button" onclick="copyQrUrl()">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <div class="text-muted" style="font-size: 0.72rem;">Link otomatis disesuaikan dengan domain atau IP perangkat saat ini</div>
                </div>
                
                <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex flex-column gap-2">
                    <div class="d-flex w-100 gap-2">
                        <button type="button" class="btn btn-dark w-50 rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-sm" onclick="downloadQrModalImage()">
                            <i class="bi bi-download"></i> Unduh PNG
                        </button>
                        <button type="button" class="btn text-white w-50 rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="background-color: #8b211e;" onclick="printQrFromModal()">
                            <i class="bi bi-printer"></i> Cetak QR
                        </button>
                    </div>
                    <button type="button" class="btn btn-light w-100 rounded-3 py-2 text-muted fw-semibold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

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

    let currentPrintTable = '';
    let currentPrintUrl = '';

    function showQRModal(tableNumber, qrToken, tableName) {
        currentPrintTable = tableNumber;
        currentPrintUrl = window.location.origin + '/?qr_token=' + qrToken;
        
        document.getElementById('modalTableTitle').innerText = 'Meja ' + tableNumber;
        document.getElementById('modalTableSubtitle').innerText = tableName ? (tableName + ' • Scan untuk memesan') : 'Scan QR untuk melihat menu & memesan';
        document.getElementById('modalQrUrl').value = currentPrintUrl;
        
        const qrApi = `https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=${encodeURIComponent(currentPrintUrl)}`;
        document.getElementById('modalQrImage').src = qrApi;
        
        const modal = new bootstrap.Modal(document.getElementById('qrPreviewModal'));
        modal.show();
    }

    function copyQrUrl() {
        const copyText = document.getElementById('modalQrUrl');
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value).then(() => {
            alert('Link pemesanan Meja ' + currentPrintTable + ' berhasil disalin!');
        }).catch(() => {
            document.execCommand('copy');
            alert('Link pemesanan Meja ' + currentPrintTable + ' berhasil disalin!');
        });
    }

    function downloadQrModalImage() {
        const qrApi = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(currentPrintUrl)}`;
        const fileName = `QR-Meja-${currentPrintTable || 'Dynasty'}.png`;

        fetch(qrApi)
            .then(res => res.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(() => {
                // Fallback direct open/download
                const a = document.createElement('a');
                a.href = qrApi;
                a.download = fileName;
                a.target = '_blank';
                a.click();
            });
    }

    function printQrFromModal() {
        const qrApi = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodeURIComponent(currentPrintUrl)}`;
        const printWindow = window.open('', '_blank', 'width=520,height=680');
        if (!printWindow) {
            alert('Pop-up terblokir oleh browser. Harap izinkan pop-up untuk mencetak.');
            return;
        }
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>QR Code Meja ${currentPrintTable} - Dynasty Cafe</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 98vh; margin: 0; text-align: center; background: #fff; }
                    .qr-card { border: 3px dashed #8b211e; padding: 32px 28px; border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); max-width: 320px; }
                    h2 { margin: 0; font-size: 18px; color: #8b211e; letter-spacing: 2px; text-transform: uppercase; font-weight: 800; }
                    h1 { margin: 8px 0 14px 0; font-size: 32px; color: #1c1917; font-weight: 800; }
                    img { border-radius: 12px; margin: 6px 0; }
                    .note { margin-top: 14px; font-size: 14px; font-weight: 600; color: #374151; }
                    .url { font-size: 10px; color: #9ca3af; word-break: break-all; margin-top: 8px; font-family: monospace; }
                    @media print {
                        body { background: transparent; }
                        .qr-card { box-shadow: none; }
                    }
                </style>
            </head>
            <body>
                <div class="qr-card">
                    <h2>☕ DYNASTY CAFE</h2>
                    <h1>Meja ${currentPrintTable}</h1>
                    <img src="${qrApi}" width="220" height="220" alt="QR Code" onload="window.print();" />
                    <div class="note">Scan untuk melihat menu & memesan</div>
                    <div class="url">${currentPrintUrl}</div>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
    }
</script>
@endpush
@endsection
