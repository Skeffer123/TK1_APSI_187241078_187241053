<template>
  <div>
    <!-- Title Section -->
    <div class="mb-8">
      <h3 class="text-2xl font-bold text-slate-800">Katalog Buku</h3>
      <p class="text-xs text-slate-500 mt-1">Cari dan pinjam buku referensi akademik untuk menunjang perkuliahan Anda.</p>
    </div>

    <!-- Filters and Searches -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="w-full md:w-1/3 relative">
        <input 
          v-model="search"
          @input="debounceSearch"
          type="text" 
          placeholder="Cari judul buku, pengarang, penerbit..." 
          class="w-full pl-10 pr-4 py-2.5 text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue focus:ring-1 focus:ring-unair-blue text-xs transition-all"
        />
        <span class="absolute left-3.5 top-3 text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
      </div>

      <div class="w-full md:w-48">
        <select 
          v-model="kategoriFilter" 
          @change="fetchBuku"
          class="w-full px-3 py-2.5 text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue focus:ring-1 focus:ring-unair-blue text-xs bg-white"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-pulse">
      <div v-for="i in 6" :key="i" class="h-48 bg-white rounded-2xl border border-slate-100"></div>
    </div>

    <!-- Grid of Books -->
    <div v-else-if="books.length === 0" class="text-center py-12 text-slate-400 font-medium text-sm">
      Buku tidak ditemukan. Coba gunakan kata kunci pencarian yang lain.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="book in books" 
        :key="book.id_buku" 
        class="bg-white rounded-2xl border border-slate-100 hover:border-slate-200 hover:-translate-y-1 p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-all duration-300"
      >
        <div>
          <!-- Category & Status Badge -->
          <div class="flex justify-between items-start gap-2">
            <span class="px-2.5 py-0.5 rounded-full bg-unair-blue/10 text-unair-blue font-bold text-[9px] uppercase">
              {{ book.kategori || 'General' }}
            </span>
            <span 
              class="font-bold text-[9px] px-2 py-0.5 rounded"
              :class="book.stok > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
            >
              {{ book.stok > 0 ? `Stok: ${book.stok}` : 'Stok Habis' }}
            </span>
          </div>

          <!-- Book Info -->
          <h4 class="font-extrabold text-slate-800 text-base mt-4 line-clamp-2" :title="book.judul">
            {{ book.judul }}
          </h4>
          <span class="text-xs text-slate-500 font-semibold mt-1 block">Oleh: {{ book.pengarang }}</span>
          
          <div class="mt-4 pt-4 border-t border-slate-50 text-[10px] text-slate-400 space-y-1">
            <p>Penerbit: <strong class="text-slate-600">{{ book.penerbit || '-' }}</strong></p>
            <p>Tahun Terbit: <strong class="text-slate-600">{{ book.tahun_terbit || '-' }}</strong></p>
          </div>
        </div>

        <!-- Action Borrow Button -->
        <button 
          @click="handleBorrow(book)"
          :disabled="book.stok <= 0 || borrowing"
          class="w-full mt-6 py-2 px-4 rounded-xl bg-unair-dark hover:bg-unair-blue disabled:bg-slate-100 disabled:text-slate-400 font-bold text-xs tracking-wide text-white shadow-md disabled:shadow-none transition-all duration-200"
        >
          <span v-if="book.stok <= 0">Stok Habis</span>
          <span v-else>Pinjam Buku Ini</span>
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="mt-8 flex justify-between items-center bg-white p-4 rounded-2xl border border-slate-100 text-xs shadow-sm">
      <span class="text-slate-500 font-medium">
        Menampilkan {{ books.length }} dari {{ pagination.total }} buku
      </span>
      <div class="flex items-center space-x-2">
        <button 
          @click="prevPage" 
          :disabled="pagination.current_page === 1"
          class="px-3 py-1.5 rounded-lg border bg-white disabled:opacity-40 font-semibold transition-all"
        >
          Prev
        </button>
        <span class="font-bold text-slate-700">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <button 
          @click="nextPage" 
          :disabled="pagination.current_page === pagination.last_page"
          class="px-3 py-1.5 rounded-lg border bg-white disabled:opacity-40 font-semibold transition-all"
        >
          Next
        </button>
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
const borrowing = ref(false);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0
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
    console.error('Failed to load catalog:', err);
  } finally {
    loading.value = false;
  }
};

const handleBorrow = async (book) => {
  if (confirm(`Apakah Anda yakin ingin meminjam buku "${book.judul}"? Peminjaman berlaku selama 7 hari.`)) {
    borrowing.value = true;
    try {
      const res = await api.post('/peminjaman', { id_buku: book.id_buku });
      if (res.success) {
        alert(res.message);
        fetchBuku(); // Refresh catalog to update stock numbers
      }
    } catch (err) {
      alert(err.message || 'Gagal meminjam buku. Periksa apakah status keanggotaan Anda aktif atau Anda telah meminjam buku yang sama.');
    } finally {
      borrowing.value = false;
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
