<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pelanggans;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class PelangganSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggans = [
            [
                'nama_pelanggan' => 'Agus Pratama',
                'alamat' => 'Jalan Mawar No. 12',
                'nomor_telepon' => '081234567890',
            ],
            [
                'nama_pelanggan' => 'Budi Santoso',
                'alamat' => 'Jalan Kenanga No. 23',
                'nomor_telepon' => '081234567891',
            ],
            [
                'nama_pelanggan' => 'Citra Lestari',
                'alamat' => 'Jalan Anggrek No. 56',
                'nomor_telepon' => '081234567892',
            ],
            [
                'nama_pelanggan' => 'Dewi Rahayu',
                'alamat' => 'Jalan Melati No. 78',
                'nomor_telepon' => '081234567893',
            ],
            [
                'nama_pelanggan' => 'Eko Saputra',
                'alamat' => 'Jalan Kamboja No. 90',
                'nomor_telepon' => '081234567894',
            ],
        ];

        foreach ($pelanggans as $data) {
            // Ambil wilayah random
            $province  = Province::inRandomOrder()->first();
            $city      = City::where('province_code', $province->code)->inRandomOrder()->first();
            $district  = District::where('city_code', $city->code)->inRandomOrder()->first();
            $village   = Village::where('district_code', $district->code)->inRandomOrder()->first();

            Pelanggans::create([
                'nama_pelanggan' => $data['nama_pelanggan'],
                'alamat' => $data['alamat'],
                'nomor_telepon' => $data['nomor_telepon'],
                'province_id' => $province->code,
                'city_id' => $city->code,
                'district_id' => $district->code,
                'village_id' => $village->code,
            ]);
        }
    }
}
