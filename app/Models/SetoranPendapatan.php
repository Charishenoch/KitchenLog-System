<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SetoranPendapatan extends Model
{
    protected $table = 'setoran_pendapatan';
    protected $fillable = ['kasir_id','jumlah','tanggal_setor','keterangan','status'];
    protected $casts = ['tanggal_setor' => 'date', 'jumlah' => 'float'];

    public function kasir() { return $this->belongsTo(User::class, 'kasir_id'); }
}
