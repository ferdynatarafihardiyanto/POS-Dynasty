@extends('layouts.admin')
@section('title', 'Tambah Modifier Group')
@section('content')
<h2>Tambah Modifier Group</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.modifier-groups.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nama Group (Misal: Sugar Level)</label>
                <input type="text" name="nama" value="{{ old('nama') }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Tipe</label>
                <select name="tipe" class="form-control">
                    <option value="single" {{ old('tipe') == 'single' ? 'selected' : '' }}>Single (Pilih 1)</option>
                    <option value="multiple" {{ old('tipe') == 'multiple' ? 'selected' : '' }}>Multiple (Bisa banyak)</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Wajib Diisi?</label>
                <select name="wajib_diisi" class="form-control">
                    <option value="1" {{ old('wajib_diisi') == '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ old('wajib_diisi') == '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Min Pilihan</label>
                <input type="number" name="min_pilihan" value="{{ old('min_pilihan', 0) }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Max Pilihan</label>
                <input type="number" name="max_pilihan" value="{{ old('max_pilihan', 1) }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Aktif</label>
                <select name="aktif" class="form-control">
                    <option value="1" {{ old('aktif') == '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ old('aktif') == '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <button class="btn btn-success">Simpan Group</button>
            <a href="{{ route('admin.modifier-groups.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
