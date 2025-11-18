<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualans extends Model
{
    use HasFactory;

    protected $table = 'tbl_detail_penjualans';
    protected $primaryKey = 'detail_id';
    
    protected $fillable = [
        'penjualan_id',
        'produk_id',
        'batch_id',              // ✅ TAMBAH INI
        'jumlah_produk',
        'subtotal',
        'barcode_batch',         // ✅ TAMBAH INI
        'kadaluwarsa_batch',     // ✅ TAMBAH INI
    ];

    protected $casts = [
        'kadaluwarsa_batch' => 'date',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualans::class, 'penjualan_id', 'penjualan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produks::class, 'produk_id', 'produk_id');
    }

    // ✅ TAMBAH RELASI KE BATCH
    public function batch()
    {
        return $this->belongsTo(BatchProduk::class, 'batch_id', 'batch_id');
    }
}