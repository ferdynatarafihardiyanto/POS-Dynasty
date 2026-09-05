@extends('layouts.admin')
@section('title', 'Edit Kategori')
@section('content')
<h2>Edit Kategori</h2>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.kategori.update', $kategori->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Nama</label>
                <input type="text" name="nama" value="{{ $kategori->nama }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control">{{ $kategori->deskripsi }}</textarea>
            </div>
            <div class="mb-3">
                <label>Aktif</label>
                <select name="aktif" class="form-control">
                    <option value="1" {{ $kategori->aktif ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ !$kategori->aktif ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <button class="btn btn-success">Update</button>
        </form>
    </div>
</div>
@endsection
