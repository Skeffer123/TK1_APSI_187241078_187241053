<template>
  <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row">
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-unair-dark text-white flex-shrink-0 flex flex-col shadow-xl">
      <!-- Logo / Header -->
      <div class="p-6 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-xl bg-unair-gold flex items-center justify-center text-unair-dark font-extrabold shadow-md">
            M
          </div>
          <div>
            <h1 class="font-extrabold text-sm tracking-wide leading-tight text-white">PERPUS UNAIR</h1>
            <span class="text-[10px] text-unair-gold uppercase tracking-widest font-bold">Anggota Panel</span>
          </div>
        </div>
      </div>
      
      <!-- Navigation Links -->
      <nav class="flex-1 p-4 space-y-1">
        <router-link 
          to="/anggota/dashboard" 
          class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-white/5"
          active-class="bg-unair-gold !text-unair-dark font-bold shadow-md shadow-unair-gold/20"
        >
          <span class="text-sm">Dashboard</span>
        </router-link>
        
        <router-link 
          to="/anggota/katalog" 
          class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-white/5"
          active-class="bg-unair-gold !text-unair-dark font-bold shadow-md shadow-unair-gold/20"
        >
          <span class="text-sm">Katalog Buku</span>
        </router-link>
        
        <router-link 
          to="/anggota/peminjaman" 
          class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 hover:bg-white/5"
          active-class="bg-unair-gold !text-unair-dark font-bold shadow-md shadow-unair-gold/20"
        >
          <span class="text-sm">Peminjaman Saya</span>
        </router-link>
      </nav>

      <!-- Logout / User Section -->
      <div class="p-4 border-t border-white/10 bg-black/25">
        <div class="flex items-center justify-between">
          <div class="truncate mr-3">
            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">NIM: {{ authStore.user?.nim }}</p>
            <p class="text-sm font-bold truncate text-white">{{ authStore.user?.nama }}</p>
          </div>
          <button 
            @click="handleLogout" 
            class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white text-xs font-semibold transition-all duration-200"
            title="Logout"
          >
            Logout
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0">
      <!-- Navbar / Header -->
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-6 md:px-8 shadow-sm">
        <h2 class="font-bold text-slate-800 text-base md:text-lg tracking-tight">
          Perpustakaan Universitas Airlangga
        </h2>
        <div class="flex items-center space-x-2 bg-slate-100 px-3 py-1.5 rounded-lg">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
          <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wide">Mahasiswa Aktif</span>
        </div>
      </header>

      <!-- Scrollable content -->
      <div class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  if (confirm('Apakah Anda yakin ingin logout dari akun perpustakaan Anda?')) {
    await authStore.logout();
    router.push('/login');
  }
};
</script>
