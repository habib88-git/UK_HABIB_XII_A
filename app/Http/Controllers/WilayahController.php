<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class WilayahController extends Controller
{
    // Ambil kota berdasarkan province code (kode provinsi)
    public function getCities($province_code)
    {
        // pluck(name, code) => key = code, value = name
        $cities = City::where('province_code', $province_code)->pluck('name', 'code');
        return response()->json($cities);
    }

    // Ambil kecamatan berdasarkan city code (kode kota)
    public function getDistricts($city_code)
    {
        $districts = District::where('city_code', $city_code)->pluck('name', 'code');
        return response()->json($districts);
    }

    // Ambil kelurahan berdasarkan district code (kode kecamatan)
    public function getVillages($district_code)
    {
        $villages = Village::where('district_code', $district_code)->pluck('name', 'code');
        return response()->json($villages);
    }
}
