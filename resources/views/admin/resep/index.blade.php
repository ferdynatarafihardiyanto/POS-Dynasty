@extends('layouts.pos')

@push('styles')
<style>
    .table-custom th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;
        color: #4b5563; font-weight: 600; border-bottom: 1px solid #e5e7eb; padding: 12px 14px;
        background-color: #f9fafb;
    }
    .table-custom td {
        vertical-align: middle; font-size: 0.85rem; padding: 12px 14px; border-bottom: 1px solid #f3f4f6;
    }
    .metric-card {
        border-radius: 12px;
        padding: 14px 18px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .product-filter-item {
        transition: all 0.15s ease-in-out;
    }
    .product-filter-item:hover {
        background-color: #f8fafc;
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
                <h4 class="mb-0 fw-bold text-dark fs-5 fs-md-4">Resep Produk & HPP Otomatis</h4>
                <div class="text-muted small">Kalkulasi biaya bahan baku (HPP) dan ketersediaan porsi otomatis dari resep</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-4">
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

    <!-- Alert Success/Error -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show m-4 mb-0 rounded-3 py-2 px-3 shadow-sm border-0" role="alert" style="background-color: #dcfce7; color: #16a34a;">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show m-4 mb-0 rounded-3 py-2 px-3 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Perhatian:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-4 flex-grow-1 overflow-auto" style="background-color: #fcfcfc;">
        <div class="row g-4">
            <!-- Product List -->
            <div class="col-lg-4 col-md-5">
                <div class="bg-white rounded-4 border shadow-sm p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-dark">Daftar Menu & Resep</h6>
                        <span class="badge bg-light text-muted border">{{ $produks->count() }} Menu</span>
                    </div>
                    <div class="position-relative mb-3">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="recipeSearchInput" class="form-control rounded-3 ps-5 border py-2" style="font-size: 0.85rem;" placeholder="Cari nama menu..." onkeyup="filterRecipeProducts()">
                    </div>
                    <div class="list-group list-group-flush border-top overflow-auto" style="max-height: 580px;" id="recipeProductList">
                        @forelse($produks as $p)
                        @php
                            $hasDetail = $p->resep && $p->resep->detail->isNotEmpty();
                            $isSelected = request('produk_id') == $p->id;
                        @endphp
                        <a href="{{ route('admin.resep.index', ['produk_id' => $p->id]) }}" 
                           class="list-group-item list-group-item-action product-filter-item border-bottom py-3 {{ $isSelected ? 'active bg-light text-dark fw-bold' : '' }}" 
                           data-name="{{ strtolower($p->nama) }}"
                           {!! $isSelected ? 'style="border-left: 4px solid #8b211e !important;"' : '' !!}>
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold {{ $isSelected ? 'text-danger' : 'text-dark' }}">{{ $p->nama }}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $p->kategori->nama ?? 'Umum' }}</div>
                                </div>
                                <div class="text-end">
                                    @if($hasDetail)
                                        <span class="badge rounded-pill" style="background-color: #e0f2fe; color: #0369a1; font-size: 0.68rem;">
                                            <i class="bi bi-bezier2"></i> {{ $p->resep->detail->count() }} Bahan
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.68rem;">
                                            Item Satuan
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top border-light small" style="font-size: 0.75rem;">
                                <span>HPP: <strong class="text-dark">Rp {{ number_format($p->hpp, 0, ',', '.') }}</strong></span>
                                <span>Stok: <strong class="{{ $p->stok <= 5 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">{{ $p->stok }} {{ $hasDetail ? 'Porsi' : 'Pcs' }}</strong></span>
                            </div>
                        </a>
                        @empty
                        <div class="p-3 text-center text-muted small">Belum ada produk aktif</div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Recipe Details -->
            <div class="col-lg-8 col-md-7">
                <div class="bg-white rounded-4 border shadow-sm p-4 h-100">
                    @php
                        $selectedProduk = request('produk_id') ? $produks->firstWhere('id', request('produk_id')) : ($produks->first() ?? null);
                    @endphp

                    @if($selectedProduk)
                    @php
                        $hasDetail = $selectedProduk->resep && $selectedProduk->resep->detail->isNotEmpty();
                        $marginRp = $selectedProduk->harga - $selectedProduk->hpp;
                        $marginPct = $selectedProduk->harga > 0 ? round(($marginRp / $selectedProduk->harga) * 100, 1) : 0;
                    @endphp
                    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="fw-bold mb-0 text-dark">{{ $selectedProduk->nama }}</h4>
                                @if($hasDetail)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">
                                        <i class="bi bi-check2-circle me-1"></i> Resep Aktif
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 0.75rem;">
                                        Item Satuan (Belum Ada Bahan Baku)
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted small mt-1">
                                Komposisi bahan baku untuk 1 porsi pesanan. Stok dan HPP produk akan dihitung secara real-time.
                            </div>
                        </div>
                        <button class="btn btn-sm text-white fw-bold px-3 py-2 rounded-3 shadow-sm d-flex align-items-center gap-1" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahResepModal">
                            <i class="bi bi-plus-lg"></i> Tambah Bahan Baku
                        </button>
                    </div>

                    <!-- Metric Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="metric-card border-start border-4 border-danger">
                                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">HPP (BIAYA / PORSI)</div>
                                <div class="fs-5 fw-bold text-dark mt-1">Rp {{ number_format($selectedProduk->hpp, 0, ',', '.') }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    @if($hasDetail)
                                        <span class="text-success"><i class="bi bi-calculator"></i> Otomatis dari resep</span>
                                    @else
                                        <span class="text-muted">Biaya input manual</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="metric-card border-start border-4 border-primary">
                                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">HARGA JUAL</div>
                                <div class="fs-5 fw-bold text-dark mt-1">Rp {{ number_format($selectedProduk->harga, 0, ',', '.') }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Harga menu ke pelanggan</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="metric-card border-start border-4 border-success">
                                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">ESTIMASI MARGIN</div>
                                <div class="fs-5 fw-bold text-success mt-1">Rp {{ number_format($marginRp, 0, ',', '.') }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Margin laba: <strong>{{ $marginPct }}%</strong></div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-3">
                            <div class="metric-card border-start border-4 {{ $selectedProduk->stok <= 5 ? 'border-danger' : 'border-info' }}">
                                <div class="text-muted small text-uppercase fw-semibold" style="font-size: 0.7rem;">KAPASITAS PORSI</div>
                                <div class="fs-5 fw-bold {{ $selectedProduk->stok <= 5 ? 'text-danger' : 'text-primary' }} mt-1">
                                    {{ $selectedProduk->stok }} Porsi
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    @if($hasDetail)
                                        Batasan stok bahan baku terkecil
                                    @else
                                        Stok item satuan di toko
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Resep Detail -->
                    <div class="border rounded-3 overflow-hidden mb-3">
                        <table class="table table-hover table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Bahan Baku</th>
                                    <th>Takaran / Porsi</th>
                                    <th>Harga Beli Satuan</th>
                                    <th>Biaya per Porsi</th>
                                    <th>Stok Tersedia & Kapasitas</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($hasDetail)
                                    @php
                                        $totalBiayaResep = 0;
                                    @endphp
                                    @foreach($selectedProduk->resep->detail as $dtl)
                                    @php
                                        $bb = $dtl->bahanBaku;
                                        $biayaSubtotal = (float)$dtl->jumlah * ($bb ? (float)$bb->harga_beli : 0);
                                        $totalBiayaResep += $biayaSubtotal;
                                        $kapasitasBahan = ($bb && (float)$dtl->jumlah > 0) ? floor((float)$bb->stok / (float)$dtl->jumlah) : 0;
                                        $isBottleneck = ($kapasitasBahan == $selectedProduk->stok);
                                    @endphp
                                    <tr class="{{ $isBottleneck ? 'table-warning-subtle' : '' }}">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $bb->nama ?? 'Bahan Tidak Ditemukan' }}</div>
                                            <div class="small text-muted" style="font-size: 0.72rem;">Satuan: {{ $bb->satuan ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1 fw-bold fs-6">
                                                {{ floatval($dtl->jumlah) }} {{ $bb->satuan ?? '' }}
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            Rp {{ number_format($bb->harga_beli ?? 0, 0, ',', '.') }} / {{ $bb->satuan ?? '' }}
                                        </td>
                                        <td class="fw-bold text-dark">
                                            Rp {{ number_format($biayaSubtotal, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <div class="small">
                                                Stok: <strong>{{ floatval($bb->stok ?? 0) }} {{ $bb->satuan ?? '' }}</strong>
                                            </div>
                                            <div class="small {{ $kapasitasBahan <= 5 ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 0.75rem;">
                                                Maks: <strong>{{ $kapasitasBahan }} porsi</strong>
                                                @if($isBottleneck)
                                                    <span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem;">Faktor Pembatas</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.resep.detail.destroy', $dtl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus {{ $bb->nama }} dari resep {{ $selectedProduk->nama }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Hapus bahan">
                                                    <i class="bi bi-trash fs-6"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="table-light border-top">
                                        <td colspan="3" class="fw-bold text-end text-dark">TOTAL BIAYA HPP RESEP / PORSI:</td>
                                        <td colspan="3" class="fw-bold text-danger fs-6">
                                            Rp {{ number_format($totalBiayaResep, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-basket3 fs-1 text-muted d-block mb-2"></i>
                                            <div class="fw-bold">Belum Ada Komposisi Resep</div>
                                            <div class="small text-muted mb-3">Produk ini saat ini berstatus sebagai <strong>Item Satuan</strong> (stok & HPP diisi mandiri).</div>
                                            <button class="btn btn-sm text-white fw-bold px-3 py-2 rounded-3 shadow-sm" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahResepModal">
                                                <i class="bi bi-plus-lg me-1"></i> Tambahkan Bahan Baku Pertama
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Info Box -->
                    <div class="p-3 rounded-3 border bg-light small text-muted d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-primary fs-5 mt-n1"></i>
                        <div>
                            <strong>Cara Kerja Sistem Resep Dinasty POS:</strong>
                            <ul class="mb-0 ps-3 mt-1" style="font-size: 0.8rem; line-height: 1.5;">
                                <li><strong>HPP Otomatis:</strong> Dihitung langsung dari $\sum (\text{Takaran Bahan} \times \text{Harga Beli Bahan})$. Contoh: 1 cup butuh 15g bubuk (@ Rp 300) + 150ml susu (@ Rp 20) = Rp 4.500 + Rp 3.000 = <strong>Rp 7.500</strong>.</li>
                                <li><strong>Stok Porsi Otomatis:</strong> Dihitung berdasarkan bahan baku paling kritis (bottleneck). Contoh: Gula cukup untuk 100 porsi tetapi Susu hanya cukup untuk 10 porsi, maka stok menu ini menjadi <strong>10 Porsi</strong>.</li>
                                <li><strong>Pengurangan Saat Transaksi:</strong> Saat pesanan kasir dibayar, seluruh stok bahan baku di atas otomatis terpotong sesuai porsi pesanan.</li>
                            </ul>
                        </div>
                    </div>

                    @else
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted p-5 text-center">
                        <div>
                            <i class="bi bi-arrow-left-circle fs-1 text-muted d-block mb-2"></i>
                            <div class="fw-bold">Silakan Pilih Produk di Kolom Kiri</div>
                            <div class="small">Pilih salah satu menu untuk melihat dan mengelola komposisi resep bahan bakunya.</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Bahan Resep -->
    @if($selectedProduk)
    <div class="modal fade" id="tambahResepModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Tambah Bahan untuk {{ $selectedProduk->nama }}</h5>
                        <div class="text-muted small">Pilih bahan baku dan tentukan takaran per 1 porsi sajian</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.resep.detail.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="produk_id" value="{{ $selectedProduk->id }}">
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Pilih Bahan Baku <span class="text-danger">*</span></label>
                            <select class="form-select" name="bahan_baku_id" id="modalBahanSelect" required onchange="updateModalSatuanLabel()">
                                <option value="" disabled selected>Pilih bahan baku...</option>
                                @foreach($bahanBakus as $bb)
                                    <option value="{{ $bb->id }}" data-satuan="{{ $bb->satuan }}" data-harga="{{ $bb->harga_beli }}" data-stok="{{ $bb->stok }}">
                                        {{ $bb->nama }} (Stok: {{ floatval($bb->stok) }} {{ $bb->satuan }} • Rp {{ number_format($bb->harga_beli, 0, ',', '.') }}/{{ $bb->satuan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">
                                Takaran (Kebutuhan per 1 Porsi) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="qty" id="modalQtyInput" placeholder="Contoh: 15 atau 100" step="0.001" required min="0.001" oninput="calculateModalSubtotal()">
                                <span class="input-group-text bg-light text-muted fw-bold" id="modalSatuanSpan">Satuan</span>
                            </div>
                            <div class="form-text text-muted" id="modalSubtotalText">
                                Masukkan takaran untuk menghitung estimasi biaya bahan.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4" style="background-color: #8b211e;">Simpan Bahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function updateHeaderTime() {
        const now = new Date();
        document.getElementById('currentTimeHeader').innerText = now.toLocaleTimeString('id-ID', { hour12: false }) + ' WIB';
    }
    updateHeaderTime();
    setInterval(updateHeaderTime, 1000);

    function filterRecipeProducts() {
        const query = document.getElementById('recipeSearchInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('#recipeProductList .product-filter-item');
        items.forEach(item => {
            const name = item.getAttribute('data-name');
            if (name.includes(query)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function updateModalSatuanLabel() {
        const select = document.getElementById('modalBahanSelect');
        const selectedOption = select.options[select.selectedIndex];
        const satuan = selectedOption.getAttribute('data-satuan') || 'Satuan';
        document.getElementById('modalSatuanSpan').innerText = satuan;
        calculateModalSubtotal();
    }

    function calculateModalSubtotal() {
        const select = document.getElementById('modalBahanSelect');
        const qty = parseFloat(document.getElementById('modalQtyInput').value) || 0;
        const textEl = document.getElementById('modalSubtotalText');
        if (select.selectedIndex > 0 && qty > 0) {
            const selectedOption = select.options[select.selectedIndex];
            const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;
            const satuan = selectedOption.getAttribute('data-satuan') || '';
            const subtotal = Math.round(qty * harga);
            textEl.innerHTML = `Biaya bahan: <strong>Rp ${subtotal.toLocaleString('id-ID')}</strong> per porsi (${qty} ${satuan} × Rp ${harga.toLocaleString('id-ID')})`;
        } else {
            textEl.innerText = 'Masukkan takaran untuk menghitung estimasi biaya bahan.';
        }
    }
</script>
@endpush
@endsection
