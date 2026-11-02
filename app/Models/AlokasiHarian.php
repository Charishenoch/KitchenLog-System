<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiHarian extends Model
{
    protected $table = 'alokasi_harian';

    protected $fillable = [
        'tanggal',
        'menu_id',
        'porsi_dibuat',
        'porsi_tersedia',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'porsi_dibuat' => 'integer',
        'porsi_tersedia' => 'integer',
    ];

    /**
     * Relasi ke menu yang dialokasikan.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}

