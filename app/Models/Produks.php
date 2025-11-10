<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produks extends Model
{
    use HasFactory;

    protected $table = 'tbl_produks';
    protected $primaryKey = 'produk_id';

    protected $fillable = [
        'barcode',
        'nama_produk',
        'photo',
        'harga_jual',
        'harga_beli',
        'stok',
        'kadaluwarsa',
        'kategori_id',
        'satuan_id',
        'supplier_id',
    ];

    protected $casts = [
        'kadaluwarsa' => 'date',
    ];

    // === AUTO GENERATE BARCODE ===
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($produk) {
            if (empty($produk->barcode)) {
                $produk->barcode = self::generateUniqueBarcode();
            }
        });
    }

    public static function generateUniqueBarcode()
    {
        do {
            $base = str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
            $checkDigit = self::calculateEAN13CheckDigit($base);
            $barcode = $base . $checkDigit;
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    private static function calculateEAN13CheckDigit($digits)
    {
        $sum = 0;
        foreach (str_split($digits) as $i => $d) {
            $sum += ($i % 2 === 0 ? $d : $d * 3);
        }
        $mod = $sum % 10;
        return $mod === 0 ? 0 : 10 - $mod;
    }

    // === RELASI ===
    public function kategori()
    {
        return $this->belongsTo(Kategoris::class, 'kategori_id', 'kategori_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class, 'supplier_id', 'supplier_id');
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
