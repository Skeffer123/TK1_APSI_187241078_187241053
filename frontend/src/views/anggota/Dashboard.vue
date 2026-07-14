<template>
  <div>
    <!-- Welcome Header -->
    <div class="mb-8 bg-gradient-to-r from-unair-dark to-unair-blue p-6 md:p-8 rounded-3xl text-white shadow-lg relative overflow-hidden">
      <!-- Decorative vector -->
      <div class="absolute right-0 bottom-0 w-64 h-64 bg-unair-gold/10 rounded-full blur-2xl transform translate-x-20 translate-y-20"></div>
      
      <div class="relative z-10">
        <span class="text-xs font-bold text-unair-gold tracking-widest uppercase">Selamat Datang</span>
        <h3 class="text-2xl md:text-3xl font-extrabold mt-1">{{ authStore.user?.nama }}</h3>
        <p class="text-xs text-slate-300 mt-2">NIM: {{ authStore.user?.nim }} | Mahasiswa Universitas Airlangga</p>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
      <!-- Active Loans -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Buku Sedang Dipinjam</span>
          <span class="text-3xl font-extrabold text-slate-800 block mt-2">{{ activeLoans.length }}</span>
          <span class="text-[10px] text-slate-400 font-semibold block mt-1">Maksimal peminjaman: 3 buku</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-unair-blue/10 flex items-center justify-center text-unair-blue">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
      </div>

      <!-- Nearest Due Date -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Jatuh Tempo Terdekat</span>
          <span class="text-sm font-bold text-slate-800 block mt-2.5">
            {{ nearestDueDate ? formatDate(nearestDueDate) : '-' }}
          </span>
          <span class="text-[10px] text-slate-400 block mt-1" :class="nearestDueDate ? 'text-amber-600 font-bold' : ''">
            {{ nearestDueDate ? 'Harap kembalikan tepat waktu' : 'Tidak ada pinjaman aktif' }}
          </span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>

      <!-- Total Fines -->
      <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Denda Terhutang</span>
          <span class="text-2xl font-extrabold text-red-600 block mt-2">Rp{{ formatCurrency(totalFines) }}</span>
          <span class="text-[10px] text-slate-400 font-semibold block mt-1">Estimasi denda keterlambatan</span>
        </div>
        <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Active Books Borrowed List -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
      <h4 class="text-sm font-bold text-slate-800 mb-4">Buku Yang Sedang Dipinjam</h4>
      
      <div v-if="loading" class="space-y-3 animate-pulse">
        <div v-for="i in 2" :key="i" class="h-16 bg-slate-50 border rounded-xl"></div>
      </div>

      <div v-else-if="activeLoans.length === 0" class="text-center py-8 text-slate-400 font-medium text-xs">
        Anda sedang tidak meminjam buku apa pun. Silakan cari buku di menu <router-link to="/anggota/katalog" class="text-unair-blue hover:underline font-bold">Katalog Buku</router-link>.
      </div>

      <div v-else class="space-y-4">
        <div 
          v-for="loan in activeLoans" 
          :key="loan.id_peminjaman"
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 transition-all gap-4 text-xs"
        >
          <div>
            <span class="font-bold text-slate-800 text-sm block">{{ loan.buku?.judul }}</span>
            <span class="text-slate-400 block mt-1">Pengarang: {{ loan.buku?.pengarang }}</span>
          </div>
          <div class="flex flex-col sm:items-end gap-1.5">
            <span class="text-slate-500 font-medium">Jatuh Tempo: <strong class="text-slate-700">{{ formatDate(loan.tgl_jatuh_tempo) }}</strong></span>
            
            <!-- Late runtime estimated fine -->
            <span v-if="loan.runtime_denda" class="text-[10px] text-red-600 font-bold">
              Terlambat {{ loan.runtime_denda.jumlah_hari }} hari (Estimasi denda: Rp{{ formatCurrency(loan.runtime_denda.total_denda) }})
            </span>
            <span v-else class="text-[10px] text-emerald-600 font-bold">Tepat Waktu</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../services/api';

const authStore = useAuthStore();
const activeLoans = ref([]);
const nearestDueDate = ref(null);
const totalFines = ref(0);
const loading = ref(true);

const fetchLoansStatus = async () => {
  loading.value = true;
  try {
    const res = await api.get('/peminjaman/status');
    if (res.success) {
      // Filter out only active loans (dipinjam or terlambat)
      activeLoans.value = res.data.filter(l => l.status === 'dipinjam' || l.status === 'terlambat');
      
      // Compute nearest due date
      if (activeLoans.value.length > 0) {
        const dates = activeLoans.value.map(l => new Date(l.tgl_jatuh_tempo));
        nearestDueDate.value = new Date(Math.min(...dates));
      } else {
        nearestDueDate.value = null;
      }

      // Compute total fines (unpaid denda + runtime denda estimates)
      let sum = 0;
      res.data.forEach(l => {
        if (l.denda && l.denda.status_bayar === 'belum_bayar') {
          sum += parseFloat(l.denda.total_denda);
        } else if (l.runtime_denda) {
          sum += parseFloat(l.runtime_denda.total_denda);
        }
      });
      totalFines.value = sum;
    }
  } catch (err) {
    console.error('Failed to load member dashboard stats:', err);
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateVal) => {
  if (!dateVal) return '-';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateVal).toLocaleDateString('id-ID', options);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID').format(val || 0);
};

onMounted(() => {
  fetchLoansStatus();
});
</script>
