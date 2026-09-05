@extends('layouts.admin')
@section('title', 'Tambah Kategori')
@section('content')
<h2>Tambah Kategori</h2>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.kategori.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nama</label>
                <input type="text" name="nama" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Aktif</label>
                <select name="aktif" class="form-control">
                    <option value="1">Ya</option>
                    <option value="0">Tidak</option>
                </select>
            </div>
            <button class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
@endsection
