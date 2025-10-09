<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pelanggans;
use App\Models\Users;
use App\Models\DetailPenjualans;
use App\Models\Pembayarans;

class Penjualans extends Model
{
    use HasFactory;

    protected $table = 'tbl_penjualans';
    protected $primaryKey = 'penjualan_id';

    protected $fillable = [
        'tanggal_penjualan',
        'total_harga',
        'diskon',
        'pelanggan_id',
        'user_id',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggans::class, 'pelanggan_id', 'pelanggan_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualans::class, 'penjualan_id', 'penjualan_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayarans::class, 'penjualan_id', 'penjualan_id');
    }
}
