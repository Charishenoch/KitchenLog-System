<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $fillable = ['tanggal','kasir_id','menu_id','jumlah_porsi','total_harga'];
    protected $casts = ['tanggal' => 'datetime', 'total_harga' => 'float'];

    public function kasir() { return $this->belongsTo(User::class, 'kasir_id'); }
    public function menu() { return $this->belongsTo(Menu::class); }
}
