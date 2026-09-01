<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatStok extends Model
{
    protected $table = 'riwayat_stok';
    protected $fillable = ['produk_id', 'jenis', 'jumlah', 'stok_sebelum', 'stok_sesudah', 'referensi', 'keterangan'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id');
    }
}
