<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Produks;


class Kategoris extends Model
{
    use HasFactory;

    protected $table = 'tbl_kategoris';
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'nama_kategori',
    ];

    public function produks()
    {
        return $this->hasMany(Produks::class, 'kategori_id', 'kategori_id');
    }
}
