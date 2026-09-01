<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    protected $table = 'stock_opname_detail';
    protected $fillable = ['stock_opname_id', 'produk_id', 'stok_sistem', 'stok_fisik', 'selisih', 'keterangan'];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id', 'id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id');
    }
}
