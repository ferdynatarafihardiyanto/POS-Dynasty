<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\CafeTable;
use Illuminate\Support\Str;

class CafeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $tableNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
            CafeTable::firstOrCreate(
                ['table_number' => $tableNumber],
                [
                    'qr_token' => Str::random(10),
                    'status' => 'active'
                ]
            );
        }
    }
}
