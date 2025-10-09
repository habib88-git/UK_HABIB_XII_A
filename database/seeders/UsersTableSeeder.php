<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tbl_users')->insert([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'no_telp' => '123456789098',
            'alamat' => 'Jl. Kamcil',
            'sandi' => Hash::make('sandi'),
        ]);
    }
}
