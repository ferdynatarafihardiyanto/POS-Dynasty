<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter tipe_produk column to support 'satuan', 'resep', 'bundling', 'standar'
        DB::statement("ALTER TABLE produk MODIFY COLUMN tipe_produk VARCHAR(30) NOT NULL DEFAULT 'satuan'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE produk MODIFY COLUMN tipe_produk ENUM('standar', 'bundling') NOT NULL DEFAULT 'standar'");
    }
};
