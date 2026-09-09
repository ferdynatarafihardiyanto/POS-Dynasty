@extends('layouts.admin')
@section('title', 'Tambah Produk')
@section('content')
<h2>Tambah Produk</h2>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.produk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Kategori</label>
                <select name="kategori_id" required class="form-control">
                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Nama Produk</label>
                <input type="text" name="nama" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Foto Produk (Opsional)</label>
                <input type="file" name="gambar" accept="image/*" class="form-control">
            </div>
            <div class="mb-3">
                <label>Harga</label>
                <input type="number" name="harga" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Stok Awal</label>
                <input type="number" name="stok" value="0" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Aktif</label>
                <select name="aktif" class="form-control">
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
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
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="modifier_groups[]" value="{{ $mg->id }}" 
                                    id="mg_{{ $mg->id }}"
                                    {{ (is_array(old('modifier_groups')) && in_array($mg->id, old('modifier_groups'))) ? 'checked' : '' }}>
                                <label class="form-check-label" for="mg_{{ $mg->id }}">
                                    {{ $mg->nama }}
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

            <button class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
@endsection
