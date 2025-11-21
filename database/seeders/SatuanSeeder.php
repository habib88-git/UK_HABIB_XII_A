<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Satuans;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuans = [
            ['nama_satuan' => 'Pcs'],
            ['nama_satuan' => 'Box'],
            ['nama_satuan' => 'Pack'],
            ['nama_satuan' => 'Botol'],
            ['nama_satuan' => 'Dus'],
        ];

        foreach ($satuans as $data) {
            Satuans::create($data);
        }
    }
}
