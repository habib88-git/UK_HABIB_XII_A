<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualans extends Model
{
    protected $table = 'tbl_detail_penjualans';
    protected $primaryKey = 'detail_id';
    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'jumlah_produk',
        'subtotal'
        ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualans::class, 'penjualan_id', 'penjualan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produks::class, 'produk_id', 'produk_id');
    }
}
