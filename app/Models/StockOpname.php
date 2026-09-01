<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $table = 'stock_opname';
    protected $fillable = ['nomor_opname', 'tanggal', 'status', 'keterangan'];

    public function detail()
    {
        return $this->hasMany(StockOpnameDetail::class, 'stock_opname_id', 'id');
    }
}
