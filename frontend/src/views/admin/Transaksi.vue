<template>
  <div>
    <!-- Header Section -->
    <div class="mb-8">
      <h3 class="text-2xl font-bold text-slate-800">Transaksi Peminjaman</h3>
      <p class="text-xs text-slate-500 mt-1">Pantau seluruh sirkulasi buku perpustakaan, lakukan pengembalian, dan kelola denda anggota.</p>
    </div>

    <!-- Filters and Searches -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-6 flex flex-col lg:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap items-center gap-2">
        <button 
          v-for="st in statusOptions" 
          :key="st.value"
          @click="setStatusFilter(st.value)"
          class="px-4 py-2 rounded-xl text-xs font-semibold border transition-all"
          :class="statusFilter === st.value 
            ? 'bg-unair-dark text-white border-unair-dark shadow-md' 
            : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200'"
        >
          {{ st.label }}
        </button>
      </div>

      <div class="flex items-center space-x-2 w-full lg:w-auto">
        <div class="flex items-center space-x-1">
          <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mulai</label>
          <input 
            v-model="tglAwal"
            type="date"
            class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue"
          />
        </div>
        <div class="flex items-center space-x-1">
          <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hingga</label>
          <input 
            v-model="tglAkhir"
            type="date"
            class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue"
          />
        </div>
        <button 
          @click="applyFilters"
          class="py-1.5 px-4 rounded-xl bg-unair-blue text-white text-xs font-semibold hover:bg-unair-dark shadow"
        >
          Cari
        </button>
        <button 
          @click="resetFilters"
          class="py-1.5 px-3 rounded-xl border hover:bg-slate-50 text-xs font-semibold"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-4 px-6">Anggota / NIM</th>
              <th class="py-4 px-6">Buku Yang Dipinjam</th>
              <th class="py-4 px-6 text-center">Tgl Pinjam</th>
              <th class="py-4 px-6 text-center">Jatuh Tempo</th>
              <th class="py-4 px-6 text-center">Tgl Kembali</th>
              <th class="py-4 px-6 text-center">Status</th>
              <th class="py-4 px-6 text-right">Denda / Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
              <td colspan="7" class="py-6 px-6"><div class="h-4 bg-slate-100 rounded"></div></td>
            </tr>
            <tr v-else-if="loans.length === 0">
              <td colspan="7" class="py-10 text-center text-slate-400 font-medium">Tidak ada data transaksi peminjaman.</td>
            </tr>
            <tr v-else v-for="loan in loans" :key="loan.id_peminjaman" class="hover:bg-slate-50/50 transition-colors">
              <td class="py-4 px-6">
                <span class="font-bold text-slate-800 block text-sm">{{ loan.anggota?.nama || '-' }}</span>
                <span class="text-[10px] text-slate-400 block font-semibold">NIM: {{ loan.anggota?.nim || '-' }}</span>
              </td>
              <td class="py-4 px-6 font-semibold text-slate-800">{{ loan.buku?.judul || '-' }}</td>
              <td class="py-4 px-6 text-center font-medium">{{ formatDate(loan.tgl_pinjam) }}</td>
              <td class="py-4 px-6 text-center font-medium">{{ formatDate(loan.tgl_jatuh_tempo) }}</td>
              <td class="py-4 px-6 text-center font-medium">
                {{ loan.tgl_kembali ? formatDate(loan.tgl_kembali) : '-' }}
              </td>
              <td class="py-4 px-6 text-center">
                <span 
                  class="font-bold px-2 py-0.5 rounded text-[10px] uppercase"
                  :class="{
                    'bg-amber-50 text-amber-700': loan.status === 'dipinjam',
                    'bg-emerald-50 text-emerald-700': loan.status === 'dikembalikan',
                    'bg-red-50 text-red-700': loan.status === 'terlambat'
                  }"
                >
                  {{ loan.status }}
                </span>
              </td>
              <td class="py-4 px-6 text-right">
                <!-- If loan has fine -->
                <div v-if="loan.denda" class="flex flex-col items-end gap-1.5">
                  <span class="font-bold text-red-600 block">Rp{{ formatCurrency(loan.denda.total_denda) }}</span>
                  <span class="text-[9px] font-semibold px-2 py-0.5 rounded bg-red-50 text-red-600 uppercase" v-if="loan.denda.status_bayar === 'belum_bayar'">
                    Belum Lunas
                  </span>
                  <span class="text-[9px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 uppercase" v-else>
                    Lunas ({{ formatDate(loan.denda.tgl_denda) }})
                  </span>
                  <button 
                    v-if="loan.denda.status_bayar === 'belum_bayar'"
                    @click="handlePayFine(loan.denda.id_denda)"
                    class="py-1 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] shadow-sm transition-all"
                  >
                    Bayar Lunas
                  </button>
                </div>
                <!-- If loan has NO fine but is active (can be returned) -->
                <div v-else-if="loan.status === 'dipinjam' || loan.status === 'terlambat'" class="flex justify-end">
                  <button 
                    @click="handleReturn(loan.id_peminjaman)"
                    class="py-1.5 px-3 rounded-lg bg-unair-blue hover:bg-unair-dark text-white font-bold text-[10px] shadow-sm transition-all"
                  >
                    Kembalikan Buku
                  </button>
                </div>
                <!-- Default state (no action needed) -->
                <span v-else class="text-slate-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center text-xs">
        <span class="text-slate-500 font-medium">
          Menampilkan {{ loans.length }} dari {{ pagination.total }} transaksi
        </span>
        <div class="flex items-center space-x-2">
          <button 
            @click="prevPage" 
            :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border bg-white disabled:opacity-40 transition-all font-semibold"
          >
            Prev
          </button>
          <span class="font-bold text-slate-700">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
          <button 
            @click="nextPage" 
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg border bg-white disabled:opacity-40 transition-all font-semibold"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const loans = ref([]);
const loading = ref(true);
const statusFilter = ref('');
const tglAwal = ref('');
const tglAkhir = ref('');

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'dipinjam', label: 'Dipinjam (Aktif)' },
  { value: 'dikembalikan', label: 'Dikembalikan' },
  { value: 'terlambat', label: 'Terlambat' }
];

const fetchLoans = async () => {
  loading.value = true;
  try {
    const res = await api.get('/peminjaman', {
      params: {
        page: pagination.value.current_page,
        status: statusFilter.value,
        tgl_awal: tglAwal.value,
        tgl_akhir: tglAkhir.value
      }
    });
    if (res.success) {
      loans.value = res.data.data;
      pagination.value = {
        current_page: res.data.current_page,
        last_page: res.data.last_page,
        total: res.data.total
      };
    }
  } catch (err) {
    console.error('Failed to load loans:', err);
  } finally {
    loading.value = false;
  }
};

const setStatusFilter = (status) => {
  statusFilter.value = status;
  pagination.value.current_page = 1;
  fetchLoans();
};

const applyFilters = () => {
  pagination.value.current_page = 1;
  fetchLoans();
};

const resetFilters = () => {
  statusFilter.value = '';
  tglAwal.value = '';
  tglAkhir.value = '';
  pagination.value.current_page = 1;
  fetchLoans();
};

const handleReturn = async (id) => {
  if (confirm('Apakah Anda yakin ingin mengembalikan buku ini?')) {
    try {
      const res = await api.post(`/pengembalian/${id}`);
      if (res.success) {
        alert(res.message);
        fetchLoans();
      }
    } catch (err) {
      alert(err.message || 'Gagal memproses pengembalian.');
    }
  }
};

const handlePayFine = async (id) => {
  if (confirm('Apakah Anda yakin denda ini telah dibayar lunas?')) {
    try {
      const res = await api.patch(`/denda/${id}/bayar`);
      if (res.success) {
        alert(res.message);
        fetchLoans();
      }
    } catch (err) {
      alert(err.message || 'Gagal memproses pembayaran denda.');
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

const prevPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--;
    fetchLoans();
  }
};

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++;
    fetchLoans();
  }
};

onMounted(() => {
  fetchLoans();
});
</script>
