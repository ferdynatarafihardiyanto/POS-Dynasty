<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Kategori;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            'Coffee',
            'Non Coffee',
            'Food',
            'Snack'
        ];

        foreach ($kategoris as $kategori) {
            Kategori::firstOrCreate([
                'nama' => $kategori
            ], [
                'deskripsi' => 'Kategori ' . $kategori,
                'aktif' => true
            ]);
        }
    }
}
