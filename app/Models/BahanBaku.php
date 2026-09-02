<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';

    protected $fillable = [
        'nama',
        'harga_beli',
        'satuan',
        'stok',
        'stok_minimum',
        'aktif',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'stok' => 'decimal:3',
        'stok_minimum' => 'decimal:3',
        'aktif' => 'boolean',
    ];

    public function resepDetails()
    {
        return $this->hasMany(ResepDetail::class, 'bahan_baku_id', 'id');
    }
}
