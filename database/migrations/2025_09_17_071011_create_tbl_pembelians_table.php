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
        Schema::create('tbl_pembelians', function (Blueprint $table) {
            $table->increments('pembelian_id');
            $table->dateTime('tanggal');
            $table->decimal('total_harga', 12, 2);
            $table->unsignedInteger('supplier_id')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('supplier_id')->references('supplier_id')->on('tbl_suppliers')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('tbl_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pembelians');
    }
};
