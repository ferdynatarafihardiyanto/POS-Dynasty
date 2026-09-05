@extends('layouts.admin')
@section('title', 'Edit Meja')
@section('content')
<h2>Edit Meja</h2>
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <form action="{{ route('admin.meja.update', $meja->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label>Nomor Meja</label>
                <input type="text" name="table_number" value="{{ $meja->table_number }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Nama (Opsional)</label>
                <input type="text" name="name" value="{{ $meja->name }}" class="form-control">
            </div>
            <div class="mb-3">
                <label>QR Token</label>
                <input type="text" name="qr_token" value="{{ $meja->qr_token }}" required class="form-control">
            </div>
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active" {{ $meja->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $meja->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button class="btn btn-success">Update</button>
        </form>
    </div>
</div>
@endsection
