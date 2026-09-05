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
</style>
@endpush

@section('content')
<div class="pos-main d-flex flex-column h-100">
    <div class="d-flex align-items-center justify-content-between p-4 bg-white border-bottom">
        <div>
            <h4 class="mb-0 fw-bold text-dark">Resep Produk (BOM)</h4>
            <div class="text-muted small">Atur komposisi bahan baku untuk setiap produk jual</div>
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

    <div class="p-4 flex-grow-1 overflow-auto" style="background-color: #fcfcfc;">
        <div class="row g-4">
            <!-- Product List -->
            <div class="col-md-4">
                <div class="bg-white rounded-4 border shadow-sm p-3">
                    <h6 class="fw-bold mb-3">Pilih Produk</h6>
                    <div class="position-relative mb-3">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control rounded-3 ps-5 border py-2" style="font-size: 0.85rem;" placeholder="Cari menu...">
                    </div>
                    <div class="list-group list-group-flush border-top">
                        @forelse($produks as $p)
                        <a href="{{ route('admin.resep.index', ['produk_id' => $p->id]) }}" class="list-group-item list-group-item-action border-bottom py-3 {{ request('produk_id') == $p->id ? 'active bg-light text-dark fw-bold' : 'text-muted' }}" {!! request('produk_id') == $p->id ? 'style="border-left: 3px solid #8b211e;"' : '' !!}>
                            {{ $p->nama }}
                        </a>
                        @empty
                        <div class="p-3 text-center text-muted small">Belum ada produk</div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Recipe Details -->
            <div class="col-md-8">
                <div class="bg-white rounded-4 border shadow-sm p-4 h-100">
                    @php
                        $selectedProduk = request('produk_id') ? $produks->firstWhere('id', request('produk_id')) : null;
                    @endphp

                    @if($selectedProduk)
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">Resep: {{ $selectedProduk->nama }}</h5>
                            <div class="text-muted small">Produk ini akan memotong stok bahan baku berikut saat terjual</div>
                        </div>
                        <button class="btn btn-sm text-white fw-bold px-3 py-2 rounded-3" style="background-color: #8b211e;" data-bs-toggle="modal" data-bs-target="#tambahResepModal">
                            <i class="bi bi-plus-lg"></i> Tambah Bahan
                        </button>
                    </div>

                    <table class="table table-hover table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Takaran (Qty)</th>
                                <th>Satuan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($selectedProduk->resep && $selectedProduk->resep->detail->count() > 0)
                                @foreach($selectedProduk->resep->detail as $dtl)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $dtl->bahanBaku->nama }}</td>
                                    <td>{{ floatval($dtl->jumlah) }}</td>
                                    <td>{{ $dtl->bahanBaku->satuan }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.resep.detail.destroy', $dtl->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bahan ini dari resep?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada bahan baku di resep ini</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    @else
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                        Pilih produk di sebelah kiri untuk melihat resep
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
                        <div class="text-muted small">Pilih bahan baku dan tentukan takarannya</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form action="{{ route('admin.resep.detail.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="produk_id" value="{{ $selectedProduk->id }}">
                    <div class="modal-body px-4 pt-4 pb-2">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Pilih Bahan Baku <span class="text-danger">*</span></label>
                            <select class="form-select" name="bahan_baku_id" required>
                                <option value="" disabled selected>Pilih bahan...</option>
                                @foreach($bahanBakus as $bb)
                                    <option value="{{ $bb->id }}">{{ $bb->nama }} ({{ $bb->satuan }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Takaran (Qty) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="qty" placeholder="0" step="0.01" required min="0.01">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn bg-white rounded-3 fw-bold py-2 px-4 border text-dark" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn text-white rounded-3 fw-bold py-2 px-4" style="background-color: #8b211e;">Tambahkan</button>
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
</script>
@endpush
@endsection
