<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $fillable = ['kategori_id', 'nama', 'deskripsi', 'gambar', 'hpp', 'harga', 'stok', 'min_stok', 'aktif', 'tipe_produk'];

    protected $casts = [
        'aktif' => 'boolean',
        'hpp' => 'integer',
        'harga' => 'integer',
        'stok' => 'integer',
        'min_stok' => 'integer',
    ];

    protected $appends = ['gambar_url', 'tipe_label'];

    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            $url = null;
            if (str_starts_with($this->gambar, 'http')) {
                $url = $this->gambar;
            } elseif (str_starts_with($this->gambar, 'images/')) {
                $url = asset($this->gambar);
            } else {
                $basename = basename($this->gambar);
                if (file_exists(public_path('images/produk/' . $basename))) {
                    $url = asset('images/produk/' . $basename);
                } else {
                    $url = asset('storage/' . $this->gambar);
                }
            }

            // Tambahkan timestamp updated_at agar browser langsung memuat gambar baru tanpa tertahan cache lama
            if ($url && $this->updated_at) {
                $url .= (str_contains($url, '?') ? '&' : '?') . 'v=' . $this->updated_at->timestamp;
            }

            return $url;
        }

        return self::getDefaultImageForName($this->nama, $this->kategori->nama ?? '');
    }

    public static function getDefaultImageForName($nama, $kategori = '')
    {
        $name = strtolower($nama ?? '');
        if (str_contains($name, 'americano')) return asset('images/produk/americano.jpg');
        if (str_contains($name, 'cappuccino')) return asset('images/produk/cappuccino.jpg');
        if (str_contains($name, 'latte') && !str_contains($name, 'matcha')) return asset('images/produk/latte.jpg');
        if (str_contains($name, 'matcha')) return asset('images/produk/matcha_latte.jpg');
        if (str_contains($name, 'choc') || str_contains($name, 'coklat')) return asset('images/produk/chocolate.jpg');
        if (str_contains($name, 'sandwich')) return asset('images/produk/sandwich.jpg');
        if (str_contains($name, 'croissant')) return asset('images/produk/croissant.jpg');
        if (str_contains($name, 'fries') || str_contains($name, 'kentang')) return asset('images/produk/french_fries.jpg');

        $cat = strtolower($kategori ?? '');
        if (str_contains($cat, 'coffee') && !str_contains($cat, 'non')) return asset('images/produk/americano.jpg');
        if (str_contains($cat, 'non coffee')) return asset('images/produk/matcha_latte.jpg');
        if (str_contains($cat, 'food')) return asset('images/produk/sandwich.jpg');
        if (str_contains($cat, 'snack')) return asset('images/produk/french_fries.jpg');

        return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';
    }

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

    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'produk_modifier_group', 'produk_id', 'modifier_group_id');
    }

    public function bundleItems()
    {
        return $this->hasMany(ProdukBundleItem::class, 'bundle_id', 'id');
    }

    public function isBundle(): bool
    {
        return $this->tipe_produk === 'bundling';
    }

    public function isResep(): bool
    {
        if ($this->isBundle()) return false;
        if ($this->tipe_produk === 'resep') return true;
        return $this->resep && $this->resep->detail->isNotEmpty();
    }

    public function isSatuan(): bool
    {
        return !$this->isBundle() && !$this->isResep();
    }

    public function getTipeLabelAttribute(): string
    {
        if ($this->isBundle()) return 'Paket Bundling';
        if ($this->isResep()) return 'Item Resep';
        return 'Item Satuan';
    }

    /**
     * Hitung total HPP otomatis:
     * - Bundling: Jumlah dari setiap sub-produk × HPP sub-produk
     * - Resep: Jumlah dari setiap bahan baku × harga_beli bahan baku
     * - Satuan: Nilai hpp manual dari database
     */
    public function hitungHpp(): float
    {
        if ($this->isBundle()) {
            $total = 0;
            if ($this->relationLoaded('bundleItems') || $this->bundleItems()->exists()) {
                $items = $this->bundleItems()->with('item')->get();
                foreach ($items as $bItem) {
                    $sub = $bItem->item;
                    $total += ($sub ? $sub->hpp : 0) * $bItem->jumlah;
                }
            }
            return (float) $total;
        }

        // Cek resep
        $resep = $this->relationLoaded('resep') ? $this->resep : $this->resep()->with('detail.bahanBaku')->first();
        if ($resep && $resep->detail && $resep->detail->isNotEmpty()) {
            $total = 0;
            foreach ($resep->detail as $dtl) {
                $bb = $dtl->bahanBaku;
                $hargaBeli = $bb ? (float) $bb->harga_beli : 0;
                $total += (float) $dtl->jumlah * $hargaBeli;
            }
            return (float) $total;
        }

        return (float) ($this->attributes['hpp'] ?? 0);
    }

    /**
     * Hitung stok maksimum berdasarkan bahan baku resep (bottleneck rule)
     * atau sub-produk bundling.
     * Mengembalikan null jika produk adalah Item Satuan (stok dikelola mandiri).
     */
    public function hitungStokMaksimal(): ?int
    {
        if ($this->isBundle()) {
            $items = $this->relationLoaded('bundleItems') ? $this->bundleItems : $this->bundleItems()->with('item')->get();
            if ($items->isEmpty()) return 0;
            $minStok = PHP_INT_MAX;
            foreach ($items as $bItem) {
                $sub = $bItem->item;
                if (!$sub || !$sub->aktif || $bItem->jumlah <= 0) {
                    return 0;
                }
                $avail = (int) floor($sub->stok / $bItem->jumlah);
                if ($avail < $minStok) $minStok = $avail;
            }
            return max(0, $minStok === PHP_INT_MAX ? 0 : $minStok);
        }

        $resep = $this->relationLoaded('resep') ? $this->resep : $this->resep()->with('detail.bahanBaku')->first();
        if ($resep && $resep->detail && $resep->detail->isNotEmpty()) {
            $minStok = PHP_INT_MAX;
            foreach ($resep->detail as $dtl) {
                $bb = $dtl->bahanBaku;
                if (!$bb || !$bb->aktif || (float) $dtl->jumlah <= 0) {
                    return 0;
                }
                $avail = (int) floor((float) $bb->stok / (float) $dtl->jumlah);
                if ($avail < $minStok) $minStok = $avail;
            }
            return max(0, $minStok === PHP_INT_MAX ? 0 : $minStok);
        }

        return null;
    }

    /**
     * Sinkronisasi Stok dan HPP secara real-time ke tabel produk.
     */
    public function syncStokDanHpp(): void
    {
        $calculatedHpp = $this->hitungHpp();
        $calculatedStok = $this->hitungStokMaksimal();

        $dirty = false;
        if ($this->isResep() || $this->isBundle()) {
            $roundedHpp = (int) round($calculatedHpp);
            if ($this->hpp !== $roundedHpp) {
                $this->hpp = $roundedHpp;
                $dirty = true;
            }
            if ($calculatedStok !== null && $this->stok !== $calculatedStok) {
                $this->stok = $calculatedStok;
                $dirty = true;
            }
            $targetTipe = $this->isBundle() ? 'bundling' : 'resep';
            if ($this->tipe_produk !== $targetTipe) {
                $this->tipe_produk = $targetTipe;
                $dirty = true;
            }
        } else {
            if ($this->tipe_produk !== 'satuan' && $this->tipe_produk !== 'bundling') {
                $this->tipe_produk = 'satuan';
                $dirty = true;
            }
        }

        if ($dirty) {
            $this->saveQuietly();
        }
    }
}
