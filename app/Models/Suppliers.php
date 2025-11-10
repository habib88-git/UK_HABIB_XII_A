<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produks;

class Suppliers extends Model
{
    use HasFactory;

    protected $table = 'tbl_suppliers';
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'nama_supplier',
        'alamat',
        'no_telp',
    ];

    public function produks()
    {
        return $this->hasMany(Produks::class, 'supplier_id', 'supplier_id');
    }
}
