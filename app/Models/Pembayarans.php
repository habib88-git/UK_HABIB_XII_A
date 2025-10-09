<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Penjualans;

class Pembayarans extends Model
{
    use HasFactory;

    protected $table = 'tbl_pembayarans';
    protected $primaryKey = 'pembayaran_id';

    protected $fillable = [
        'penjualan_id',
        'metode',
        'jumlah',
        'kembalian',
        'tanggal_pembayaran',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualans::class, 'penjualan_id', 'penjualan_id');
    }
}
