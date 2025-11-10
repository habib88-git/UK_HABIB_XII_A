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
        Schema::table('tbl_produks', function (Blueprint $table) {
            // Tambah kolom supplier_id (non-nullable)
            $table->unsignedInteger('supplier_id')->after('satuan_id');

            // Tambah foreign key
            $table->foreign('supplier_id')
                ->references('supplier_id')
                ->on('tbl_suppliers')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_produks', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
