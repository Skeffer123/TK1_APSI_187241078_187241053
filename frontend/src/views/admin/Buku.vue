<template>
  <div>
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <div>
        <h3 class="text-2xl font-bold text-slate-800">Kelola Buku</h3>
        <p class="text-xs text-slate-500 mt-1">Daftar buku perpustakaan UNAIR. Tambahkan, ubah, atau hapus koleksi buku.</p>
      </div>
      <button 
        @click="openAddModal"
        class="self-start sm:self-auto py-2.5 px-5 rounded-xl bg-unair-dark hover:bg-unair-blue text-white font-semibold text-xs tracking-wide shadow-md transition-all duration-200"
      >
        + Tambah Buku Baru
      </button>
    </div>

    <!-- Filters and Searches -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="w-full md:w-1/3 relative">
        <input 
          v-model="search"
          @input="debounceSearch"
          type="text" 
          placeholder="Cari judul, pengarang, penerbit..." 
          class="w-full pl-10 pr-4 py-2 text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue focus:ring-1 focus:ring-unair-blue text-xs transition-all"
        />
        <span class="absolute left-3.5 top-2.5 text-slate-400">
          <!-- Search icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
      </div>

      <div class="w-full md:w-48">
        <select 
          v-model="kategoriFilter" 
          @change="fetchBuku"
          class="w-full px-3 py-2 text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue focus:ring-1 focus:ring-unair-blue text-xs transition-all"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>
    </div>

    <!-- Books Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-4 px-6">Buku</th>
              <th class="py-4 px-6">Pengarang & Penerbit</th>
              <th class="py-4 px-6">Kategori</th>
              <th class="py-4 px-6 text-center">Tahun Terbit</th>
              <th class="py-4 px-6 text-center">Stok</th>
              <th class="py-4 px-6 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-if="loading" v-for="i in 5" :key="i" class="animate-pulse">
              <td colspan="6" class="py-6 px-6"><div class="h-4 bg-slate-100 rounded"></div></td>
            </tr>
            <tr v-else-if="books.length === 0">
              <td colspan="6" class="py-10 text-center text-slate-400 font-medium">Belum ada data buku.</td>
            </tr>
            <tr v-else v-for="book in books" :key="book.id_buku" class="hover:bg-slate-50/50 transition-colors">
              <td class="py-4 px-6">
                <span class="font-bold text-slate-800 block text-sm">{{ book.judul }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">ID: {{ book.id_buku }}</span>
              </td>
              <td class="py-4 px-6">
                <span class="font-semibold text-slate-700 block">{{ book.pengarang }}</span>
                <span class="text-[10px] text-slate-400 block">{{ book.penerbit || '-' }}</span>
              </td>
              <td class="py-4 px-6">
                <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full font-medium text-[10px]">
                  {{ book.kategori || 'General' }}
                </span>
              </td>
              <td class="py-4 px-6 text-center font-medium">{{ book.tahun_terbit || '-' }}</td>
              <td class="py-4 px-6 text-center">
                <span 
                  class="font-bold px-2 py-0.5 rounded text-[10px]"
                  :class="book.stok > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                >
                  {{ book.stok }}
                </span>
              </td>
              <td class="py-4 px-6 text-right space-x-2">
                <button 
                  @click="openEditModal(book)"
                  class="text-unair-blue font-bold hover:underline"
                >
                  Edit
                </button>
                <button 
                  @click="handleDelete(book.id_buku)"
                  class="text-red-500 font-bold hover:underline"
                >
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center text-xs">
        <span class="text-slate-500 font-medium">
          Menampilkan {{ books.length }} dari {{ pagination.total }} buku
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

    <!-- Add/Edit Buku Modal -->
    <div v-if="modalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        <div class="bg-unair-dark p-6 text-white flex justify-between items-center">
          <h4 class="font-bold text-base">{{ isEditMode ? 'Edit Data Buku' : 'Tambah Buku Baru' }}</h4>
          <button @click="closeModal" class="text-white hover:text-unair-gold text-lg">&times;</button>
        </div>

        <form @submit.prevent="saveBook" class="p-6 space-y-4">
          <div v-if="formErrors.length > 0" class="p-3 bg-red-50 text-red-700 text-xs font-semibold rounded-xl border border-red-100">
            <ul class="list-disc list-inside">
              <li v-for="err in formErrors" :key="err">{{ err }}</li>
            </ul>
          </div>

          <div>
            <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Judul Buku *</label>
            <input 
              v-model="form.judul" 
              type="text" 
              required
              placeholder="Masukkan judul buku" 
              class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Pengarang *</label>
              <input 
                v-model="form.pengarang" 
                type="text" 
                required
                placeholder="Pengarang buku" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Penerbit</label>
              <input 
                v-model="form.penerbit" 
                type="text" 
                placeholder="Penerbit buku" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Tahun Terbit</label>
              <input 
                v-model="form.tahun_terbit" 
                type="number" 
                placeholder="Contoh: 2021" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Kategori</label>
              <input 
                v-model="form.kategori" 
                type="text" 
                placeholder="Kategori buku" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
            <div>
              <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-2">Jumlah Stok *</label>
              <input 
                v-model="form.stok" 
                type="number" 
                min="0"
                required
                placeholder="Stok buku" 
                class="w-full px-4 py-2 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
              />
            </div>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const books = ref([]);
const categories = ref(['Teknologi', 'Sistem Informasi', 'Matematika', 'Fisika', 'Pendidikan', 'Ekonomi']);
const search = ref('');
const kategoriFilter = ref('');
const loading = ref(true);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
});

// Modal control
const modalOpen = ref(false);
const isEditMode = ref(false);
const formErrors = ref([]);
const saving = ref(false);

const form = ref({
  id_buku: null,
  judul: '',
  pengarang: '',
  penerbit: '',
  tahun_terbit: '',
  kategori: '',
  stok: 0
});

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchBuku();
  }, 500);
};

const fetchBuku = async () => {
  loading.value = true;
  try {
    const res = await api.get('/buku', {
      params: {
        page: pagination.value.current_page,
        search: search.value,
        kategori: kategoriFilter.value
      }
    });
    if (res.success) {
      books.value = res.data.data;
      pagination.value = {
        current_page: res.data.current_page,
        last_page: res.data.last_page,
        total: res.data.total
      };
    }
  } catch (err) {
    console.error('Failed to load books:', err);
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEditMode.value = false;
  formErrors.value = [];
  form.value = {
    id_buku: null,
    judul: '',
    pengarang: '',
    penerbit: '',
    tahun_terbit: '',
    kategori: '',
    stok: 0
  };
  modalOpen.value = true;
};

const openEditModal = (book) => {
  isEditMode.value = true;
  formErrors.value = [];
  form.value = { ...book };
  modalOpen.value = true;
};

const closeModal = () => {
  modalOpen.value = false;
};

const saveBook = async () => {
  saving.value = true;
  formErrors.value = [];

  try {
    let res;
    if (isEditMode.value) {
      res = await api.put(`/buku/${form.value.id_buku}`, form.value);
    } else {
      res = await api.post('/buku', form.value);
    }
    
    if (res.success) {
      alert(res.message);
      closeModal();
      fetchBuku();
    }
  } catch (err) {
    if (err.errors) {
      formErrors.value = Object.values(err.errors).flat();
    } else {
      formErrors.value = [err.message || 'Gagal menyimpan data buku.'];
    }
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus buku ini dari database? Hapus tidak dapat dilakukan jika terdapat transaksi peminjaman aktif.')) {
    try {
      const res = await api.delete(`/buku/${id}`);
      if (res.success) {
        alert(res.message);
        fetchBuku();
      }
    } catch (err) {
      alert(err.message || 'Gagal menghapus buku.');
    }
  }
};

const prevPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--;
    fetchBuku();
  }
};

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++;
    fetchBuku();
  }
};

onMounted(() => {
  fetchBuku();
});
</script>
