<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DetailPembelians;

class DetailPembelianSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'pembelian_id' => 1,
                'produk_id' => 1,
                'jumlah' => 10,
                'harga_beli' => 15000,
                'subtotal' => 150000,
            ],
            [
                'pembelian_id' => 2,
                'produk_id' => 2,
                'jumlah' => 20,
                'harga_beli' => 11000,
                'subtotal' => 220000,
            ],
            [
                'pembelian_id' => 3,
                'produk_id' => 3,
                'jumlah' => 12,
                'harga_beli' => 15000,
                'subtotal' => 180000,
            ],
            [
                'pembelian_id' => 4,
                'produk_id' => 4,
                'jumlah' => 6,
                'harga_beli' => 15000,
                'subtotal' => 90000,
            ],
            [
                'pembelian_id' => 5,
                'produk_id' => 5,
                'jumlah' => 25,
                'harga_beli' => 12000,
                'subtotal' => 300000,
            ],
        ];

        DetailPembelians::insert($data);
    }
}
