<template>
  <div class="min-h-screen flex flex-col md:flex-row bg-slate-50">
    <!-- Left Column: Branding (UNAIR Blue and Gold Gradient) -->
    <div class="md:w-1/2 bg-gradient-to-br from-unair-dark via-unair-blue to-slate-900 flex flex-col justify-between p-8 text-white relative overflow-hidden">
      <!-- Decorative circle backgrounds -->
      <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-unair-gold/10 blur-3xl"></div>
      <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-unair-gold/10 blur-3xl"></div>

      <!-- Header Logo -->
      <div class="flex items-center space-x-3 z-10">
        <div class="w-10 h-10 rounded-xl bg-unair-gold flex items-center justify-center text-unair-dark font-extrabold shadow-lg">
          U
        </div>
        <div>
          <span class="font-extrabold tracking-wider text-base">UNIVERSITAS AIRLANGGA</span>
          <p class="text-[10px] text-unair-gold font-bold tracking-widest uppercase">Excellence With Morality</p>
        </div>
      </div>

      <!-- Center content -->
      <div class="my-auto max-w-md z-10">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight leading-tight">
          Sistem Informasi <br>
          <span class="text-unair-gold">Perpustakaan UNAIR</span>
        </h2>
        <p class="mt-4 text-slate-300 text-sm md:text-base leading-relaxed">
          Selamat datang di platform peminjaman buku digital Universitas Airlangga. Hubungkan diri Anda ke akses ilmu pengetahuan tanpa batas secara efisien.
        </p>
      </div>

      <!-- Footer -->
      <div class="text-xs text-slate-400 z-10">
        &copy; 2026 Universitas Airlangga. All rights reserved.
      </div>
    </div>

    <!-- Right Column: Login Form -->
    <div class="md:w-1/2 flex items-center justify-center p-8 bg-slate-50 relative">
      <div class="w-full max-w-md bg-white p-8 md:p-10 rounded-2xl shadow-xl border border-slate-100 relative z-10 transition-all duration-300 hover:shadow-2xl">
        <div class="mb-8">
          <h3 class="text-2xl font-bold text-slate-800">Masuk Akun</h3>
          <p class="text-xs text-slate-500 mt-1">Masukkan username, email, atau NIM perpustakaan Anda.</p>
        </div>

        <!-- Error notification -->
        <div v-if="errorMessage" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-xs font-medium flex items-start space-x-2">
          <span class="font-bold">Error:</span>
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Username / Email / NIM</label>
            <input 
              v-model="loginKey"
              type="text" 
              required
              placeholder="Masukkan identitas login" 
              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-unair-blue focus:ring-1 focus:ring-unair-blue outline-none text-slate-800 text-sm transition-all bg-slate-50"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Password</label>
            <input 
              v-model="password"
              type="password" 
              required
              placeholder="Masukkan password" 
              class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-unair-blue focus:ring-1 focus:ring-unair-blue outline-none text-slate-800 text-sm transition-all bg-slate-50"
            />
          </div>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full py-3.5 px-4 rounded-xl bg-unair-dark text-white font-bold text-sm tracking-wide shadow-lg shadow-unair-dark/20 hover:bg-unair-blue hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200"
          >
            <span v-if="loading">Menghubungkan...</span>
            <span v-else>Masuk Ke Sistem</span>
          </button>
        </form>

        <!-- Register Link -->
        <div class="mt-6 text-center text-xs text-slate-500">
          Belum terdaftar sebagai anggota? 
          <router-link to="/register" class="text-unair-blue font-bold hover:underline">
            Daftar Sekarang
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const loginKey = ref('');
const password = ref('');
const loading = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';
  
  try {
    const res = await authStore.login(loginKey.value, password.value);
    if (res.success) {
      if (res.data.role === 'admin') {
        router.push('/admin/dashboard');
      } else {
        router.push('/anggota/dashboard');
      }
    }
  } catch (err) {
    errorMessage.value = err.message || 'Login gagal. Silakan coba lagi.';
  } finally {
    loading.value = false;
  }
};
</script>
