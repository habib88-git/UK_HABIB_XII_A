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
        Schema::create('tbl_users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->string('no_telp', 15);
            $table->text('alamat')->nullable();
            $table->string('sandi');
            $table->enum('role', ['admin', 'kasir'])->default('kasir');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_users');
    }
};
