<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $fillable = ['nomor_pesanan', 'meja_id', 'status', 'total_harga', 'catatan'];

    protected $appends = ['status_pembayaran'];

    public function meja()
    {
        return $this->belongsTo(CafeTable::class, 'meja_id', 'id');
    }

    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id', 'id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'pesanan_id', 'id');
    }

    public function getStatusPembayaranAttribute()
    {
        if ($this->relationLoaded('pembayaran')) {
            if ($this->pembayaran !== null) {
                return 'dibayar';
            }
        } elseif ($this->pembayaran()->exists()) {
            return 'dibayar';
        }

        if (in_array($this->status, ['dibayar', 'disajikan', 'selesai'])) {
            return 'dibayar';
        }

        return 'menunggu_pembayaran';
    }
}
