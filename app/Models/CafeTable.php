<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    protected $fillable = ['table_number', 'name', 'qr_token', 'status'];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'meja_id', 'id');
    }
}
