@extends('layouts.admin')
@section('title', 'Edit Produk')
@section('content')
<h2>Edit Produk</h2>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Kategori</label>
                <select name="kategori_id" required class="form-control">
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ $produk->kategori_id == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Tipe Produk</label>
                <select name="tipe_produk" class="form-control" x-data="{tipe: '{{ $produk->tipe_produk }}'}" x-model="tipe" @change="$dispatch('tipe-changed', tipe)">
                    <option value="standar">Standar (Punya Stok & Resep)</option>
                    <option value="bundling">Paket Bundling (Gabungan Produk Standar)</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Nama Produk</label>
                <input type="text" name="nama" value="{{ $produk->nama }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control">{{ $produk->deskripsi }}</textarea>
            </div>
            <div class="mb-3">
                <label>Foto Produk (Opsional)</label>
                @if($produk->gambar)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $produk->gambar) }}" alt="{{ $produk->nama }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                @endif
                <input type="file" name="gambar" accept="image/*" class="form-control">
            </div>
            <div class="mb-3">
                <label>Harga</label>
                <input type="number" name="harga" value="{{ $produk->harga }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Aktif</label>
                <select name="aktif" class="form-control">
                    <option value="1" {{ $produk->aktif ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ !$produk->aktif ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="fw-bold">Modifier Groups (Varian)</label>
                @if($modifierGroups->isEmpty())
                    <div class="alert alert-light border small text-muted">
                        Belum ada Modifier Group aktif. 
                        Buat melalui menu Varian / Topping terlebih dahulu.
                    </div>
                @else
                    <div class="border rounded p-3 bg-light">
                        @foreach($modifierGroups as $mg)
                            @php
                                $isChecked = (is_array(old('modifier_groups')) && in_array($mg->id, old('modifier_groups'))) 
                                    || (!session()->has('errors') && $produk->modifierGroups->contains('id', $mg->id));
                                $isInactive = !$mg->aktif;
                            @endphp
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="modifier_groups[]" value="{{ $mg->id }}" 
                                    id="mg_{{ $mg->id }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                    {{ $isInactive ? 'disabled' : '' }}>
                                <label class="form-check-label {{ $isInactive ? 'text-muted' : '' }}" for="mg_{{ $mg->id }}">
                                    {{ $mg->nama }} 
                                    @if($isInactive)
                                        <span class="badge bg-secondary ms-1">Inactive</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                @error('modifier_groups')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('modifier_groups.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- Bagian Bundle Items -->
            <div class="mb-4" x-data="{ tipe_produk: '{{ $produk->tipe_produk }}' }" @tipe-changed.window="tipe_produk = $event.detail" x-show="tipe_produk === 'bundling'" style="display: none;">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 fs-6"><i class="bi bi-box-seam me-2"></i>Isi Paket Bundling</h5>
                    </div>
                    <div class="card-body bg-light">
                        <p class="text-muted small mb-3">Pilih produk standar yang masuk ke dalam paket ini. Stok bahan baku akan otomatis terpotong dari produk-produk ini saat paket terjual.</p>
                        <div class="table-responsive">
                            <table class="table table-bordered bg-white">
                                <thead>
                                    <tr>
                                        <th width="60%">Produk Standar</th>
                                        <th width="40%">Jumlah dlm Paket</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Cache existing bundle items mapping
                                        $existingItems = $produk->bundleItems->pluck('jumlah', 'item_id')->toArray();
                                    @endphp
                                    @foreach($allProduks as $p)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($p->gambar)
                                                    <img src="{{ $p->gambar_url }}" class="rounded me-2" style="width:30px; height:30px; object-fit:cover;">
                                                @endif
                                                <span>{{ $p->nama }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="bundle_items[{{ $p->id }}]" class="form-control form-control-sm" value="{{ $existingItems[$p->id] ?? 0 }}" min="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-success">Update</button>
        </form>
    </div>
</div>
@endsection
