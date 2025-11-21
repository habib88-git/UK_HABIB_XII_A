<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            ['nama_kategori' => 'Makanan'],
            ['nama_kategori' => 'Minuman'],
            ['nama_kategori' => 'Bahan Pokok'],
            ['nama_kategori' => 'Snack'],
            ['nama_kategori' => 'Keperluan Rumah'],
        ];

        DB::table('tbl_kategoris')->insert($kategoris);
    }
}
