<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tbl_users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'no_telp' => '081234567890',
                'alamat' => 'Jl. Merdeka No. 1',
                'sandi' => Hash::make('admin123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kasir',
                'email' => 'kasir@gmail.com',
                'no_telp' => '089876543210',
                'alamat' => 'Jl. Sudirman No. 2',
                'sandi' => Hash::make('kasir123'),
                'role' => 'kasir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
