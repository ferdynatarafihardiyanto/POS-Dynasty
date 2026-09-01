<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Produk;
use App\Models\Kategori;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriCoffee = Kategori::where('nama', 'Coffee')->first();
        $kategoriNonCoffee = Kategori::where('nama', 'Non Coffee')->first();
        $kategoriFood = Kategori::where('nama', 'Food')->first();
        $kategoriSnack = Kategori::where('nama', 'Snack')->first();

        if (!$kategoriCoffee) return;

        $produks = [
            ['kategori_id' => $kategoriCoffee->id, 'nama' => 'Americano', 'harga' => 18000, 'stok' => 20, 'aktif' => true, 'deskripsi' => 'Espresso dengan air'],
            ['kategori_id' => $kategoriCoffee->id, 'nama' => 'Latte', 'harga' => 22000, 'stok' => 15, 'aktif' => true, 'deskripsi' => 'Espresso dengan susu'],
            ['kategori_id' => $kategoriCoffee->id, 'nama' => 'Cappuccino', 'harga' => 24000, 'stok' => 10, 'aktif' => true, 'deskripsi' => 'Espresso dengan foam susu tebal'],
            ['kategori_id' => $kategoriNonCoffee->id, 'nama' => 'Matcha Latte', 'harga' => 25000, 'stok' => 10, 'aktif' => true, 'deskripsi' => 'Matcha dengan susu'],
            ['kategori_id' => $kategoriNonCoffee->id, 'nama' => 'Chocolate', 'harga' => 20000, 'stok' => 20, 'aktif' => true, 'deskripsi' => 'Coklat premium'],
            ['kategori_id' => $kategoriFood->id, 'nama' => 'Sandwich', 'harga' => 30000, 'stok' => 5, 'aktif' => true, 'deskripsi' => 'Roti isi daging dan sayur'],
            ['kategori_id' => $kategoriFood->id, 'nama' => 'Croissant', 'harga' => 25000, 'stok' => 8, 'aktif' => true, 'deskripsi' => 'Roti prancis renyah'],
            ['kategori_id' => $kategoriSnack->id, 'nama' => 'French Fries', 'harga' => 15000, 'stok' => 30, 'aktif' => true, 'deskripsi' => 'Kentang goreng renyah'],
        ];

        foreach ($produks as $produk) {
            Produk::firstOrCreate([
                'nama' => $produk['nama']
            ], $produk);
        }
    }
}
