<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $fillable = [
        'nomor_pesanan',
        'meja_id',
        'status',
        'total_harga',
        'catatan',
        'bukti_pembayaran',
        'metode_pembayaran_customer',
        'waktu_upload_bukti',
        'catatan_tolak_bukti',
    ];

    protected $casts = [
        'waktu_upload_bukti' => 'datetime',
    ];

    protected $appends = ['status_pembayaran', 'nama_pelanggan', 'bukti_pembayaran_url'];

    public function getBuktiPembayaranUrlAttribute()
    {
        if (empty($this->bukti_pembayaran)) {
            return null;
        }

        $path = $this->bukti_pembayaran;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            return '/' . $cleanPath;
        }

        return '/storage/' . $cleanPath;
    }

    /**
     * Memperbarui nama pemesan di dalam kolom catatan secara konsisten.
     */
    public function syncCustomerNameInCatatan(string $namaPelanggan): void
    {
        $namaPelanggan = trim($namaPelanggan);
        if ($namaPelanggan === '') {
            return;
        }

        $currentCatatan = $this->catatan ?? '';
        if (preg_match('/^Pemesan:\s*([^|]+)(?:\s*\|\s*(.*))?$/i', $currentCatatan, $matches)) {
            $extraNote = isset($matches[2]) ? trim($matches[2]) : '';
            $this->catatan = "Pemesan: {$namaPelanggan}" . ($extraNote ? " | {$extraNote}" : "");
        } elseif (empty($currentCatatan) || str_starts_with($currentCatatan, 'Pesanan Meja')) {
            $this->catatan = "Pemesan: {$namaPelanggan}";
        } elseif (!str_contains($currentCatatan, $namaPelanggan)) {
            $this->catatan = "Pemesan: {$namaPelanggan} | {$currentCatatan}";
        }
    }

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

        if ($this->status === 'menunggu_konfirmasi') {
            return 'menunggu_konfirmasi';
        }

        return 'menunggu_pembayaran';
    }

    /**
     * Ekstrak catatan khusus pelanggan di luar prefix nama pemesan.
     */
    public function getCatatanKhususAttribute(): string
    {
        $rawCatatan = $this->catatan;
        if (empty($rawCatatan)) {
            return '';
        }

        if (preg_match('/^Pemesan:\s*[^|]+(?:\s*\|\s*(.*))?$/i', $rawCatatan, $m)) {
            return isset($m[1]) ? trim($m[1]) : '';
        }

        if (!str_starts_with($rawCatatan, 'Pesanan Meja') && str_contains($rawCatatan, ' - ')) {
            $parts = explode(' - ', $rawCatatan, 2);
            return trim($parts[1]);
        }

        return '';
    }

    /**
     * Format data pesanan untuk endpoint daftar pesanan aktif POS Kasir.
     */
    public function toPosPayload(): array
    {
        $adminBuktiUrl = null;
        if (!empty($this->bukti_pembayaran)) {
            $ver = $this->waktu_upload_bukti ? $this->waktu_upload_bukti->timestamp : time();
            $adminBuktiUrl = url('/admin/pos/pesanan/' . $this->id . '/bukti') . '?v=' . $ver;
        }

        return [
            'id' => $this->id,
            'nomor_pesanan' => $this->nomor_pesanan,
            'meja_id' => $this->meja_id,
            'meja_nomor' => $this->meja ? ($this->meja->table_number ? str_pad($this->meja->table_number, 2, '0', STR_PAD_LEFT) : $this->meja->id) : '-',
            'meja_nama' => $this->meja ? ($this->meja->name ?? ('Meja ' . $this->meja->table_number)) : 'Meja Umum',
            'nama_pelanggan' => $this->nama_pelanggan,
            'catatan_khusus' => $this->catatan_khusus,
            'status' => $this->status,
            'status_pembayaran' => $this->status_pembayaran,
            'metode_pembayaran' => $this->pembayaran ? $this->pembayaran->metode_pembayaran : null,
            'metode_pembayaran_customer' => $this->metode_pembayaran_customer,
            'bukti_pembayaran' => $this->bukti_pembayaran,
            'bukti_pembayaran_url' => $this->bukti_pembayaran_url,
            'admin_bukti_url' => $adminBuktiUrl,
            'waktu_upload_bukti' => $this->waktu_upload_bukti ? $this->waktu_upload_bukti->timezone('Asia/Jakarta')->format('H:i:s') : null,
            'waktu_upload_bukti_iso' => $this->waktu_upload_bukti ? $this->waktu_upload_bukti->toISOString() : null,
            'catatan_tolak_bukti' => $this->catatan_tolak_bukti,
            'total_harga' => $this->total_harga,
            'catatan' => $this->catatan,
            'waktu' => $this->created_at ? $this->created_at->timezone('Asia/Jakarta')->format('H:i') : '-',
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'items' => $this->detailPesanan->map(function ($d) {
                return [
                    'id' => $d->id,
                    'produk_id' => $d->produk_id,
                    'nama_produk' => $d->nama_produk,
                    'harga' => $d->harga,
                    'jumlah' => $d->jumlah,
                    'subtotal' => $d->subtotal,
                    'catatan' => $d->catatan,
                    'modifiers' => $d->modifiers_snapshot ? json_decode($d->modifiers_snapshot, true) : []
                ];
            })
        ];
    }
}
