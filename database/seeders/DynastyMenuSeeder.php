<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use Illuminate\Support\Facades\DB;

class DynastyMenuSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            // 1. Kategori
            $kategoriKopi = Kategori::firstOrCreate(['nama' => 'Kopi']);
            $kategoriNonKopi = Kategori::firstOrCreate(['nama' => 'Non-Kopi']);
            $kategoriMilkBased = Kategori::firstOrCreate(['nama' => 'Milk Based']);
            $kategoriMakanan = Kategori::firstOrCreate(['nama' => 'Makanan']);
            $kategoriMie = Kategori::firstOrCreate(['nama' => 'Mie']);
            $kategoriRotiBakar = Kategori::firstOrCreate(['nama' => 'Roti Bakar']);

            // 2. Modifier Groups
            // Suhu untuk Kopi dan Non-Kopi
            $modSuhu = ModifierGroup::firstOrCreate(
                ['nama' => 'Varian Suhu'],
                ['tipe' => 'single', 'wajib_diisi' => true]
            );
            $optPanas = ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modSuhu->id, 'nama' => 'Panas'],
                ['harga_tambahan' => 0]
            );
            $optDingin = ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modSuhu->id, 'nama' => 'Dingin'],
                ['harga_tambahan' => 2000] // Default +2k
            );

            $modSuhuSama = ModifierGroup::firstOrCreate(
                ['nama' => 'Varian Suhu (Harga Sama)'],
                ['tipe' => 'single', 'wajib_diisi' => true]
            );
            ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modSuhuSama->id, 'nama' => 'Panas'],
                ['harga_tambahan' => 0]
            );
            ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modSuhuSama->id, 'nama' => 'Dingin'],
                ['harga_tambahan' => 0]
            );

            // Tambahan Mie
            $modTambahanMie = ModifierGroup::firstOrCreate(
                ['nama' => 'Tambahan Mie'],
                ['tipe' => 'multiple', 'wajib_diisi' => false]
            );
            ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modTambahanMie->id, 'nama' => 'Telur'],
                ['harga_tambahan' => 3000]
            );
            ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modTambahanMie->id, 'nama' => 'Nasi'],
                ['harga_tambahan' => 4000]
            );

            // Tambahan Roti Bakar
            $modTambahanRoti = ModifierGroup::firstOrCreate(
                ['nama' => 'Tambahan Roti Bakar'],
                ['tipe' => 'multiple', 'wajib_diisi' => false]
            );
            ModifierOption::firstOrCreate(
                ['modifier_group_id' => $modTambahanRoti->id, 'nama' => 'Mix'],
                ['harga_tambahan' => 2000]
            );

            // Helper function to create product and attach modifiers
            $createProduk = function ($nama, $harga, $kategori, $modifiers = []) {
                $produk = Produk::firstOrCreate(
                    ['nama' => $nama],
                    [
                        'kategori_id' => $kategori->id,
                        'harga' => $harga,
                        'stok' => 100, // default stok
                        'hpp' => 0,
                    ]
                );
                
                // Only sync if modifiers provided to prevent detaching if already synced
                if (!empty($modifiers)) {
                    $produk->modifierGroups()->syncWithoutDetaching(collect($modifiers)->pluck('id'));
                }
            };

            // SEED KOPI
            // Kopi - Beda Harga Panas Dingin
            $createProduk('Americano', 8000, $kategoriKopi, [$modSuhu]);
            $createProduk('Caffe Latte', 10000, $kategoriKopi, [$modSuhu]);
            
            // Kopi - Sama Harga Panas Dingin
            $createProduk('V60', 20000, $kategoriKopi, [$modSuhuSama]);
            
            // Kopi - Hanya Panas
            $createProduk('Kopi Butter', 12000, $kategoriKopi);
            
            // Kopi - Hanya Dingin
            $createProduk('Kopi Dinasty', 12000, $kategoriKopi);
            $createProduk('Kopi Milo', 12000, $kategoriKopi);
            $createProduk('Butterscotch Latte', 14000, $kategoriKopi);
            $createProduk('Aren Latte', 12000, $kategoriKopi);
            $createProduk('Banana Latte', 14000, $kategoriKopi);

            // SEED NON-KOPI
            $createProduk('Red Velvet', 10000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Matcha', 10000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Taro', 12000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Choco', 14000, $kategoriNonKopi);
            $createProduk('Lemon Tea', 10000, $kategoriNonKopi);
            $createProduk('Lychee Tea', 12000, $kategoriNonKopi);
            $createProduk('Peach / Mixed Fruit', 12000, $kategoriNonKopi);
            $createProduk('Air Mineral', 5000, $kategoriNonKopi);
            
            // Tea Series
            $createProduk('Tea (Lychee)', 10000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Tea (Strawberry)', 10000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Tea (Lemonade)', 10000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Tea (Mango)', 10000, $kategoriNonKopi, [$modSuhu]);
            $createProduk('Tea (Watermelon)', 10000, $kategoriNonKopi, [$modSuhu]);

            // SEED MILK BASED (Hanya Dingin)
            $createProduk('Butterscotch Milk Shake', 14000, $kategoriMilkBased);
            $createProduk('Strawberry Milk Shake', 14000, $kategoriMilkBased);
            $createProduk('Watermelon Milk Shake', 14000, $kategoriMilkBased);
            $createProduk('Banana Milk Shake', 14000, $kategoriMilkBased);
            $createProduk('Cookies & Cream', 16000, $kategoriMilkBased);
            $createProduk('Fresh Milk Regal', 16000, $kategoriMilkBased);

            // SEED MAKANAN (Asumsi Harga Default 15k, bisa diedit admin nanti)
            $makananItems = [
                'Chicken Katsu', 'Nasi Telur Dinasty', 'Spaghetti Bolognese',
                'Spaghetti Carbonara', 'Spaghetti Aglio Olio', 'Cireng Asik',
                'French Fries', 'Dinasty Platter'
            ];
            foreach ($makananItems as $makanan) {
                $createProduk($makanan, 15000, $kategoriMakanan);
            }

            // SEED MIE
            $createProduk('Mie Dinasty', 10000, $kategoriMie, [$modTambahanMie]);
            $createProduk('Mie Dinasty Spesial', 15000, $kategoriMie, [$modTambahanMie]);
            $createProduk('Mie Creamy Kari Spesial', 15000, $kategoriMie, [$modTambahanMie]);
            $createProduk('Mie Goreng Spesial', 15000, $kategoriMie, [$modTambahanMie]);

            // SEED ROTI BAKAR
            $rotiItems = [
                'Strawberry' => 12000,
                'Coklat' => 12000,
                'Peanut' => 12000,
                'Keju' => 12000,
                'Tiramisu' => 12000,
                'Coklat Keju' => 14000
            ];
            foreach ($rotiItems as $nama => $harga) {
                $createProduk("Roti Bakar $nama", $harga, $kategoriRotiBakar, [$modTambahanRoti]);
            }
        });
    }
}
