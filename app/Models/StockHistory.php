<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $table = 'tbl_stock_history';
    protected $primaryKey = 'stock_history_id';
    protected $fillable = [
        'produk_id',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
        'referensi_tipe',
        'referensi_id',
        'user_id',
    ];

    // Relasi ke Produk
    public function produk()
    {
        return $this->belongsTo(Produks::class, 'produk_id', 'produk_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    // Scope untuk filter
    public function scopeMasuk($query)
    {
        return $query->where('tipe', 'masuk');
    }

    public function scopeKeluar($query)
    {
        return $query->where('tipe', 'keluar');
    }

    public function scopeProduk($query, $produkId)
    {
        return $query->where('produk_id', $produkId);
    }
}