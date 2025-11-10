<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravolt\Indonesia\Models\{City, District, Village};

// Route default Sanctum
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ambil kota berdasarkan kode provinsi
Route::get('/cities/{province_code}', function ($province_code) {
    return City::where('province_code', $province_code)->pluck('name', 'code');
});

// Ambil kecamatan berdasarkan kode kota
Route::get('/districts/{city_code}', function ($city_code) {
    return District::where('city_code', $city_code)->pluck('name', 'code');
});

// Ambil kelurahan berdasarkan kode kecamatan
Route::get('/villages/{district_code}', function ($district_code) {
    return Village::where('district_code', $district_code)->pluck('name', 'code');
});
