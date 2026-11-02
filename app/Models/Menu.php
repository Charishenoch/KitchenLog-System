<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Menu extends Model
{
    protected $table = 'menu';
    protected $fillable = ['nama_menu','harga','kategori','deskripsi','tersedia'];
    protected $casts = ['harga' => 'float', 'tersedia' => 'boolean'];

    public function resep(): HasMany
    {
        return $this->hasMany(Resep::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function alokasiHarian(): HasMany
    {
        return $this->hasMany(AlokasiHarian::class, 'menu_id');
    }

    public function alokasiHariIni(): HasOne
    {
        return $this->hasOne(AlokasiHarian::class, 'menu_id')->whereDate('tanggal', today());
    }
}

