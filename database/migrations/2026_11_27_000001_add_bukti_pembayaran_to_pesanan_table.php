<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('pesanan', 'metode_pembayaran_customer')) {
                $table->string('metode_pembayaran_customer', 30)->nullable()->after('bukti_pembayaran');
            }
            if (!Schema::hasColumn('pesanan', 'waktu_upload_bukti')) {
                $table->timestamp('waktu_upload_bukti')->nullable()->after('metode_pembayaran_customer');
            }
            if (!Schema::hasColumn('pesanan', 'catatan_tolak_bukti')) {
                $table->string('catatan_tolak_bukti')->nullable()->after('waktu_upload_bukti');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('pesanan', 'bukti_pembayaran') ? 'bukti_pembayaran' : null,
                Schema::hasColumn('pesanan', 'metode_pembayaran_customer') ? 'metode_pembayaran_customer' : null,
                Schema::hasColumn('pesanan', 'waktu_upload_bukti') ? 'waktu_upload_bukti' : null,
                Schema::hasColumn('pesanan', 'catatan_tolak_bukti') ? 'catatan_tolak_bukti' : null,
            ]);

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
