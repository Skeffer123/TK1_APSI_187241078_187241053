<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nama',
        'username',
        'password',
        'email',
    ];

    protected $hidden = [
        'password',
    ];

    public function anggota()
    {
        return $this->hasMany(Anggota::class, 'id_admin', 'id_admin');
    }

    public function buku()
    {
        return $this->hasMany(Buku::class, 'id_admin', 'id_admin');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'id_admin', 'id_admin');
    }
}
