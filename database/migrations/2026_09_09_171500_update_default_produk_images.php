<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Copy images from public/images/produk to storage/app/public/produk if needed
        $sourceDir = public_path('images/produk');
        $targetDir = storage_path('app/public/produk');
        $publicStorageDir = public_path('storage/produk');

        if (File::isDirectory($sourceDir)) {
            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0777, true, true);
            }
            if (!File::isDirectory($publicStorageDir)) {
                File::makeDirectory($publicStorageDir, 0777, true, true);
            }
            $files = File::files($sourceDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                File::copy($file->getPathname(), $targetDir . '/' . $filename);
                if (File::isDirectory($publicStorageDir) && !is_link($publicStorageDir)) {
                    File::copy($file->getPathname(), $publicStorageDir . '/' . $filename);
                }
            }
        }

        $imageMappings = [
            'Americano' => 'produk/americano.jpg',
            'Latte' => 'produk/latte.jpg',
            'Cappuccino' => 'produk/cappuccino.jpg',
            'Matcha Latte' => 'produk/matcha_latte.jpg',
            'Chocolate' => 'produk/chocolate.jpg',
            'Sandwich' => 'produk/sandwich.jpg',
            'Croissant' => 'produk/croissant.jpg',
            'French Fries' => 'produk/french_fries.jpg',
        ];

        foreach ($imageMappings as $nama => $imagePath) {
            DB::table('produk')
                ->where('nama', $nama)
                ->where(function ($q) {
                    $q->whereNull('gambar')
                      ->orWhere('gambar', '')
                      ->orWhere('gambar', '-');
                })
                ->update(['gambar' => $imagePath]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
