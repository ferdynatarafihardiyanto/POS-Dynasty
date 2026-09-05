<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = [
        'nama',
        'tipe',
        'wajib_diisi',
        'min_pilihan',
        'max_pilihan',
        'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'wajib_diisi' => 'boolean'
    ];

    public function options()
    {
        return $this->hasMany(ModifierOption::class);
    }

    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'produk_modifier_group', 'modifier_group_id', 'produk_id');
    }
}
