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
            Schema::create('tbl_penjualans', function (Blueprint $table) {
                $table->increments('penjualan_id');
                $table->dateTime('tanggal_penjualan');
                $table->decimal('total_harga', 10, 2);
                $table->decimal('diskon', 10, 2)->default(0);
                $table->unsignedInteger('pelanggan_id')->nullable();
                $table->unsignedInteger('user_id');
                $table->timestamps();

                $table->foreign('pelanggan_id')->references('pelanggan_id')->on('tbl_pelanggans')->onDelete('cascade');
                $table->foreign('user_id')->references('user_id')->on('tbl_users')->onDelete('cascade');
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('tbl_penjualans');
        }
    };
