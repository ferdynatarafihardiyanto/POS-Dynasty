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
    /* Modal Bukti Simpan Estetik */
    .save-modal-glow {
        animation: pulseGreenGlow 2s infinite ease-in-out;
    }
    @keyframes pulseGreenGlow {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.45);
        }
        70% {
            transform: scale(1.05);
            box-shadow: 0 0 0 14px rgba(16, 185, 129, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between p-3 p-md-4 bg-white border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-light border rounded-3 p-2 d-lg-none shadow-xs d-flex align-items-center justify-content-center" id="openSidebarBtn" title="Buka Navigasi" style="width: 38px; height: 38px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">Profil Toko</h4>
                <div class="text-muted small">Kelola identitas, pajak, dan informasi struk kafe Anda</div>
            </div>
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
            <div class="col-md-10 col-lg-9">


                <div class="bg-white rounded-4 border shadow-sm p-4 p-md-5">
                    
                    <form id="storeProfileForm" onsubmit="event.preventDefault(); saveStoreProfile();">
                        <div class="d-flex flex-column flex-md-row gap-4 align-items-start mb-4 pb-4 border-bottom">
                            
                            <!-- Sisi Kiri: Foto / Logo Kafe & Preview Struk Hitam Putih (B&W) -->
                            <div class="d-flex flex-column align-items-center" style="min-width: 145px;">
                                <label class="upload-box text-center p-2 mb-1 position-relative overflow-hidden shadow-xs" title="Klik untuk pilih / ganti foto logo kafe">
                                    <input type="file" class="d-none" name="logo" id="logoInput" accept="image/*" onchange="previewLogo(this)">
                                    <div id="logoPlaceholder" class="d-flex flex-column align-items-center justify-content-center h-100">
                                        <i class="bi bi-camera text-muted fs-3 mb-1"></i>
                                        <div class="fw-semibold text-muted" style="font-size: 0.72rem;">Upload Foto / Logo</div>
                                    </div>
                                    <img id="logoPreview" src="" alt="Logo Toko" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover rounded-2 d-none">
                                </label>
                                <div class="text-muted text-center mb-2" style="font-size: 0.68rem;">Rasio 1:1 (Persegi)</div>

                                <!-- Box Pratinjau Tampilan di Struk Fisik (Wajib Hitam Putih / Thermal B&W) -->
                                <div class="p-2 rounded-3 border bg-light text-center w-100" style="max-width: 145px;">
                                    <div class="fw-bold text-dark mb-1 d-flex align-items-center justify-content-center gap-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-printer text-dark"></i> Di Struk (Hitam Putih)
                                    </div>
                                    <div class="p-2 bg-white rounded border border-dashed d-flex align-items-center justify-content-center overflow-hidden" style="min-height: 72px;">
                                        <img id="receiptLogoBwPreview" src="" alt="Struk Hitam Putih" class="d-none" style="max-width: 65px; max-height: 65px; object-fit: contain; filter: grayscale(100%) contrast(180%) brightness(85%); -webkit-filter: grayscale(100%) contrast(180%) brightness(85%); mix-blend-mode: multiply;">
                                        <div id="receiptLogoEmptyNotice" class="text-muted text-center" style="font-size: 0.68rem; line-height: 1.2;">
                                            <i class="bi bi-receipt fs-5 mb-1 d-block"></i>
                                            <span>Hitam Putih<br>(Thermal B&W)</span>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark text-white mt-2 px-2 py-1 rounded-pill" style="font-size: 0.62rem;">
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size: 6px;"></i>100% Monokrom
                                    </span>
                                </div>

                                <!-- Switch Cetak Logo di Struk -->
                                <div class="form-check form-switch mt-3 text-start w-100 ps-4" style="font-size: 0.75rem;">
                                    <input class="form-check-input" type="checkbox" id="cetakLogoStruk" checked style="cursor: pointer; transform: scale(1.05);">
                                    <label class="form-check-label fw-semibold text-dark" for="cetakLogoStruk" style="cursor: pointer;">
                                        Cetak Logo Struk
                                    </label>
                                </div>

                                <!-- Tombol Cek Struk Thermal -->
                                <button type="button" class="btn btn-sm btn-dark w-100 mt-3 rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="font-size: 0.75rem;" onclick="openReceiptPreviewModal()">
                                    <i class="bi bi-receipt"></i> Cek Struk (B&W)
                                </button>
                            </div>

                            <!-- Sisi Kanan: Form Informasi Dasar & Pengaturan Struk Lengkap -->
                            <div class="flex-grow-1 w-100">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2 text-dark">
                                        <i class="bi bi-info-circle text-danger"></i> Informasi Dasar & Pengaturan Struk
                                    </h5>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bi bi-receipt-cutoff me-1"></i> Data Struk
                                        </span>
                                        <button type="button" class="btn btn-sm btn-dark rounded-3 px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5 shadow-sm text-nowrap" style="font-size: 0.75rem;" onclick="openReceiptPreviewModal()" title="Lihat Pratinjau Struk Thermal">
                                            <i class="bi bi-eye-fill"></i> Pratinjau Struk
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label">Nama Kafe / Resto <span class="text-danger">*</span> <span class="text-muted fw-normal" style="font-size: 0.75rem;">(Header Utama Struk)</span></label>
                                        <input type="text" class="form-control" id="namaToko" value="Dynasty Cafe" required placeholder="Contoh: Dynasty Cafe">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Slogan / Tagline <span class="text-muted fw-normal" style="font-size: 0.75rem;">(Sub-header Struk)</span></label>
                                        <input type="text" class="form-control" id="slogan" value="Authentic Coffee & Eatery" placeholder="Contoh: Authentic Coffee & Eatery">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Telepon / WhatsApp <span class="text-muted fw-normal" style="font-size: 0.75rem;">(Kontak di Struk)</span></label>
                                        <input type="text" class="form-control" id="telepon" value="0812-3456-7890" placeholder="Contoh: 0812-3456-7890">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Instagram / Sosmed / Website <span class="text-muted fw-normal" style="font-size: 0.75rem;">(Footer Bawah Struk)</span></label>
                                        <input type="text" class="form-control" id="sosmed" value="@dynastycafe.id" placeholder="Contoh: @dynastycafe.id atau domain website">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Alamat Lengkap <span class="text-muted fw-normal" style="font-size: 0.75rem;">(Alamat Tercetak di Header Struk)</span></label>
                                        <textarea class="form-control" id="alamat" rows="2" placeholder="Masukkan alamat lengkap kafe">Jl. Mawar No. 123, Jakarta Selatan</textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Pesan Footer Struk (Penutup) <span class="text-muted fw-normal" style="font-size: 0.75rem;">(Ucapan Terima Kasih Tercetak di Bawah Struk)</span></label>
                                        <textarea class="form-control" id="pesanFooterStruk" rows="2" placeholder="Contoh: Terima kasih atas kunjungan Anda! Silakan datang kembali.">Terima kasih atas kunjungan Anda!
Silakan datang kembali.</textarea>
                                    </div>

                                    <!-- Form Khusus WiFi Pelanggan (Bisa Tambah Banyak WiFi) -->
                                    <div class="col-12 mt-3">
                                        <div class="p-3 p-md-4 rounded-3 border" style="background: #fafafa; border-color: #e5e7eb !important;">
                                            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-2 d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; background-color: #8b211e;">
                                                        <i class="bi bi-wifi fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark small">Akses WiFi Pengunjung (Tercetak di Struk)</div>
                                                        <div class="text-muted" style="font-size: 0.72rem;">Daftar koneksi WiFi dan password yang otomatis dicetak pada bagian bawah struk</div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill" style="font-size: 0.7rem;">
                                                        <i class="bi bi-broadcast me-1"></i> Hotspot Kafe
                                                    </span>
                                                    <button type="button" class="btn btn-sm text-white rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm" style="background-color: #8b211e; font-size: 0.78rem;" onclick="addWifiNetwork()">
                                                        <i class="bi bi-plus-circle"></i> + Tambah WiFi
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Container Daftar Jaringan WiFi Dinamis -->
                                            <div id="wifiListContainer" class="d-flex flex-column gap-3">
                                                <!-- Dynamic WiFi cards rendered here -->
                                            </div>

                                            <div id="emptyWifiNotice" class="text-center py-3 border border-dashed rounded-3 bg-white text-muted small mt-2" style="display: none;">
                                                <i class="bi bi-wifi-off me-1"></i> Belum ada jaringan WiFi. Klik tombol <b>"+ Tambah WiFi"</b> di atas untuk menambahkan SSID & Password WiFi.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bagian Custom Form Mandiri (Bisa Custom Form Sendiri) -->
                                    <div class="col-12 mt-4 pt-3 border-top">
                                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                            <div>
                                                <label class="form-label mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                                    <i class="bi bi-ui-checks-grid text-danger"></i> Formulir Kustom Mandiri (Custom Form)
                                                </label>
                                                <div class="text-muted" style="font-size: 0.72rem;">Anda bebas menambahkan form input sendiri sesuai kebutuhan (misal: TikTok, No. Rekening, Jam Buka, dll.)</div>
                                            </div>
                                            <button type="button" class="btn btn-sm text-white rounded-3 px-3 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm" style="background-color: #8b211e; font-size: 0.8rem;" onclick="addCustomField()">
                                                <i class="bi bi-plus-circle"></i> + Tambah Form Kustom
                                            </button>
                                        </div>

                                        <!-- Container Baris Form Kustom -->
                                        <div id="customFieldsContainer" class="d-flex flex-column gap-3">
                                            <!-- Dynamic rows rendered here -->
                                        </div>

                                        <div id="emptyCustomFieldNotice" class="text-center py-3 border border-dashed rounded-3 bg-light text-muted small" style="display: none;">
                                            <i class="bi bi-pencil-square me-1"></i> Belum ada form kustom tambahan. Klik tombol <b>"+ Tambah Form Kustom"</b> di atas untuk membuat form baru Anda sendiri.
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Row Pengaturan Pajak & Biaya Transaksi -->
                        <div class="row g-4 mb-4 pb-4 border-bottom">
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-percent text-danger"></i> Pengaturan Pajak & Biaya
                                </h5>
                                <div class="mb-3">
                                    <label class="form-label">Pajak Resto (PB1) %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control border-end-0" id="pajakResto" value="10">
                                        <span class="input-group-text bg-white">%</span>
                                    </div>
                                    <div class="form-text" style="font-size: 0.7rem;">Otomatis ditambahkan ke kalkulasi total pesanan kasir dan customer.</div>
                                </div>
                                <div>
                                    <label class="form-label">Service Charge %</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control border-end-0" id="serviceCharge" value="5">
                                        <span class="input-group-text bg-white">%</span>
                                    </div>
                                    <div class="form-text" style="font-size: 0.7rem;">Biaya layanan kafe. Kosongkan (0) jika tidak dikenakan biaya layanan.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                                    <i class="bi bi-shield-check text-success"></i> Standar Cetak Struk Kasir & Pelanggan
                                </h5>
                                <div class="rounded-3 border bg-light p-3 p-sm-4 shadow-xs" style="padding: 1.25rem;">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="rounded-3 bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                                            <i class="bi bi-printer fs-5"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem; letter-spacing: -0.2px;">Format Kertas Thermal 58mm / 80mm</div>
                                            <div class="text-secondary" style="font-size: 0.8rem; line-height: 1.55;">
                                                Desain struk dirancang monokrom hitam putih dengan teks tajam, font monospace Courier New, pemotong gerigi, dan logo kontras tinggi.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-3 mt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <button type="button" class="btn btn-dark px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm rounded-3" style="font-size: 0.82rem;" onclick="openReceiptPreviewModal()">
                                            <i class="bi bi-receipt"></i>
                                            <span>Buka Pratinjau Struk (Hitam Putih)</span>
                                        </button>
                                        <div class="text-muted d-inline-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                                            <span>Perubahan otomatis diterapkan seketika.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light fw-bold px-4 border" onclick="resetStoreProfile()">Reset Semula</button>
                            <button type="button" class="btn text-white fw-bold px-5 shadow-sm d-flex align-items-center gap-2" style="background-color: #8b211e;" onclick="saveStoreProfile()">
                                <i class="bi bi-check2-circle"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Bukti Simpan Estetik -->
<div class="modal fade" id="saveSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden position-relative">
            <div class="modal-body p-4 text-center">
                
                <!-- Icon Animasi Centang Sukses Modern -->
                <div class="position-relative d-inline-block my-3">
                    <div class="position-absolute w-100 h-100 rounded-circle save-modal-glow" style="background-color: rgba(16, 185, 129, 0.25); filter: blur(4px);"></div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg position-relative" style="width: 70px; height: 70px; background: linear-gradient(135deg, #10b981, #059669); color: white;">
                        <i class="bi bi-check-lg" style="font-size: 2.4rem; -webkit-text-stroke: 1px;"></i>
                    </div>
                </div>

                <h5 class="fw-bold text-dark mb-1 font-display">Perubahan Berhasil Disimpan!</h5>
                <p class="text-muted small mb-3">Informasi profil toko, jaringan WiFi, dan formulir kustom Anda telah diperbarui secara aman.</p>

                <!-- Kotak Bukti Simpan Bergaya Resi / Receipt -->
                <div class="rounded-3 p-3 text-start mb-4 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                    <div class="text-uppercase fw-bold text-muted mb-2 pb-2 border-bottom d-flex align-items-center justify-content-between" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                        <span><i class="bi bi-shield-check text-success me-1"></i> Bukti Penyimpanan</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">Tersimpan</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                        <span class="text-muted small">☕ Kafe / Resto:</span>
                        <span class="fw-bold text-dark small text-truncate ms-2" id="modalSummaryToko">Dynasty Cafe</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                        <span class="text-muted small">📶 Jaringan WiFi:</span>
                        <span id="modalSummaryWifi" class="fw-semibold text-dark small">2 Jaringan Aktif</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-light">
                        <span class="text-muted small">📝 Form Kustom:</span>
                        <span id="modalSummaryCustom" class="fw-semibold text-dark small">2 Form Tambahan</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="text-muted small">🕒 Waktu Simpan:</span>
                        <span class="text-muted font-monospace small" id="modalSummaryTime" style="font-size: 0.75rem;">-</span>
                    </div>
                </div>

                <!-- Tombol Konfirmasi Estetik -->
                <button type="button" class="btn text-white w-100 py-2 rounded-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" style="background-color: #8b211e;" data-bs-dismiss="modal">
                    <i class="bi bi-check2-circle fs-5"></i> Selesai & Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pratinjau Struk Kasir & Pelanggan (Thermal 58mm Hitam Putih) -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden position-relative bg-dark">
            
            <!-- Header Modal -->
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom border-secondary bg-dark text-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-printer text-warning"></i>
                    <span class="font-monospace fw-bold small text-uppercase" style="letter-spacing: 0.5px;">Pratinjau Struk (Hitam Putih)</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Receipt Container Area -->
            <div class="p-3 py-4 bg-black d-flex flex-column align-items-center overflow-auto" style="max-height: 78vh;">
                
                <!-- Top Paper Tear Sawtooth Edge (Gerigi Kertas Thermal) -->
                <div class="w-100" style="max-width: 320px; margin-bottom: -1px; overflow: hidden; line-height: 0;">
                    <svg viewBox="0 0 240 10" preserveAspectRatio="none" style="width: 100%; height: 10px; fill: #ffffff;">
                        <path d="M0,10 L5,0 L10,10 L15,0 L20,10 L25,0 L30,10 L35,0 L40,10 L45,0 L50,10 L55,0 L60,10 L65,0 L70,10 L75,0 L80,10 L85,0 L90,10 L95,0 L100,10 L105,0 L110,10 L115,0 L120,10 L125,0 L130,10 L135,0 L140,10 L145,0 L150,10 L155,0 L160,10 L165,0 L170,10 L175,0 L180,10 L185,0 L190,10 L195,0 L200,10 L205,0 L210,10 L215,0 L220,10 L225,0 L230,10 L235,0 L240,10 Z"></path>
                    </svg>
                </div>

                <!-- Printable Paper Sheet (Authentic Thermal 58mm Monokrom) -->
                <div id="thermalReceiptPreviewPrintArea" class="bg-white text-dark shadow-sm w-100" style="max-width: 320px; padding: 18px 24px; box-sizing: border-box; font-family: 'Courier New', Courier, Consolas, Monaco, monospace; font-size: 11px; line-height: 1.35; color: #000000 !important;">
                    
                    <!-- Logo Toko Hitam Putih / Thermal Monochrome -->
                    <div id="modalReceiptLogoContainer" class="text-center pb-2">
                        <img id="modalReceiptLogoBw" src="" alt="Logo Struk B&W" class="d-none mx-auto" style="max-height: 55px; max-width: 75px; object-fit: contain; filter: grayscale(100%) contrast(180%) brightness(85%); -webkit-filter: grayscale(100%) contrast(180%) brightness(85%); mix-blend-mode: multiply;">
                    </div>

                    <!-- Header Toko -->
                    <div class="text-center pb-2">
                        <div class="fw-bold text-uppercase" id="modalReceiptNama" style="font-size: 14px; letter-spacing: 0.5px;">Dynasty Cafe</div>
                        <div class="fw-bold text-uppercase mt-1" id="modalReceiptSlogan" style="font-size: 10px;">Authentic Coffee & Eatery</div>
                        <div class="mt-1" id="modalReceiptAlamat" style="font-size: 10px; line-height: 1.3;">Jl. Mawar No. 123, Jakarta Selatan</div>
                        <div id="modalReceiptTelepon" style="font-size: 9.5px;">Telp/WA: 0812-3456-7890</div>
                    </div>

                    <!-- Dashed Line -->
                    <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                    <!-- Metadata Transaksi -->
                    <div style="font-size: 11px;">
                        <div class="d-flex justify-content-between"><span>Pembeli</span><span class="fw-bold">Natan (Pelanggan)</span></div>
                        <div class="d-flex justify-content-between"><span>Pembayaran</span><span>QRIS</span></div>
                        <div class="d-flex justify-content-between"><span>Tanggal</span><span id="modalReceiptDate">09/09/2026 08:30</span></div>
                        <div class="d-flex justify-content-between"><span>No Struk</span><span class="fw-bold">SR44646</span></div>
                        <div class="d-flex justify-content-between"><span>Kasir</span><span>Masdarul</span></div>
                    </div>

                    <!-- Dashed Line -->
                    <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                    <!-- Items Contoh Pesanan -->
                    <div class="py-1">
                        <div class="mb-2">
                            <div class="fw-bold text-dark">Kopi Susu Gula Aren (Ice)</div>
                            <div class="d-flex justify-content-between"><span>18,000 x 1</span><span class="fw-bold">18,000</span></div>
                            <div style="font-size: 10px; color: #333; padding-left: 6px;">+ Extra Espresso Shot</div>
                        </div>
                        <div class="mb-1">
                            <div class="fw-bold text-dark">Butter Croissant</div>
                            <div class="d-flex justify-content-between"><span>24,000 x 1</span><span class="fw-bold">24,000</span></div>
                        </div>
                    </div>

                    <!-- Dashed Line -->
                    <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                    <!-- Total -->
                    <div style="font-size: 11px;">
                        <div class="d-flex justify-content-between fw-bold" style="font-size: 11.5px;">
                            <span>TOTAL 2 QTY</span>
                            <span>42,000</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Bayar (QRIS)</span>
                            <span>42,000</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Kembali</span>
                            <span>0</span>
                        </div>
                    </div>

                    <!-- Dashed Line -->
                    <div style="border-top: 1px dashed #000000; margin: 6px 0;"></div>

                    <!-- WiFi Section Dinamis -->
                    <div id="modalReceiptWifiList" style="font-size: 10px; line-height: 1.35; padding-top: 2px;">
                        <div>Wifi : Dynasty Cafe Free (Lt. 1)</div>
                        <div>Pass : kedaidynasty123</div>
                    </div>

                    <!-- Pesan Footer -->
                    <div id="modalReceiptFooterMsg" class="text-center pt-2.5" style="font-size: 10px; white-space: pre-line; line-height: 1.3;">
                        Terima kasih atas kunjungan Anda!
                        Silakan datang kembali.
                    </div>

                    <!-- Footer Link / Sosmed -->
                    <div id="modalReceiptSosmed" class="text-center pt-2 pb-1 fw-bold" style="font-size: 11px;">
                        @dynastycafe.id
                    </div>

                </div>

                <!-- Bottom Paper Tear Sawtooth Edge (Gerigi Kertas Thermal) -->
                <div class="w-100" style="max-width: 320px; margin-top: -1px; overflow: hidden; line-height: 0; transform: rotate(180deg);">
                    <svg viewBox="0 0 240 10" preserveAspectRatio="none" style="width: 100%; height: 10px; fill: #ffffff;">
                        <path d="M0,10 L5,0 L10,10 L15,0 L20,10 L25,0 L30,10 L35,0 L40,10 L45,0 L50,10 L55,0 L60,10 L65,0 L70,10 L75,0 L80,10 L85,0 L90,10 L95,0 L100,10 L105,0 L110,10 L115,0 L120,10 L125,0 L130,10 L135,0 L140,10 L145,0 L150,10 L155,0 L160,10 L165,0 L170,10 L175,0 L180,10 L185,0 L190,10 L195,0 L200,10 L205,0 L210,10 L215,0 L220,10 L225,0 L230,10 L235,0 L240,10 Z"></path>
                    </svg>
                </div>

            </div>

            <!-- Footer Modal Controls -->
            <div class="p-3 bg-dark border-top border-secondary d-flex gap-2">
                <button type="button" class="btn btn-light fw-bold flex-grow-1 py-2 font-monospace d-flex align-items-center justify-content-center gap-2 shadow-sm" style="font-size: 0.8rem;" onclick="printModalReceipt()">
                    <i class="bi bi-printer-fill"></i> Cetak Uji Coba (Hitam Putih)
                </button>
                <button type="button" class="btn btn-secondary fw-semibold px-3 py-2" data-bs-dismiss="modal" style="font-size: 0.8rem;">
                    Tutup
                </button>
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

    // Dynamic WiFi Networks Manager
    function addWifiNetwork(ssid = '', password = '') {
        const container = document.getElementById('wifiListContainer');
        const notice = document.getElementById('emptyWifiNotice');
        if (notice) notice.style.display = 'none';

        const count = container.querySelectorAll('.wifi-item-card').length + 1;
        const card = document.createElement('div');
        card.className = 'wifi-item-card p-3 rounded-3 border bg-white shadow-xs position-relative';
        card.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                <span class="fw-bold text-dark small d-flex align-items-center gap-2">
                    <i class="bi bi-router text-danger"></i> <span class="wifi-title-label">Jaringan WiFi #${count}</span>
                </span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none d-flex align-items-center gap-1" onclick="removeWifiNetwork(this)" title="Hapus Jaringan WiFi ini" style="font-size: 0.75rem;">
                    <i class="bi bi-trash3"></i> Hapus
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="form-label mb-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                        <i class="bi bi-wifi text-muted"></i> Nama WiFi (SSID)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-broadcast"></i></span>
                        <input type="text" class="form-control form-control-sm wifi-ssid-input" placeholder="Contoh: Dynasty_Lt1" value="${escapeHtml(ssid)}">
                    </div>
                    <div class="form-text" style="font-size: 0.68rem;">Nama sinyal WiFi kafe Anda</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label mb-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                        <i class="bi bi-key text-muted"></i> Password WiFi
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" class="form-control form-control-sm wifi-password-input" placeholder="Kata sandi WiFi" value="${escapeHtml(password)}">
                        <button class="btn btn-sm btn-outline-secondary bg-light border-start-0" type="button" onclick="togglePassRow(this)" title="Lihat / Sembunyikan Password" style="border-color: #d1d5db;">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text" style="font-size: 0.68rem;">Kata sandi untuk terhubung ke internet</div>
                </div>
            </div>
        `;
        container.appendChild(card);
        updateWifiTitles();
    }

    function removeWifiNetwork(button) {
        const card = button.closest('.wifi-item-card');
        if (card) {
            card.remove();
            updateWifiTitles();
            checkEmptyWifi();
        }
    }

    function updateWifiTitles() {
        const container = document.getElementById('wifiListContainer');
        const cards = container.querySelectorAll('.wifi-item-card');
        cards.forEach((card, idx) => {
            const titleSpan = card.querySelector('.wifi-title-label');
            if (titleSpan) {
                titleSpan.textContent = `Jaringan WiFi #${idx + 1}`;
            }
        });
    }

    function checkEmptyWifi() {
        const container = document.getElementById('wifiListContainer');
        const notice = document.getElementById('emptyWifiNotice');
        if (container.children.length === 0) {
            if (notice) notice.style.display = 'block';
        } else {
            if (notice) notice.style.display = 'none';
        }
    }

    function togglePassRow(button) {
        const inputGroup = button.closest('.input-group');
        const passInput = inputGroup.querySelector('.wifi-password-input');
        const icon = button.querySelector('i');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            passInput.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // Preview Logo Handler (Color & Strict Thermal Hitam Putih)
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const dataUrl = e.target.result;
                setLogoPreview(dataUrl);
                try {
                    localStorage.setItem('dynasty_logo_data', dataUrl);
                } catch(err) {}
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function setLogoPreview(dataUrl) {
        if (!dataUrl) return;
        const preview = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');
        const receiptPreview = document.getElementById('receiptLogoBwPreview');
        const receiptNotice = document.getElementById('receiptLogoEmptyNotice');
        const modalLogo = document.getElementById('modalReceiptLogoBw');

        if (preview) { preview.src = dataUrl; preview.classList.remove('d-none'); }
        if (placeholder) { placeholder.classList.add('d-none'); }
        if (receiptPreview) { receiptPreview.src = dataUrl; receiptPreview.classList.remove('d-none'); }
        if (receiptNotice) { receiptNotice.classList.add('d-none'); }
        if (modalLogo) { modalLogo.src = dataUrl; modalLogo.classList.remove('d-none'); }
    }

    // Open Real-time Black & White Thermal Receipt Modal
    function openReceiptPreviewModal() {
        const nama = document.getElementById('namaToko')?.value || 'Dynasty Cafe';
        const slogan = document.getElementById('slogan')?.value || '';
        const alamat = document.getElementById('alamat')?.value || '';
        const telepon = document.getElementById('telepon')?.value || '';
        const sosmed = document.getElementById('sosmed')?.value || '';
        const footer = document.getElementById('pesanFooterStruk')?.value || '';
        const cetakLogo = document.getElementById('cetakLogoStruk')?.checked ?? true;

        if (document.getElementById('modalReceiptNama')) document.getElementById('modalReceiptNama').textContent = nama;
        
        const sloganEl = document.getElementById('modalReceiptSlogan');
        if (sloganEl) {
            sloganEl.textContent = slogan;
            sloganEl.style.display = slogan ? 'block' : 'none';
        }

        const alamatEl = document.getElementById('modalReceiptAlamat');
        if (alamatEl) alamatEl.textContent = alamat;

        const telpEl = document.getElementById('modalReceiptTelepon');
        if (telpEl) {
            telpEl.textContent = telepon ? 'Telp/WA: ' + telepon : '';
            telpEl.style.display = telepon ? 'block' : 'none';
        }

        const footerEl = document.getElementById('modalReceiptFooterMsg');
        if (footerEl) {
            footerEl.textContent = footer;
            footerEl.style.display = footer ? 'block' : 'none';
        }

        const sosmedEl = document.getElementById('modalReceiptSosmed');
        if (sosmedEl) sosmedEl.textContent = sosmed || '@dynastycafe.id';

        // Live Date
        const now = new Date();
        const dateStr = String(now.getDate()).padStart(2, '0') + '/' + 
                        String(now.getMonth() + 1).padStart(2, '0') + '/' + 
                        now.getFullYear() + ' ' + 
                        String(now.getHours()).padStart(2, '0') + ':' + 
                        String(now.getMinutes()).padStart(2, '0');
        const dateEl = document.getElementById('modalReceiptDate');
        if (dateEl) dateEl.textContent = dateStr;

        // Render WiFi list dynamically from current form cards
        const wifiCards = document.querySelectorAll('.wifi-item-card');
        const modalWifi = document.getElementById('modalReceiptWifiList');
        if (modalWifi) {
            modalWifi.innerHTML = '';
            let hasAny = false;
            if (wifiCards.length > 0) {
                wifiCards.forEach(card => {
                    const ssid = card.querySelector('.wifi-ssid-input')?.value.trim() || '';
                    const password = card.querySelector('.wifi-password-input')?.value.trim() || '';
                    if (ssid || password) {
                        modalWifi.innerHTML += `<div>Wifi : ${escapeHtml(ssid)}</div><div>Pass : ${escapeHtml(password)}</div>`;
                        hasAny = true;
                    }
                });
            }
            if (!hasAny) {
                modalWifi.innerHTML = '<div>Wifi : Dynasty Cafe Free (Lt. 1)</div><div>Pass : kedaidynasty123</div>';
            }
        }

        // Handle Strict Black & White Logo in Modal
        const modalLogo = document.getElementById('modalReceiptLogoBw');
        const logoPreview = document.getElementById('logoPreview');
        if (modalLogo) {
            if (cetakLogo && logoPreview && logoPreview.src && !logoPreview.classList.contains('d-none')) {
                modalLogo.src = logoPreview.src;
                modalLogo.classList.remove('d-none');
            } else {
                modalLogo.classList.add('d-none');
            }
        }

        const modalEl = document.getElementById('receiptPreviewModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    // Direct Thermal Test Print from Modal
    function printModalReceipt() {
        const printArea = document.getElementById('thermalReceiptPreviewPrintArea');
        if (!printArea) return;
        
        let iframe = document.getElementById('adminThermalPreviewIframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'adminThermalPreviewIframe';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            document.body.appendChild(iframe);
        }
        
        const content = `<!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Struk Thermal Hitam Putih - POS Dynasty</title>
            <style>
                body {
                    font-family: 'Courier New', Courier, Consolas, Monaco, monospace;
                    width: 58mm;
                    margin: 0 auto;
                    padding: 2mm 3mm;
                    font-size: 11px;
                    line-height: 1.25;
                    color: #000;
                    background: #fff;
                }
                img {
                    filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                    -webkit-filter: grayscale(100%) contrast(180%) brightness(85%) !important;
                    mix-blend-mode: multiply;
                }
                @media print {
                    body { width: 58mm; margin: 0; padding: 2mm; }
                    @page { size: 58mm auto; margin: 0; }
                }
            </style>
        </head>
        <body>
            ${printArea.innerHTML}
        </body>
        </html>`;

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(content);
        doc.close();
        setTimeout(() => {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch(e) {}
        }, 300);
    }

    // Dynamic Custom Fields Manager
    function addCustomField(label = '', value = '') {
        const container = document.getElementById('customFieldsContainer');
        const notice = document.getElementById('emptyCustomFieldNotice');
        if (notice) notice.style.display = 'none';

        const row = document.createElement('div');
        row.className = 'custom-field-row d-flex align-items-center gap-2 p-2 rounded-3 border bg-white';
        row.innerHTML = `
            <div class="input-group" style="max-width: 220px;">
                <span class="input-group-text bg-light border-end-0 text-muted small"><i class="bi bi-tag"></i></span>
                <input type="text" class="form-control form-control-sm custom-label-input fw-semibold" placeholder="Nama Label (misal: TikTok)" value="${escapeHtml(label)}">
            </div>
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-light border-end-0 text-muted small"><i class="bi bi-pencil"></i></span>
                <input type="text" class="form-control form-control-sm custom-value-input" placeholder="Isi data form kustom ini..." value="${escapeHtml(value)}">
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-2 d-flex align-items-center justify-content-center" onclick="removeCustomField(this)" title="Hapus form kustom ini" style="height: 33px;">
                <i class="bi bi-trash3"></i>
            </button>
        `;
        container.appendChild(row);
    }

    function removeCustomField(button) {
        const row = button.closest('.custom-field-row');
        if (row) {
            row.remove();
            checkEmptyCustomFields();
        }
    }

    function checkEmptyCustomFields() {
        const container = document.getElementById('customFieldsContainer');
        const notice = document.getElementById('emptyCustomFieldNotice');
        if (container.children.length === 0) {
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Save Store Profile
    function saveStoreProfile() {
        const customRows = document.querySelectorAll('.custom-field-row');
        const customFields = [];
        customRows.forEach(row => {
            const label = row.querySelector('.custom-label-input')?.value.trim() || '';
            const value = row.querySelector('.custom-value-input')?.value.trim() || '';
            if (label || value) {
                customFields.push({ label, value });
            }
        });

        const wifiCards = document.querySelectorAll('.wifi-item-card');
        const wifiList = [];
        wifiCards.forEach(card => {
            const ssid = card.querySelector('.wifi-ssid-input')?.value.trim() || '';
            const password = card.querySelector('.wifi-password-input')?.value.trim() || '';
            if (ssid || password) {
                wifiList.push({ ssid, password });
            }
        });

        const logoData = localStorage.getItem('dynasty_logo_data') || '';

        const profileData = {
            namaToko: document.getElementById('namaToko')?.value || '',
            slogan: document.getElementById('slogan')?.value || '',
            telepon: document.getElementById('telepon')?.value || '',
            sosmed: document.getElementById('sosmed')?.value || '',
            alamat: document.getElementById('alamat')?.value || '',
            pesanFooterStruk: document.getElementById('pesanFooterStruk')?.value || '',
            cetakLogoStruk: document.getElementById('cetakLogoStruk')?.checked ?? true,
            wifiList: wifiList,
            wifiSsid: wifiList[0]?.ssid || '',
            wifiPassword: wifiList[0]?.password || '',
            customFields: customFields,
            pajakResto: document.getElementById('pajakResto')?.value || '0',
            serviceCharge: document.getElementById('serviceCharge')?.value || '0',
            logo_data: logoData
        };

        try {
            localStorage.setItem('dynasty_store_profile', JSON.stringify(profileData));
        } catch(err) {
            console.error('Failed to save to localStorage', err);
        }

        // Sync ke Server API (agar customer web & nota print transaksi langsung otomatis terupdate)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/api/profil-toko', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(profileData)
        }).then(res => res.json()).then(res => {
            console.log('[Profil Toko] Berhasil sync ke database server:', res);
        }).catch(err => {
            console.warn('[Profil Toko] Gagal sync ke server API:', err);
        });

        // Update data pada Modal Bukti Simpan
        const summaryToko = document.getElementById('modalSummaryToko');
        if (summaryToko) summaryToko.textContent = profileData.namaToko || 'Dynasty Cafe';

        const summaryWifi = document.getElementById('modalSummaryWifi');
        if (summaryWifi) {
            if (wifiList.length > 0) {
                summaryWifi.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-wifi me-1"></i>${wifiList.length} Jaringan Aktif</span>`;
            } else {
                summaryWifi.innerHTML = `<span class="text-muted small">0 Jaringan</span>`;
            }
        }

        const summaryCustom = document.getElementById('modalSummaryCustom');
        if (summaryCustom) {
            if (customFields.length > 0) {
                summaryCustom.innerHTML = `<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-ui-checks-grid me-1"></i>${customFields.length} Form Kustom</span>`;
            } else {
                summaryCustom.innerHTML = `<span class="text-muted small">0 Form</span>`;
            }
        }

        const summaryTime = document.getElementById('modalSummaryTime');
        if (summaryTime) {
            const now = new Date();
            const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            summaryTime.textContent = `${dateStr}, ${timeStr} WIB`;
        }

        // Tampilkan Modal Bukti Simpan
        const modalEl = document.getElementById('saveSuccessModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    // Load Store Profile on Page Open
    function loadStoreProfile() {
        // First load local logo if available
        const savedLogo = localStorage.getItem('dynasty_logo_data');
        if (savedLogo) {
            setLogoPreview(savedLogo);
        }

        const applyProfileToForm = (data) => {
            if (!data) return;
            if (data.namaToko !== undefined) document.getElementById('namaToko').value = data.namaToko;
            if (data.slogan !== undefined) document.getElementById('slogan').value = data.slogan;
            if (data.telepon !== undefined) document.getElementById('telepon').value = data.telepon;
            if (data.sosmed !== undefined) document.getElementById('sosmed').value = data.sosmed;
            if (data.alamat !== undefined) document.getElementById('alamat').value = data.alamat;

            if (data.pajakResto !== undefined) document.getElementById('pajakResto').value = data.pajakResto;
            if (data.serviceCharge !== undefined) document.getElementById('serviceCharge').value = data.serviceCharge;
            if (data.pesanFooterStruk !== undefined) document.getElementById('pesanFooterStruk').value = data.pesanFooterStruk;
            if (data.cetakLogoStruk !== undefined) document.getElementById('cetakLogoStruk').checked = data.cetakLogoStruk;

            // Logo from data
            if (data.logo_url) {
                setLogoPreview(data.logo_url);
            } else if (data.logo_data) {
                setLogoPreview(data.logo_data);
            }

            // WiFi list
            const wifiContainer = document.getElementById('wifiListContainer');
            wifiContainer.innerHTML = '';
            if (Array.isArray(data.wifiList) && data.wifiList.length > 0) {
                data.wifiList.forEach(w => {
                    addWifiNetwork(w.ssid, w.password);
                });
            } else if (data.wifiSsid || data.wifiPassword) {
                addWifiNetwork(data.wifiSsid || '', data.wifiPassword || '');
            } else {
                addWifiNetwork('Dynasty Cafe Free (Lt. 1)', 'kedaidynasty123');
                addWifiNetwork('Dynasty Cafe VIP (Lt. 2)', 'dynastyvip88');
            }

            // Custom Fields
            const container = document.getElementById('customFieldsContainer');
            container.innerHTML = '';
            if (Array.isArray(data.customFields) && data.customFields.length > 0) {
                data.customFields.forEach(f => {
                    addCustomField(f.label, f.value);
                });
            } else {
                addCustomField('Website Kafe', 'https://dynastycafe.id');
                addCustomField('No. Rekening Pembayaran', 'BCA 8920-1234-56 a.n Dynasty Cafe');
            }
            checkEmptyWifi();
            checkEmptyCustomFields();
        };

        // Check local storage first
        const saved = localStorage.getItem('dynasty_store_profile');
        if (saved) {
            try {
                applyProfileToForm(JSON.parse(saved));
            } catch(e) {
                console.error(e);
            }
        }

        // Then check backend API for freshest data
        fetch('/api/profil-toko')
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    applyProfileToForm(res.data);
                }
            })
            .catch(() => {});
    }

    function resetStoreProfile() {
        if (confirm('Kembalikan ke pengaturan profil bawaan?')) {
            localStorage.removeItem('dynasty_store_profile');
            localStorage.removeItem('dynasty_logo_data');
            location.reload();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadStoreProfile();
    });
</script>
@endpush
@endsection
