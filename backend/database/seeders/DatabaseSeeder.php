<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Denda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin
        $admin = Admin::create([
            'nama' => 'Admin Perpustakaan UNAIR',
            'username' => 'admin',
            'email' => 'admin@unair.ac.id',
            'password' => Hash::make('admin123'),
        ]);

        // 2. Seed Anggota (5 members)
        $anggotaList = [
            [
                'nama' => 'Ahmad Fauzi',
                'nim' => '1020113300000001',
                'email' => 'ahmad.fauzi@fst.unair.ac.id',
                'no_telepon' => '081234567890',
                'status' => 'aktif',
                'tanggal_daftar' => '2026-01-10',
                'password' => Hash::make('anggota123'),
            ],
            [
                'nama' => 'Nabila Putri',
                'nim' => '1020113300000002',
                'email' => 'nabila.putri@fst.unair.ac.id',
                'no_telepon' => '082345678901',
                'status' => 'aktif',
                'tanggal_daftar' => '2026-01-15',
                'password' => Hash::make('anggota123'),
            ],
            [
                'nama' => 'Budi Santoso',
                'nim' => '1020113300000003',
                'email' => 'budi.santoso@fst.unair.ac.id',
                'no_telepon' => '083456789012',
                'status' => 'aktif',
                'tanggal_daftar' => '2026-02-01',
                'password' => Hash::make('anggota123'),
            ],
            [
                'nama' => 'Rian Hidayat',
                'nim' => '1020113300000004',
                'email' => 'rian.hidayat@fst.unair.ac.id',
                'no_telepon' => '084567890123',
                'status' => 'aktif',
                'tanggal_daftar' => '2026-02-10',
                'password' => Hash::make('anggota123'),
            ],
            [
                'nama' => 'Siti Aminah (Nonaktif)',
                'nim' => '1020113300000005',
                'email' => 'siti.aminah@fst.unair.ac.id',
                'no_telepon' => '085678901234',
                'status' => 'nonaktif',
                'tanggal_daftar' => '2026-02-15',
                'password' => Hash::make('anggota123'),
            ],
        ];

        $createdAnggota = [];
        foreach ($anggotaList as $data) {
            $data['id_admin'] = $admin->id_admin;
            $createdAnggota[] = Anggota::create($data);
        }

        // 3. Seed Buku (10 books)
        $bukuList = [
            [
                'judul' => 'Algoritma dan Struktur Data',
                'pengarang' => 'Rinaldi Munir',
                'penerbit' => 'Informatika',
                'tahun_terbit' => 2020,
                'kategori' => 'Teknologi',
                'stok' => 5,
            ],
            [
                'judul' => 'Dasar-Dasar Pemrograman Web',
                'pengarang' => 'Abdul Kadir',
                'penerbit' => 'Andi Offset',
                'tahun_terbit' => 2019,
                'kategori' => 'Teknologi',
                'stok' => 3,
            ],
            [
                'judul' => 'Pengantar Sistem Informasi',
                'pengarang' => 'Jogiyanto HM',
                'penerbit' => 'Andi Offset',
                'tahun_terbit' => 2018,
                'kategori' => 'Sistem Informasi',
                'stok' => 2,
            ],
            [
                'judul' => 'Sistem Manajemen Basis Data',
                'pengarang' => 'Fathansyah',
                'penerbit' => 'Informatika',
                'tahun_terbit' => 2021,
                'kategori' => 'Teknologi',
                'stok' => 4,
            ],
            [
                'judul' => 'Analisis dan Perancangan Sistem Informasi',
                'pengarang' => 'Al-Bahra Bin Ladjamudin',
                'penerbit' => 'Graha Ilmu',
                'tahun_terbit' => 2015,
                'kategori' => 'Sistem Informasi',
                'stok' => 3,
            ],
            [
                'judul' => 'Kalkulus Purcell',
                'pengarang' => 'Edwin J. Purcell',
                'penerbit' => 'Erlangga',
                'tahun_terbit' => 2016,
                'kategori' => 'Matematika',
                'stok' => 2,
            ],
            [
                'judul' => 'Metode Penelitian Kuantitatif',
                'pengarang' => 'Sugiyono',
                'penerbit' => 'Alfabeta',
                'tahun_terbit' => 2017,
                'kategori' => 'Pendidikan',
                'stok' => 6,
            ],
            [
                'judul' => 'Fisika Dasar',
                'pengarang' => 'Halliday & Resnick',
                'penerbit' => 'Erlangga',
                'tahun_terbit' => 2014,
                'kategori' => 'Fisika',
                'stok' => 1,
            ],
            [
                'judul' => 'Ekonomi Makro',
                'pengarang' => 'Sadono Sukirno',
                'penerbit' => 'Rajawali Pers',
                'tahun_terbit' => 2018,
                'kategori' => 'Ekonomi',
                'stok' => 4,
            ],
            [
                'judul' => 'Pengantar Akuntansi (Stok Kosong)',
                'pengarang' => 'Warren Reeve',
                'penerbit' => 'Salemba Empat',
                'tahun_terbit' => 2019,
                'kategori' => 'Ekonomi',
                'stok' => 0,
            ],
        ];

        $createdBuku = [];
        foreach ($bukuList as $data) {
            $data['id_admin'] = $admin->id_admin;
            $createdBuku[] = Buku::create($data);
        }

        // 4. Seed Peminjaman & Denda transactions
        // Note: Let's assume current time is 2026-07-13
        $now = Carbon::create(2026, 7, 13);

        // Transaction 1: Active loan, not late
        Peminjaman::create([
            'id_anggota' => $createdAnggota[0]->id_anggota, // Ahmad
            'id_buku' => $createdBuku[0]->id_buku, // Algoritma
            'tgl_pinjam' => $now->copy()->subDays(2)->toDateString(),
            'tgl_jatuh_tempo' => $now->copy()->addDays(5)->toDateString(),
            'tgl_kembali' => null,
            'status' => 'dipinjam',
        ]);
        // Decrement stock for the active loan
        $createdBuku[0]->decrement('stok');

        // Transaction 2: Returned loan, not late
        Peminjaman::create([
            'id_anggota' => $createdAnggota[1]->id_anggota, // Nabila
            'id_buku' => $createdBuku[1]->id_buku, // Web
            'tgl_pinjam' => $now->copy()->subDays(10)->toDateString(),
            'tgl_jatuh_tempo' => $now->copy()->subDays(3)->toDateString(),
            'tgl_kembali' => $now->copy()->subDays(4)->toDateString(), // returned 4 days ago (tgl_kembali <= tgl_jatuh_tempo)
            'status' => 'dikembalikan',
        ]);

        // Transaction 3: Active loan, late (should be marked as late, or status 'dipinjam' but date is past due)
        Peminjaman::create([
            'id_anggota' => $createdAnggota[2]->id_anggota, // Budi
            'id_buku' => $createdBuku[2]->id_buku, // PSI
            'tgl_pinjam' => $now->copy()->subDays(15)->toDateString(),
            'tgl_jatuh_tempo' => $now->copy()->subDays(8)->toDateString(), // due date passed 8 days ago
            'tgl_kembali' => null,
            'status' => 'terlambat', // marked as terlambat
        ]);
        $createdBuku[2]->decrement('stok');

        // Transaction 4: Returned late, unpaid denda
        $pem4 = Peminjaman::create([
            'id_anggota' => $createdAnggota[3]->id_anggota, // Rian
            'id_buku' => $createdBuku[3]->id_buku, // DBMS
            'tgl_pinjam' => $now->copy()->subDays(20)->toDateString(),
            'tgl_jatuh_tempo' => $now->copy()->subDays(13)->toDateString(),
            'tgl_kembali' => $now->copy()->subDays(5)->toDateString(), // 8 days late
            'status' => 'dikembalikan',
        ]);
        Denda::create([
            'id_peminjaman' => $pem4->id_peminjaman,
            'jumlah_hari' => 8,
            'total_denda' => 8 * 2000,
            'status_bayar' => 'belum_bayar',
            'tgl_denda' => null,
        ]);

        // Transaction 5: Returned late, paid denda
        $pem5 = Peminjaman::create([
            'id_anggota' => $createdAnggota[0]->id_anggota, // Ahmad
            'id_buku' => $createdBuku[4]->id_buku, // APSI
            'tgl_pinjam' => $now->copy()->subDays(20)->toDateString(),
            'tgl_jatuh_tempo' => $now->copy()->subDays(13)->toDateString(),
            'tgl_kembali' => $now->copy()->subDays(10)->toDateString(), // 3 days late
            'status' => 'dikembalikan',
        ]);
        Denda::create([
            'id_peminjaman' => $pem5->id_peminjaman,
            'jumlah_hari' => 3,
            'total_denda' => 3 * 2000,
            'status_bayar' => 'lunas',
            'tgl_denda' => $now->copy()->subDays(10)->toDateString(), // paid on the same day it was returned
        ]);
    }
}
