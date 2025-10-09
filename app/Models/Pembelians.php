<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Suppliers;
use App\Models\users;
use App\Models\DetailPembelians;

class Pembelians extends Model
{
    use HasFactory;

    protected $table = 'tbl_pembelians';
    protected $primaryKey = 'pembelian_id';

    protected $fillable = [
        'tanggal',
        'total_harga',
        'supplier_id',
        'user_id',
    ];

    // Relasi ke Supplier
    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id');
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    // Relasi ke Detail Pembelian
    public function details()
    {
        return $this->hasMany(DetailPembelians::class, 'pembelian_id');
    }
}
