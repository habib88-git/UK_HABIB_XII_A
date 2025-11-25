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
        Schema::table('tbl_batch_produks', function (Blueprint $table) {
            // ✅ CEK dan HAPUS UNIQUE constraint pada barcode_batch
            // Laravel otomatis buat nama constraint seperti: tbl_batch_produks_barcode_batch_unique
            $table->dropUnique('tbl_batch_produks_barcode_batch_unique');

            // ✅ TIDAK PERLU BUAT INDEX LAGI karena sudah ada dari migration sebelumnya
            // Migration sebelumnya sudah ada: $table->index('barcode_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_batch_produks', function (Blueprint $table) {
            // ✅ KEMBALIKAN UNIQUE constraint (kalau rollback)
            $table->unique('barcode_batch');
        });
    }
};
