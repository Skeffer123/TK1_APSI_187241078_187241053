<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_buku' => $this->id_buku,
            'id_admin' => $this->id_admin,
            'judul' => $this->judul,
            'pengarang' => $this->pengarang,
            'penerbit' => $this->penerbit,
            'tahun_terbit' => $this->tahun_terbit,
            'kategori' => $this->kategori,
            'stok' => $this->stok,
            'admin' => $this->relationLoaded('admin') && $this->admin ? [
                'id_admin' => $this->admin->id_admin,
                'nama' => $this->admin->nama,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
