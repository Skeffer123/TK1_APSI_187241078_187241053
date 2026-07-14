<template>
  <div>
    <!-- Title Section -->
    <div class="mb-8">
      <h3 class="text-2xl font-bold text-slate-800">Dashboard Admin</h3>
      <p class="text-xs text-slate-500 mt-1">Selamat datang kembali! Berikut ringkasan aktivitas perpustakaan hari ini.</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-4 gap-6 animate-pulse">
      <div v-for="i in 4" :key="i" class="h-32 bg-white rounded-2xl border border-slate-100"></div>
    </div>

    <!-- Cards Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Card 1: Total Buku -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Buku</span>
          <span class="text-3xl font-extrabold text-slate-800 block mt-2">{{ stats.total_buku }}</span>
          <span class="text-[10px] font-medium text-slate-400 block mt-1">Stok Akumulatif: {{ stats.total_stok }}</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-unair-blue/10 flex items-center justify-center text-unair-blue">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
      </div>

      <!-- Card 2: Total Anggota -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Anggota</span>
          <span class="text-3xl font-extrabold text-slate-800 block mt-2">{{ stats.total_anggota }}</span>
          <span class="text-[10px] font-medium text-slate-400 block mt-1">Terdaftar di sistem</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-unair-gold/20 flex items-center justify-center text-amber-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
      </div>

      <!-- Card 3: Peminjaman Aktif -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Peminjaman Aktif</span>
          <span class="text-3xl font-extrabold text-slate-800 block mt-2">{{ stats.peminjaman_aktif }}</span>
          <span class="text-[10px] font-medium text-amber-600 block mt-1">Sedang dipinjam/terlambat</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>

      <!-- Card 4: Denda Belum Lunas -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Denda Belum Lunas</span>
          <span class="text-2xl font-extrabold text-red-600 block mt-2">Rp{{ formatCurrency(stats.denda_belum_lunas) }}</span>
          <span class="text-[10px] font-medium text-red-500 block mt-1">Harus segera diselesaikan</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Quick Navigation Shortcut Section -->
    <div class="mt-10 bg-white p-8 rounded-2xl border border-slate-100 shadow-sm">
      <h4 class="text-base font-bold text-slate-800">Navigasi Cepat</h4>
      <p class="text-xs text-slate-400 mt-1">Klik menu di bawah ini untuk mengakses fitur utama secara cepat.</p>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <router-link to="/admin/buku" class="p-4 rounded-xl border border-slate-100 hover:border-unair-blue/30 bg-slate-50 hover:bg-white text-center font-medium text-sm text-slate-700 hover:text-unair-blue transition-all duration-200">
          Kelola Buku
        </router-link>
        <router-link to="/admin/anggota" class="p-4 rounded-xl border border-slate-100 hover:border-unair-blue/30 bg-slate-50 hover:bg-white text-center font-medium text-sm text-slate-700 hover:text-unair-blue transition-all duration-200">
          Kelola Anggota
        </router-link>
        <router-link to="/admin/transaksi" class="p-4 rounded-xl border border-slate-100 hover:border-unair-blue/30 bg-slate-50 hover:bg-white text-center font-medium text-sm text-slate-700 hover:text-unair-blue transition-all duration-200">
          Monitoring Peminjaman
        </router-link>
        <router-link to="/admin/laporan" class="p-4 rounded-xl border border-slate-100 hover:border-unair-blue/30 bg-slate-50 hover:bg-white text-center font-medium text-sm text-slate-700 hover:text-unair-blue transition-all duration-200">
          Cetak Laporan PDF
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const loading = ref(true);
const stats = ref({
  total_buku: 0,
  total_stok: 0,
  total_anggota: 0,
  peminjaman_aktif: 0,
  denda_belum_lunas: 0
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID').format(val || 0);
};

const fetchStats = async () => {
  try {
    const res = await api.get('/dashboard-summary');
    if (res.success) {
      stats.value = res.data;
    }
  } catch (err) {
    console.error('Error fetching dashboard stats:', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
});
</script>
