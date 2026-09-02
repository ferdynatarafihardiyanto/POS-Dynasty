<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_stok', function (Blueprint $table) {
            // First drop existing foreign key before changing column to nullable
            $table->dropForeign(['produk_id']);
            $table->foreignId('produk_id')->nullable()->change();
            $table->foreign('produk_id')->references('id')->on('produk')->onDelete('cascade');
            
            $table->foreignId('bahan_baku_id')->nullable()->after('produk_id')->constrained('bahan_baku')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_stok', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_id']);
            $table->dropColumn('bahan_baku_id');
            
            // Revert produk_id to not nullable
            $table->dropForeign(['produk_id']);
            $table->foreignId('produk_id')->nullable(false)->change();
            $table->foreign('produk_id')->references('id')->on('produk')->onDelete('cascade');
        });
    }
};
