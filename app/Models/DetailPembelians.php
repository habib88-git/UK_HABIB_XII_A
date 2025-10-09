<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembelians extends Model
{
    use HasFactory;

    protected $table = 'tbl_detail_pembelians';
    protected $primaryKey = 'detail_pembelian_id';

    protected $fillable = [
        'pembelian_id',
        'produk_id',
        'jumlah',
        'harga_beli',
        'subtotal',
    ];

    // Relasi ke pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelians::class, 'pembelian_id', 'pembelian_id');
    }

    // Relasi ke produk
    public function produk()
    {
        return $this->belongsTo(Produks::class, 'produk_id', 'produk_id');
    }
}
