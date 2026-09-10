<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $fillable = ['kategori_id', 'nama', 'deskripsi', 'gambar', 'hpp', 'harga', 'stok', 'aktif', 'tipe_produk'];

    protected $casts = [
        'aktif' => 'boolean',
        'harga' => 'integer',
        'stok' => 'integer',
    ];

    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute()
    {
        if ($this->gambar) {
            if (str_starts_with($this->gambar, 'http')) {
                return $this->gambar;
            }
            if (str_starts_with($this->gambar, 'images/')) {
                return asset($this->gambar);
            }
            $basename = basename($this->gambar);
            if (file_exists(public_path('images/produk/' . $basename))) {
                return asset('images/produk/' . $basename);
            }
            return asset('storage/' . $this->gambar);
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

    public function isBundle()
    {
        return $this->tipe_produk === 'bundling';
    }
}
