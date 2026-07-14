<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->uuid('id_admin')->primary();
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('email', 100)->unique();
            $table->timestamps();
        });

        Schema::create('anggota', function (Blueprint $table) {
            $table->id('id_anggota');
            $table->uuid('id_admin')->nullable();
            $table->string('nama', 100);
            $table->char('nim', 16)->unique();
            $table->string('email', 100)->unique();
            $table->string('no_telepon', 15)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('tanggal_daftar');
            $table->string('password', 255);
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admin')->onDelete('set null');
        });

        Schema::create('buku', function (Blueprint $table) {
            $table->id('id_buku');
            $table->uuid('id_admin');
            $table->string('judul', 255);
            $table->string('pengarang', 150);
            $table->string('penerbit', 100)->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('kategori', 50)->nullable();
            $table->integer('stok')->default(0);
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admin')->onDelete('restrict');
        });

        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id('id_peminjaman');
            $table->unsignedBigInteger('id_anggota');
            $table->unsignedBigInteger('id_buku');
            $table->date('tgl_pinjam');
            $table->date('tgl_jatuh_tempo');
            $table->date('tgl_kembali')->nullable();
            $table->enum('status', ['dipinjam', 'dikembalikan', 'terlambat'])->default('dipinjam');
            $table->timestamps();

            $table->foreign('id_anggota')->references('id_anggota')->on('anggota')->onDelete('cascade');
            $table->foreign('id_buku')->references('id_buku')->on('buku')->onDelete('cascade');
        });

        Schema::create('denda', function (Blueprint $table) {
            $table->id('id_denda');
            $table->unsignedBigInteger('id_peminjaman')->unique();
            $table->integer('jumlah_hari');
            $table->decimal('total_denda', 10, 2);
            $table->enum('status_bayar', ['belum_bayar', 'lunas'])->default('belum_bayar');
            $table->date('tgl_denda')->nullable();
            $table->timestamps();

            $table->foreign('id_peminjaman')->references('id_peminjaman')->on('peminjaman')->onDelete('cascade');
        });

        Schema::create('laporan', function (Blueprint $table) {
            $table->id('id_laporan');
            $table->uuid('id_admin');
            $table->string('jenis_laporan', 80);
            $table->dateTime('tgl_generate');
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admin')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
        Schema::dropIfExists('denda');
        Schema::dropIfExists('peminjaman');
        Schema::dropIfExists('buku');
        Schema::dropIfExists('anggota');
        Schema::dropIfExists('admin');
    }
};
