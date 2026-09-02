<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $fillable = ['kategori_id', 'nama', 'deskripsi', 'harga', 'stok', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
        'harga' => 'integer',
        'stok' => 'integer',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'produk_id', 'id');
    }

    public function riwayatStok()
    {
        return $this->hasMany(RiwayatStok::class, 'produk_id', 'id');
    }

    public function resep()
    {
        return $this->hasOne(Resep::class, 'produk_id', 'id');
    }
}
