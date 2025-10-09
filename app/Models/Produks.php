<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kategoris;
use App\Models\Satuans;


class Produks extends Model
{
    use HasFactory;

    protected $table = 'tbl_produks';
    protected $primaryKey = 'produk_id';

    protected $fillable = [
        'nama_produk',
        'photo',
        'harga_jual',
        'harga_beli',
        'stok',
        'kategori_id',
        'satuan_id',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategoris::class, 'kategori_id', 'kategori_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuans::class, 'satuan_id', 'satuan_id');
    }

    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualans::class, 'produk_id', 'produk_id');
    }

}
