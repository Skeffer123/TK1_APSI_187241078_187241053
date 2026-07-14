<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnggotaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_anggota' => $this->id_anggota,
            'id_admin' => $this->id_admin,
            'nama' => $this->nama,
            'nim' => $this->nim,
            'email' => $this->email,
            'no_telepon' => $this->no_telepon,
            'status' => $this->status,
            'tanggal_daftar' => $this->tanggal_daftar ? $this->tanggal_daftar->toDateString() : null,
            'admin' => $this->relationLoaded('admin') && $this->admin ? [
                'id_admin' => $this->admin->id_admin,
                'nama' => $this->admin->nama,
            ] : null,
            'peminjaman' => $this->relationLoaded('peminjaman') ? $this->peminjaman->map(function ($p) {
                return [
                    'id_peminjaman' => $p->id_peminjaman,
                    'id_buku' => $p->id_buku,
                    'buku_judul' => $p->buku ? $p->buku->judul : null,
                    'tgl_pinjam' => $p->tgl_pinjam ? $p->tgl_pinjam->toDateString() : null,
                    'tgl_jatuh_tempo' => $p->tgl_jatuh_tempo ? $p->tgl_jatuh_tempo->toDateString() : null,
                    'tgl_kembali' => $p->tgl_kembali ? $p->tgl_kembali->toDateString() : null,
                    'status' => $p->status,
                    'denda' => $p->denda ? [
                        'id_denda' => $p->denda->id_denda,
                        'jumlah_hari' => $p->denda->jumlah_hari,
                        'total_denda' => $p->denda->total_denda,
                        'status_bayar' => $p->denda->status_bayar,
                        'tgl_denda' => $p->denda->tgl_denda ? $p->denda->tgl_denda->toDateString() : null,
                    ] : null,
                ];
            }) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
