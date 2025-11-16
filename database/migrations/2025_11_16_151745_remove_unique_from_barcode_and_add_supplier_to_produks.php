<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_produks', function (Blueprint $table) {
            // Drop unique constraint dari barcode (kalau ada)
            try {
                $table->dropUnique(['barcode']);
            } catch (\Exception $e) {
                // Kalau gak ada unique constraint, skip aja
            }

            // Tambah supplier_id HANYA kalau belum ada
            if (!Schema::hasColumn('tbl_produks', 'supplier_id')) {
                $table->unsignedInteger('supplier_id')->nullable()->after('satuan_id');
                $table->foreign('supplier_id')->references('supplier_id')->on('tbl_suppliers')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tbl_produks', function (Blueprint $table) {
            // Kembalikan unique constraint
            $table->unique('barcode');

            // Hapus supplier_id (kalau ada)
            if (Schema::hasColumn('tbl_produks', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });
    }
};
