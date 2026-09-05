<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuan;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['nama' => 'Gram', 'keterangan' => 'Satuan berat bubuk / kopi'],
            ['nama' => 'Kg', 'keterangan' => 'Satuan berat besar (Kilogram)'],
            ['nama' => 'ml', 'keterangan' => 'Satuan volume cairan (Mililiter)'],
            ['nama' => 'Liter', 'keterangan' => 'Satuan volume cairan besar'],
            ['nama' => 'Pcs', 'keterangan' => 'Pieces / satuan per buah'],
            ['nama' => 'Cup', 'keterangan' => 'Satuan cangkir minuman'],
            ['nama' => 'Botol', 'keterangan' => 'Satuan kemasan botol'],
            ['nama' => 'Porsi', 'keterangan' => 'Satuan takaran sajian makanan'],
            ['nama' => 'Sachet', 'keterangan' => 'Satuan kemasan sachet'],
            ['nama' => 'Box', 'keterangan' => 'Satuan kardus / kotak'],
        ];

        foreach ($defaults as $d) {
            Satuan::firstOrCreate(['nama' => $d['nama']], $d);
        }
    }
}
