<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produks;


class Satuans extends Model
{
    use HasFactory;

    protected $table = 'tbl_satuans';
    protected $primaryKey = 'satuan_id';

    protected $fillable = [
        'nama_satuan',
    ];

    public function produks()
    {
        return $this->hasMany(Produks::class, 'satuan_id', 'satuan_id');
    }
}
