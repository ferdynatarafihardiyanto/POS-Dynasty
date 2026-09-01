<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $fillable = ['pesanan_id', 'nomor_transaksi', 'metode_pembayaran', 'jumlah_bayar', 'kembalian', 'status', 'dibayar_pada'];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id', 'id');
    }
}
