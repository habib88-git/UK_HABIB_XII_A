<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Suppliers;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'nama_supplier' => 'PT Maju Jaya Abadi',
                'alamat'        => 'Jl. Merpati No. 12, Jakarta',
                'no_telp'       => '081234567890',
            ],
            [
                'nama_supplier' => 'CV Sumber Rezeki Makmur',
                'alamat'        => 'Jl. Kenanga No. 45, Bandung',
                'no_telp'       => '082198765432',
            ],
            [
                'nama_supplier' => 'PT Berkah Sentosa',
                'alamat'        => 'Jl. Mawar No. 7, Surabaya',
                'no_telp'       => '081355667788',
            ],
            [
                'nama_supplier' => 'Toko Sari Murni',
                'alamat'        => 'Jl. Anggrek No. 10, Semarang',
                'no_telp'       => '085712345678',
            ],
            [
                'nama_supplier' => 'UD Makmur Bersama',
                'alamat'        => 'Jl. Melati No. 99, Yogyakarta',
                'no_telp'       => '081234112233',
            ],
        ];

        foreach ($suppliers as $data) {
            Suppliers::create($data);
        }
    }
}
