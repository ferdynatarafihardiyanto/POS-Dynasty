@extends('layouts.admin')
@section('title', 'Tambah Meja')
@section('content')
<h2>Tambah Meja</h2>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.meja.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Nomor Meja</label>
                <input type="text" name="table_number" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Nama (Opsional)</label>
                <input type="text" name="name" class="form-control">
            </div>
            <div class="mb-3">
                <label>QR Token</label>
                <input type="text" name="qr_token" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button class="btn btn-success">Simpan</button>
        </form>
    </div>
</div>
@endsection
