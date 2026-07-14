<template>
  <div>
    <!-- Title Section -->
    <div class="mb-8">
      <h3 class="text-2xl font-bold text-slate-800">Peminjaman Buku Saya</h3>
      <p class="text-xs text-slate-500 mt-1">Daftar buku yang sedang Anda pinjam dan riwayat transaksi pengembalian sebelumnya.</p>
    </div>

    <!-- Active Loans Section -->
    <div class="mb-10">
      <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
        <span>Peminjaman Aktif</span>
      </h4>

      <!-- Loading skeleton -->
      <div v-if="loading" class="space-y-4 animate-pulse">
        <div v-for="i in 2" :key="i" class="h-24 bg-white border rounded-2xl"></div>
      </div>

      <!-- No active loans -->
      <div v-else-if="activeLoans.length === 0" class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm text-center text-slate-400 font-medium text-xs">
        Anda tidak memiliki pinjaman aktif saat ini.
      </div>

      <!-- Active loans list -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div 
          v-for="loan in activeLoans" 
          :key="loan.id_peminjaman"
          class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 relative overflow-hidden"
          :class="loan.status === 'terlambat' ? 'border-l-4 border-l-red-500' : 'border-l-4 border-l-unair-blue'"
        >
          <div>
            <div class="flex justify-between items-start">
              <h5 class="font-extrabold text-slate-800 text-sm line-clamp-2">{{ loan.buku?.judul }}</h5>
              <span 
                class="font-bold text-[9px] px-2 py-0.5 rounded uppercase"
                :class="loan.status === 'terlambat' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-700'"
              >
                {{ loan.status }}
              </span>
            </div>
            <span class="text-[10px] text-slate-500 block mt-1 font-semibold">Oleh: {{ loan.buku?.pengarang }}</span>

            <!-- Dates Grid -->
            <div class="grid grid-cols-2 gap-2 mt-4 text-[10px] bg-slate-50 p-3 rounded-xl">
              <div>
                <span class="text-slate-400 block font-semibold">Tanggal Pinjam</span>
                <span class="text-slate-700 font-bold block mt-0.5">{{ formatDate(loan.tgl_pinjam) }}</span>
              </div>
              <div>
                <span class="text-slate-400 block font-semibold">Jatuh Tempo</span>
                <span class="text-slate-700 font-bold block mt-0.5">{{ formatDate(loan.tgl_jatuh_tempo) }}</span>
              </div>
            </div>

            <!-- Fine notifications -->
            <div v-if="loan.runtime_denda" class="mt-4 p-3 bg-red-50 text-red-700 text-[10px] rounded-xl border border-red-100 flex flex-col gap-0.5">
              <span class="font-bold">Keterlambatan Terdeteksi!</span>
              <span>Terlambat: <strong>{{ loan.runtime_denda.jumlah_hari }} Hari</strong></span>
              <span>Estimasi Denda: <strong>Rp{{ formatCurrency(loan.runtime_denda.total_denda) }}</strong></span>
            </div>
          </div>

          <!-- Return Action -->
          <div class="mt-6 pt-4 border-t border-slate-50 flex justify-end">
            <button 
              @click="handleReturn(loan.id_peminjaman)"
              :disabled="returning"
              class="py-2 px-4 rounded-xl bg-unair-blue hover:bg-unair-dark text-white font-bold text-xs shadow-md transition-all duration-200 disabled:opacity-50"
            >
              {{ returning ? 'Memproses...' : 'Kembalikan Buku' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- History Section -->
    <div>
      <h4 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
        <span>Riwayat Transaksi</span>
      </h4>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                <th class="py-4 px-6">Buku Yang Dipinjam</th>
                <th class="py-4 px-6 text-center">Tanggal Pinjam</th>
                <th class="py-4 px-6 text-center">Tanggal Kembali</th>
                <th class="py-4 px-6 text-right">Denda / Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-if="loading" v-for="i in 3" :key="i" class="animate-pulse">
                <td colspan="4" class="py-5 px-6"><div class="h-4 bg-slate-100 rounded"></div></td>
              </tr>
              <tr v-else-if="historyLoans.length === 0">
                <td colspan="4" class="py-8 text-center text-slate-400 font-medium">Belum ada riwayat peminjaman.</td>
              </tr>
              <tr v-else v-for="loan in historyLoans" :key="loan.id_peminjaman" class="hover:bg-slate-50/50 transition-colors">
                <td class="py-4 px-6">
                  <span class="font-bold text-slate-800 text-sm block">{{ loan.buku?.judul }}</span>
                  <span class="text-[10px] text-slate-400 block mt-0.5">Oleh: {{ loan.buku?.pengarang }}</span>
                </td>
                <td class="py-4 px-6 text-center font-medium">{{ formatDate(loan.tgl_pinjam) }}</td>
                <td class="py-4 px-6 text-center font-medium">{{ formatDate(loan.tgl_kembali) }}</td>
                <td class="py-4 px-6 text-right">
                  <div v-if="loan.denda">
                    <span class="font-bold text-red-600 block">Rp{{ formatCurrency(loan.denda.total_denda) }}</span>
                    <span 
                      class="text-[9px] font-semibold px-2 py-0.5 rounded uppercase mt-0.5 inline-block"
                      :class="loan.denda.status_bayar === 'lunas' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'"
                    >
                      {{ loan.denda.status_bayar === 'lunas' ? 'LUNAS' : 'PENDING' }}
                    </span>
                  </div>
                  <span v-else class="text-slate-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../../services/api';

const loans = ref([]);
const loading = ref(true);
const returning = ref(false);

const activeLoans = computed(() => {
  return loans.value.filter(l => l.status === 'dipinjam' || l.status === 'terlambat');
});

const historyLoans = computed(() => {
  return loans.value.filter(l => l.status === 'dikembalikan');
});

const fetchLoans = async () => {
  loading.value = true;
  try {
    const res = await api.get('/peminjaman/status');
    if (res.success) {
      loans.value = res.data;
    }
  } catch (err) {
    console.error('Failed to load loans status:', err);
  } finally {
    loading.value = false;
  }
};

const handleReturn = async (id) => {
  if (confirm('Apakah Anda yakin ingin mengembalikan buku ini ke perpustakaan?')) {
    returning.value = true;
    try {
      const res = await api.post(`/pengembalian/${id}`);
      if (res.success) {
        alert(res.message);
        fetchLoans();
      }
    } catch (err) {
      alert(err.message || 'Gagal mengembalikan buku.');
    } finally {
      returning.value = false;
    }
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('id-ID').format(val || 0);
};

onMounted(() => {
  fetchLoans();
});
</script>
