<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Laporan extends Model
{
    use HasFactory;

    protected $table = 'laporan';
    protected $primaryKey = 'id_laporan';

    protected $fillable = [
        'id_admin',
        'jenis_laporan',
        'tgl_generate',
        'periode_awal',
        'periode_akhir',
    ];

    protected $casts = [
        'tgl_generate' => 'datetime',
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
