<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukBundleItem extends Model
{
    use HasFactory;

    protected $table = 'produk_bundle_items';

    protected $fillable = [
        'bundle_id',
        'item_id',
        'jumlah',
    ];

    public function bundle()
    {
        return $this->belongsTo(Produk::class, 'bundle_id');
    }

    public function item()
    {
        return $this->belongsTo(Produk::class, 'item_id');
    }
}
