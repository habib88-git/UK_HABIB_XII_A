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
     * ✅ Method FEFO (First Expired First Out)
     * Ambil batch dengan kadaluwarsa TERDEKAT terlebih dahulu
     * Digunakan saat penjualan untuk mengurangi stok
     */
    public static function getBatchFEFO($produkId, $jumlahDibutuhkan)
    {
        return self::where('produk_id', $produkId)
            ->where('stok', '>', 0)
            ->orderBy('kadaluwarsa', 'asc')      // ✅ PRIORITAS UTAMA: Kadaluwarsa terdekat
            ->orderBy('created_at', 'asc')       // ✅ PRIORITAS KEDUA: Yang lebih dulu masuk (jika tanggal ED sama)
            ->get();
    }

    /**
     * ✅ Method untuk mengurangi stok batch dengan sistem FEFO
     * Return: array detail batch yang digunakan
     */
    public static function kurangiStokFEFO($produkId, $jumlahDibutuhkan)
    {
        $batches = self::getBatchFEFO($produkId, $jumlahDibutuhkan);
        $sisaKebutuhan = $jumlahDibutuhkan;
        $batchDipakai = [];

        foreach ($batches as $batch) {
            if ($sisaKebutuhan <= 0) break;

            // Ambil sebanyak mungkin dari batch ini (maksimal sesuai stok batch)
            $ambil = min($batch->stok, $sisaKebutuhan);
            
            // Kurangi stok batch
            $batch->stok -= $ambil;
            $batch->save();

            // Catat batch yang dipakai
            $batchDipakai[] = [
                'batch_id' => $batch->batch_id,
                'barcode_batch' => $batch->barcode_batch,
                'jumlah' => $ambil,
                'kadaluwarsa' => $batch->kadaluwarsa,
                'harga_beli' => $batch->harga_beli,
            ];

            $sisaKebutuhan -= $ambil;
        }

        // Validasi: pastikan semua kebutuhan terpenuhi
        if ($sisaKebutuhan > 0) {
            throw new \Exception("Stok tidak cukup! Kurang {$sisaKebutuhan} unit.");
        }

        return $batchDipakai;
    }

    /**
     * ✅ Helper: Cek apakah batch sudah expired
     */
    public function isExpired()
    {
        return $this->kadaluwarsa->isPast();
    }

    /**
     * ✅ Helper: Hitung sisa hari sampai kadaluwarsa
     */
    public function daysUntilExpired()
    {
        return now()->diffInDays($this->kadaluwarsa, false);
    }

    /**
     * ✅ Helper: Status kadaluwarsa (untuk UI)
     */
    public function getStatusKadaluwarsa()
    {
        $days = $this->daysUntilExpired();
        
        if ($days < 0) {
            return [
                'badge' => 'danger',
                'text' => 'Expired ' . abs($days) . ' hari lalu'
            ];
        } elseif ($days == 0) {
            return [
                'badge' => 'danger',
                'text' => 'Kadaluwarsa hari ini'
            ];
        } elseif ($days <= 30) {
            return [
                'badge' => 'warning',
                'text' => $days . ' hari lagi'
            ];
        } else {
            return [
                'badge' => 'success',
                'text' => $days . ' hari lagi'
            ];
        }
    }
}