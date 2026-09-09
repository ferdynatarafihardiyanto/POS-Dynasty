<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $fillable = ['nomor_pesanan', 'meja_id', 'status', 'total_harga', 'catatan'];

    protected $appends = ['status_pembayaran', 'nama_pelanggan'];

    public function getNamaPelangganAttribute()
    {
        if (!empty($this->attributes['nama_pelanggan'] ?? null)) {
            return $this->attributes['nama_pelanggan'];
        }

        if (!empty($this->catatan)) {
            if (preg_match('/^Pemesan:\s*([^|]+)(?:\s*\|\s*(.*))?$/i', $this->catatan, $m)) {
                return trim($m[1]);
            }
            if (!str_starts_with($this->catatan, 'Pesanan Meja')) {
                if (str_contains($this->catatan, ' - ')) {
                    $parts = explode(' - ', $this->catatan, 2);
                    return trim($parts[0]);
                }
                return trim($this->catatan);
            }
        }

        return 'Pelanggan Meja ' . ($this->meja ? ($this->meja->table_number ?? $this->meja->id) : '');
    }

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
