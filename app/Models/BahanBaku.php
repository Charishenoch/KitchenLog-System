<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';
    protected $fillable = ['nama_bahan','stok','satuan','batas_minimum','harga','deskripsi','tgl_kadaluarsa','tgl_masuk'];
    protected $casts = ['tgl_kadaluarsa' => 'date', 'tgl_masuk' => 'date', 'stok' => 'float', 'batas_minimum' => 'float', 'harga' => 'float'];

    public function resep() { return $this->hasMany(Resep::class); }
    public function notifikasi() { return $this->hasMany(Notifikasi::class); }
    public function penerimaanBahan() { return $this->hasMany(PenerimaanBahan::class); }
    public function limbah() { return $this->hasMany(Limbah::class); }

    public function getStokMenipisAttribute(): bool
    {
        return $this->stok <= $this->batas_minimum;
    }

    /**
     * Kadaluarsa terdekat dari semua batch penerimaan yang masih ada sisa.
     * Kalau tidak ada penerimaan dengan kadaluarsa → fallback ke tgl_kadaluarsa master.
     */
    public function getKadaluarsaTerdekatAttribute()
    {
        $fromPenerimaan = $this->penerimaanBahan()
            ->whereNotNull('tgl_kadaluarsa')
            ->where('sisa', '>', 0)
            ->orderBy('tgl_kadaluarsa', 'asc')
            ->value('tgl_kadaluarsa');

        if ($fromPenerimaan) {
            return \Carbon\Carbon::parse($fromPenerimaan);
        }

        return $this->tgl_kadaluarsa; // fallback ke data master
    }
}
