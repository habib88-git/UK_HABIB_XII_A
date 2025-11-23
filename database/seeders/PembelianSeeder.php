<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pembelians;

class PembelianSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'supplier_id' => 1,
                'tanggal' => '2025-01-10 10:00:00',
                'total_harga' => 150000,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => 2,
                'tanggal' => '2025-01-15 14:30:00',
                'total_harga' => 220000,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => 1,
                'tanggal' => '2025-02-01 09:00:00',
                'total_harga' => 180000,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => 3,
                'tanggal' => '2025-02-20 11:15:00',
                'total_harga' => 90000,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'supplier_id' => 2,
                'tanggal' => '2025-03-05 08:45:00',
                'total_harga' => 300000,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Pembelians::insert($data);
    }
}
