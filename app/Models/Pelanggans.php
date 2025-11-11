<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggans extends Model
{
    use HasFactory;

    protected $table = 'tbl_pelanggans';
    protected $primaryKey = 'pelanggan_id';

    protected $fillable = [
        'nama_pelanggan',
        'alamat',
        'nomor_telepon',
        'province_id',  
        'city_id',
        'district_id',
        'village_id',
    ];

    public function province()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Province::class);
    }

    public function city()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\City::class);
    }

    public function district()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\District::class);
    }

    public function village()
    {
        return $this->belongsTo(\Laravolt\Indonesia\Models\Village::class);
    }
}
