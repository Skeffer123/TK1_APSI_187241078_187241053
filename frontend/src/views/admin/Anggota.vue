<template>
  <div>
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <div>
        <h3 class="text-2xl font-bold text-slate-800">Kelola Anggota</h3>
        <p class="text-xs text-slate-500 mt-1">Daftar anggota perpustakaan UNAIR. Tambahkan, ubah, nonaktifkan, atau lihat histori pinjaman.</p>
      </div>
      <button 
        @click="openRegisterModal"
        class="self-start sm:self-auto py-2.5 px-5 rounded-xl bg-unair-dark hover:bg-unair-blue text-white font-semibold text-xs tracking-wide shadow-md transition-all duration-200"
      >
        + Registrasi Anggota Baru
      </button>
    </div>

    <!-- Filters and Searches -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-6 flex gap-4 items-center">
      <div class="w-full md:w-1/3 relative">
        <input 
          v-model="search"
          @input="debounceSearch"
          type="text" 
          placeholder="Cari nama, NIM, email anggota..." 
          class="w-full pl-10 pr-4 py-2 text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue focus:ring-1 focus:ring-unair-blue text-xs transition-all"
        />
        <span class="absolute left-3.5 top-2.5 text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
      </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-4 px-6">Anggota</th>
              <th class="py-4 px-6">NIM</th>
              <th class="py-4 px-6">Kontak</th>
              <th class="py-4 px-6 text-center">Status</th>
              <th class="py-4 px-6 text-center">Tanggal Daftar</th>
              <th class="py-4 px-6 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
              <td colspan="6" class="py-6 px-6"><div class="h-4 bg-slate-100 rounded"></div></td>
            </tr>
            <tr v-else-if="members.length === 0">
              <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Belum ada data anggota.</td>
            </tr>
            <tr v-else v-for="member in members" :key="member.id_anggota" class="hover:bg-slate-50/50 transition-colors">
              <td class="py-4 px-6">
                <span class="font-bold text-slate-800 block text-sm">{{ member.nama }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">ID: {{ member.id_anggota }}</span>
              </td>
              <td class="py-4 px-6 font-semibold">{{ member.nim }}</td>
              <td class="py-4 px-6">
                <span class="block">{{ member.email }}</span>
                <span class="text-[10px] text-slate-400 block">{{ member.no_telepon || '-' }}</span>
              </td>
              <td class="py-4 px-6 text-center">
                <span 
                  class="font-bold px-2.5 py-1 rounded text-[10px]"
                  :class="member.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                >
                  {{ member.status.toUpperCase() }}
                </span>
              </td>
              <td class="py-4 px-6 text-center font-medium">{{ formatDate(member.tanggal_daftar) }}</td>
              <td class="py-4 px-6 text-right space-x-2">
                <button 
                  @click="viewDetails(member.id_anggota)"
                  class="text-unair-blue font-bold hover:underline"
                >
                  Detail & Histori
                </button>
                <button 
                  @click="openEditModal(member)"
                  class="text-slate-600 font-bold hover:underline"
                >
                  Edit
                </button>
                <button 
                  v-if="member.status === 'aktif'"
                  @click="handleDeactivate(member.id_anggota)"
                  class="text-red-500 font-bold hover:underline"
                >
                  Nonaktifkan
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center text-xs">
        <span class="text-slate-500 font-medium">
          Menampilkan {{ members.length }} dari {{ pagination.total }} anggota
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

    <!-- Register/Edit Anggota Modal -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        <div class="bg-unair-dark p-6 text-white flex justify-between items-center">
          <h4 class="font-bold text-base">{{ isEditMode ? 'Edit Data Anggota' : 'Registrasi Anggota Baru' }}</h4>
          <button @click="closeModal" class="text-white hover:text-unair-gold text-lg">&times;</button>
        </div>

        <form @submit.prevent="saveMember" class="p-6 space-y-4">
          <div v-if="formErrors.length > 0" class="p-3 bg-red-50 text-red-700 text-xs font-semibold rounded-xl border border-red-100">
            <ul class="list-disc list-inside">
              <li v-for="err in formErrors" :key="err">{{ err }}</li>
            </ul>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Lengkap *</label>
            <input 
              v-model="form.nama" 
              type="text" 
              required
              placeholder="Masukkan nama lengkap anggota" 
              class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">NIM (16 Karakter) *</label>
              <input 
                v-model="form.nim" 
                type="text" 
                maxlength="16"
                required
                placeholder="Masukkan NIM 16 digit" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">No. Telepon</label>
              <input 
                v-model="form.no_telepon" 
                type="text" 
                placeholder="Masukkan nomor telepon" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Email *</label>
            <input 
              v-model="form.email" 
              type="email" 
              required
              placeholder="Masukkan email unair (e.g. mhs@fst.unair.ac.id)" 
              class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
            />
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">
              Password {{ isEditMode ? '(Kosongkan jika tidak diubah)' : '*' }}
            </label>
            <input 
              v-model="form.password" 
              type="password" 
              :required="!isEditMode"
              placeholder="Masukkan password akun" 
              class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
            />
          </div>

          <div v-if="isEditMode">
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Status Akun</label>
            <select 
              v-model="form.status"
              class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs bg-white"
            >
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>

          <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100">
            <button 
              type="button" 
              @click="closeModal"
              class="px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="saving"
              class="px-4 py-2 rounded-xl bg-unair-dark hover:bg-unair-blue text-white text-xs font-bold shadow-md disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Member Details & History Drawer/Modal -->
    <div v-if="detailOpen" class="fixed inset-0 z-50 bg-black/50 flex justify-end">
      <div class="w-full max-w-2xl bg-white h-screen flex flex-col shadow-2xl transition-all duration-300">
        <!-- Header -->
        <div class="bg-unair-dark p-6 text-white flex justify-between items-center">
          <div>
            <h4 class="font-bold text-base">Profil & Histori Anggota</h4>
            <p class="text-[10px] text-unair-gold font-semibold uppercase tracking-wider mt-1">NIM: {{ selectedMember?.nim }}</p>
          </div>
          <button @click="closeDetail" class="text-white hover:text-unair-gold text-2xl font-bold">&times;</button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50">
          <!-- Profil Card -->
          <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm grid grid-cols-2 gap-4">
            <div>
              <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Nama Lengkap</span>
              <span class="text-sm font-bold text-slate-800 mt-1 block">{{ selectedMember?.nama }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Status Keanggotaan</span>
              <span 
                class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                :class="selectedMember?.status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
              >
                {{ selectedMember?.status }}
              </span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Email</span>
              <span class="text-xs text-slate-700 font-medium mt-1 block">{{ selectedMember?.email }}</span>
            </div>
            <div>
              <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">No. Telepon</span>
              <span class="text-xs text-slate-700 font-medium mt-1 block">{{ selectedMember?.no_telepon || '-' }}</span>
            </div>
            <div class="col-span-2 border-t border-slate-100 pt-3">
              <span class="text-[10px] text-slate-400 block font-semibold">Didaftarkan oleh admin: {{ selectedMember?.admin?.nama || 'System Seeder' }}</span>
              <span class="text-[10px] text-slate-400 block mt-0.5">Tanggal Bergabung: {{ formatDate(selectedMember?.tanggal_daftar) }}</span>
            </div>
          </div>

          <!-- Histori Peminjaman -->
          <div>
            <h5 class="text-sm font-bold text-slate-800 mb-3">Histori Peminjaman Buku</h5>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase border-b border-slate-100">
                    <th class="py-3 px-4">Buku</th>
                    <th class="py-3 px-4 text-center">Pinjam</th>
                    <th class="py-3 px-4 text-center">Kembali</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4 text-right">Denda</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px] text-slate-700">
                  <tr v-if="!selectedMember?.peminjaman || selectedMember.peminjaman.length === 0">
                    <td colspan="5" class="py-6 text-center text-slate-400">Belum ada transaksi peminjaman.</td>
                  </tr>
                  <tr v-else v-for="loan in selectedMember.peminjaman" :key="loan.id_peminjaman" class="hover:bg-slate-50/50">
                    <td class="py-3 px-4 font-bold text-slate-800">{{ loan.buku_judul }}</td>
                    <td class="py-3 px-4 text-center">{{ formatDate(loan.tgl_pinjam) }}</td>
                    <td class="py-3 px-4 text-center">{{ loan.tgl_kembali ? formatDate(loan.tgl_kembali) : 'Belum Kembali' }}</td>
                    <td class="py-3 px-4 text-center uppercase font-bold">
                      <span 
                        :class="{
                          'text-amber-600': loan.status === 'dipinjam',
                          'text-emerald-600': loan.status === 'dikembalikan',
                          'text-red-600': loan.status === 'terlambat'
                        }"
                      >
                        {{ loan.status }}
                      </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                      <div v-if="loan.denda">
                        <span class="block font-bold" :class="loan.denda.status_bayar === 'lunas' ? 'text-emerald-600' : 'text-red-600'">
                          Rp{{ formatCurrency(loan.denda.total_denda) }}
                        </span>
                        <span class="text-[9px] text-slate-400 block font-semibold">
                          {{ loan.denda.status_bayar === 'lunas' ? 'LUNAS' : 'PENDING' }}
                        </span>
                      </div>
                      <span v-else class="text-slate-400 font-medium">-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const members = ref([]);
const search = ref('');
const loading = ref(true);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

// Modal registers control
const modalOpen = ref(false);
const isEditMode = ref(false);
const formErrors = ref([]);
const saving = ref(false);

const form = ref({
  id_anggota: null,
  nama: '',
  nim: '',
  email: '',
  no_telepon: '',
  password: '',
  status: 'aktif'
});

// Detail drawer control
const detailOpen = ref(false);
const selectedMember = ref(null);

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchMembers();
  }, 500);
};

const fetchMembers = async () => {
  loading.value = true;
  try {
    const res = await api.get('/anggota', {
      params: {
        page: pagination.value.current_page,
        search: search.value
      }
    });
    if (res.success) {
      members.value = res.data.data;
      pagination.value = {
        current_page: res.data.current_page,
        last_page: res.data.last_page,
        total: res.data.total
      };
    }
  } catch (err) {
    console.error('Failed to load members:', err);
  } finally {
    loading.value = false;
  }
};

const openRegisterModal = () => {
  isEditMode.value = false;
  formErrors.value = [];
  form.value = {
    id_anggota: null,
    nama: '',
    nim: '',
    email: '',
    no_telepon: '',
    password: '',
    status: 'aktif'
  };
  modalOpen.value = true;
};

const openEditModal = (member) => {
  isEditMode.value = true;
  formErrors.value = [];
  form.value = {
    id_anggota: member.id_anggota,
    nama: member.nama,
    nim: member.nim,
    email: member.email,
    no_telepon: member.no_telepon || '',
    password: '',
    status: member.status
  };
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const saveMember = async () => {
  saving.value = true;
  formErrors.value = [];

  try {
    let res;
    if (isEditMode.value) {
      res = await api.put(`/anggota/${form.value.id_anggota}`, form.value);
    } else {
      res = await api.post('/anggota', form.value);
    }
    
    if (res.success) {
      alert(res.message);
      closeModal();
      fetchMembers();
    }
  } catch (err) {
    if (err.errors) {
      formErrors.value = Object.values(err.errors).flat();
    } else {
      formErrors.value = [err.message || 'Gagal menyimpan data anggota.'];
    }
  } finally {
    saving.value = false;
  }
};

const handleDeactivate = async (id) => {
  if (confirm('Apakah Anda yakin ingin menonaktifkan keanggotaan ini? Anggota nonaktif tidak akan bisa melakukan peminjaman buku baru.')) {
    try {
      const res = await api.patch(`/anggota/${id}/nonaktifkan`);
      if (res.success) {
        alert(res.message);
        fetchMembers();
      }
    } catch (err) {
      alert(err.message || 'Gagal menonaktifkan anggota.');
    }
  }
};

const viewDetails = async (id) => {
  try {
    const res = await api.get(`/anggota/${id}`);
    if (res.success) {
      selectedMember.value = res.data;
      detailOpen.value = true;
    }
  } catch (err) {
    alert('Gagal mengambil data detail anggota.');
  }
};

const closeDetail = () => {
  detailOpen.value = false;
  selectedMember.value = null;
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
    fetchMembers();
  }
};

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++;
    fetchMembers();
  }
};

onMounted(() => {
  fetchMembers();
});
</script>
