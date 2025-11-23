<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua supplier id
        $supplierIds = DB::table('tbl_suppliers')->pluck('supplier_id')->toArray();

        // Data produk
        $produks = [
            [
                'kategori_id' => 1,
                'satuan_id' => 1,
                'nama_produk' => 'Air Mineral Botol 600ml',
                'barcode' => '899100110001'.rand(10,99),
                'stok' => 50,
                'harga_beli' => 2000,
                'harga_jual' => 3000,
                'kadaluwarsa' => Carbon::now()->addDays(90),
            ],
            [
                'kategori_id' => 2,
                'satuan_id' => 1,
                'nama_produk' => 'Roti Tawar Mini',
                'barcode' => '899200220002'.rand(10,99),
                'stok' => 30,
                'harga_beli' => 5000,
                'harga_jual' => 7000,
                'kadaluwarsa' => Carbon::now()->addDays(5),
            ],
            [
                'kategori_id' => 3,
                'satuan_id' => 1,
                'nama_produk' => 'Susu Kotak Coklat 250ml',
                'barcode' => '899300330003'.rand(10,99),
                'stok' => 40,
                'harga_beli' => 4000,
                'harga_jual' => 6000,
                'kadaluwarsa' => Carbon::now()->addDays(40),
            ],
            [
                'kategori_id' => 1,
                'satuan_id' => 1,
                'nama_produk' => 'Teh Botol 350ml',
                'barcode' => '899400440004'.rand(10,99),
                'stok' => 60,
                'harga_beli' => 3500,
                'harga_jual' => 5000,
                'kadaluwarsa' => Carbon::now()->addDays(60),
            ],
            [
                'kategori_id' => 4,
                'satuan_id' => 1,
                'nama_produk' => 'Mi Instan Ayam Bawang',
                'barcode' => '899500550005'.rand(10,99),
                'stok' => 100,
                'harga_beli' => 2000,
                'harga_jual' => 3000,
                'kadaluwarsa' => Carbon::now()->addDays(180),
            ],
        ];

        $finalData = [];

        foreach ($produks as $index => $produk) {
            $produk['supplier_id'] = $supplierIds[$index] ?? $supplierIds[0];
            $produk['created_at'] = now();
            $produk['updated_at'] = now();
            $finalData[] = $produk;
        }

        DB::table('tbl_produks')->insert($finalData);
    }
}
