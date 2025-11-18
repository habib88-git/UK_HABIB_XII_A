<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchProduk extends Model
{
    protected $table = 'tbl_batch_produks';
    protected $primaryKey = 'batch_id';

    protected $fillable = [
        'produk_id',
        'barcode_batch',
        'stok',
        'kadaluwarsa',
        'harga_beli',
        'pembelian_id',
    ];

    protected $casts = [
        'kadaluwarsa' => 'date',
        'harga_beli' => 'decimal:2',
    ];

    // Relasi ke produk master
    public function produk()
    {
        return $this->belongsTo(Produks::class, 'produk_id', 'produk_id');
    }

    // Relasi ke pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelians::class, 'pembelian_id', 'pembelian_id');
    }

    // Relasi ke detail penjualan
    public function detailPenjualans()
    {
        return $this->hasMany(DetailPenjualans::class, 'batch_id', 'batch_id');
    }

    // Scope untuk batch yang masih ada stok
    public function scopeAktif($query)
    {
        return $query->where('stok', '>', 0);
    }

    // Scope untuk batch yang hampir/sudah kadaluwarsa
    public function scopeKadaluwarsa($query, $days = 30)
    {
        return $query->whereDate('kadaluwarsa', '<=', now()->addDays($days));
    }

    /**
     * ✅ Method FIFO - Ambil batch dengan kadaluwarsa terdekat
     * Digunakan saat penjualan untuk mengurangi stok
     */
    public static function getBatchFIFO($produkId, $jumlahDibutuhkan)
    {
        return self::where('produk_id', $produkId)
            ->where('stok', '>', 0)
            ->orderBy('kadaluwarsa', 'asc')      // Prioritas 1: Kadaluwarsa terdekat
            ->orderBy('created_at', 'asc')       // Prioritas 2: Yang lebih dulu masuk
            ->get();
    }

    /**
     * ✅ Method untuk mengurangi stok batch (untuk penjualan)
     * Return: array detail batch yang digunakan
     */
    public static function kurangiStokFIFO($produkId, $jumlahDibutuhkan)
    {
        $batches = self::getBatchFIFO($produkId, $jumlahDibutuhkan);
        $sisaKebutuhan = $jumlahDibutuhkan;
        $batchDipakai = [];

        foreach ($batches as $batch) {
            if ($sisaKebutuhan <= 0) break;

            $ambil = min($batch->stok, $sisaKebutuhan);
            
            $batch->stok -= $ambil;
            $batch->save();

            $batchDipakai[] = [
                'batch_id' => $batch->batch_id,
                'barcode_batch' => $batch->barcode_batch,
                'jumlah' => $ambil,
                'kadaluwarsa' => $batch->kadaluwarsa,
                'harga_beli' => $batch->harga_beli,
            ];

            $sisaKebutuhan -= $ambil;
        }

        if ($sisaKebutuhan > 0) {
            throw new \Exception("Stok tidak cukup! Kurang {$sisaKebutuhan} unit.");
        }

        return $batchDipakai;
    }
}