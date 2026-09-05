@extends('layouts.pos')

@push('styles')
<style>
    .upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 120px;
        width: 120px;
        background-color: #f9fafb;
        cursor: pointer;
        transition: all 0.2s;
    }
    .upload-box:hover {
        border-color: #8b211e;
        background-color: #fffaf9;
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
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Profil Toko</h4>
            <div class="text-muted small">Kelola identitas, pajak, dan informasi struk kafe Anda</div>
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
        
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="bg-white rounded-4 border shadow-sm p-4 p-md-5">
                    
                    <form action="#" method="POST">
                        <div class="d-flex gap-4 align-items-start mb-5 pb-4 border-bottom">
                            <div>
                                <label class="upload-box text-center p-2 mb-2">
                                    <input type="file" class="d-none" name="logo">
                                    <i class="bi bi-shop text-muted fs-3 mb-1"></i>
                                    <div class="small text-muted" style="font-size: 0.7rem;">Upload Logo</div>
                                </label>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-3">Informasi Dasar</h5>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Nama Kafe / Resto <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" value="Dynasty Cafe" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                                        <input type="text" class="form-control" value="0812-3456-7890">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Instagram / Sosmed</label>
                                        <input type="text" class="form-control" value="@dynastycafe.id">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Alamat Lengkap</label>
                                        <textarea class="form-control" rows="2">Jl. Mawar No. 123, Jakarta Selatan</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5 pb-4 border-bottom">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-3">Pengaturan Pajak & Biaya</h5>
                                <div class="mb-3">
                                    <label class="form-label">Pajak Resto (PB1) %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control border-end-0" value="10">
                                        <span class="input-group-text bg-white">%</span>
                                    </div>
                                    <div class="form-text" style="font-size: 0.7rem;">Akan otomatis ditambahkan ke total belanja. Kosongkan jika tidak ada.</div>
                                </div>
                                <div>
                                    <label class="form-label">Service Charge %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control border-end-0" value="5">
                                        <span class="input-group-text bg-white">%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-3">Tampilan Struk Belanja</h5>
                                <div class="mb-3">
                                    <label class="form-label">Pesan Footer Struk (Penutup)</label>
                                    <textarea class="form-control" rows="3">Terima kasih atas kunjungan Anda!
Silakan datang kembali.</textarea>
                                </div>
                                <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3 border">
                                    <div>
                                        <div class="fw-bold small">Cetak Logo di Struk?</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Hanya berlaku untuk printer thermal cetak gambar</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light fw-bold px-4 border">Batal</button>
                            <button type="button" class="btn text-white fw-bold px-5" style="background-color: #8b211e;">Simpan Perubahan</button>
                        </div>
                    </form>
                    
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
