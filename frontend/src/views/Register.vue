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
      <div class="my-auto max-w-md z-10 py-12 md:py-0">
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight leading-tight">
          Pendaftaran Anggota <br>
          <span class="text-unair-gold">Perpustakaan UNAIR</span>
        </h2>
        <p class="mt-4 text-slate-300 text-sm md:text-base leading-relaxed">
          Daftarkan diri Anda sebagai Anggota Perpustakaan UNAIR untuk meminjam buku secara digital, melacak transaksi, dan menikmati akses penuh ke katalog perpustakaan.
        </p>
      </div>

      <!-- Footer -->
      <div class="text-xs text-slate-400 z-10">
        &copy; 2026 Universitas Airlangga. All rights reserved.
      </div>
    </div>

    <!-- Right Column: Register Form -->
    <div class="md:w-1/2 flex items-center justify-center p-8 bg-slate-50 relative overflow-y-auto">
      <div class="w-full max-w-lg bg-white p-8 md:p-10 rounded-2xl shadow-xl border border-slate-100 relative z-10 transition-all duration-300 hover:shadow-2xl">
        
        <!-- Registration Success Notification -->
        <div v-if="successMessage" class="text-center py-6 flex flex-col items-center">
          <div 
            class="bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-100 shadow-sm flex-shrink-0"
            style="width: 64px; height: 64px;"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor" 
              stroke-width="3"
              style="width: 32px; height: 32px;"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-slate-800">Pendaftaran Berhasil!</h3>
          <p class="text-sm text-slate-500 mt-2 max-w-sm mx-auto">
            {{ successMessage }}
          </p>
          <div class="mt-8">
            <router-link 
              to="/login"
              class="inline-block py-3 px-8 rounded-xl bg-unair-dark text-white font-bold text-sm tracking-wide shadow-lg shadow-unair-dark/20 hover:bg-unair-blue hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200"
            >
              Masuk Sekarang
            </router-link>
          </div>
        </div>

        <div v-else>
          <div class="mb-8">
            <h3 class="text-2xl font-bold text-slate-800">Daftar Anggota</h3>
            <p class="text-xs text-slate-500 mt-1">Lengkapi form berikut dengan data mahasiswa UNAIR Anda yang valid.</p>
          </div>

          <!-- Error notification -->
          <div v-if="errorMessage" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-xs font-medium">
            <span class="font-bold block mb-1">Pendaftaran Gagal:</span>
            <ul class="list-disc pl-4 space-y-1">
              <li v-for="(errs, idx) in formattedErrors" :key="idx">{{ errs }}</li>
            </ul>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
              <input 
                v-model="form.nama"
                type="text" 
                required
                placeholder="Masukkan nama lengkap sesuai KTM" 
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-unair-blue focus:ring-1 focus:ring-unair-blue outline-none text-slate-800 text-sm transition-all bg-slate-50"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIM (8-16 Digit)</label>
                <input 
                  v-model="form.nim"
                  type="text" 
                  required
                  maxlength="16"
                  placeholder="Contoh: 1020113300000001" 
                  class="w-full px-4 py-2.5 rounded-xl border focus:ring-1 outline-none text-slate-800 text-sm transition-all bg-slate-50"
                  :class="nimError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-200 focus:border-unair-blue focus:ring-unair-blue'"
                />
                <span v-if="nimError" class="text-[10px] text-red-500 font-semibold mt-1 block">{{ nimError }}</span>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Telepon (Opsional)</label>
                <input 
                  v-model="form.no_telepon"
                  type="text" 
                  placeholder="Contoh: 081234567890" 
                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-unair-blue focus:ring-1 focus:ring-unair-blue outline-none text-slate-800 text-sm transition-all bg-slate-50"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Mahasiswa</label>
              <input 
                v-model="form.email"
                type="email" 
                required
                placeholder="Masukkan email aktif (e.g. nama@student.unair.ac.id)" 
                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-unair-blue focus:ring-1 focus:ring-unair-blue outline-none text-slate-800 text-sm transition-all bg-slate-50"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                <input 
                  v-model="form.password"
                  type="password" 
                  required
                  placeholder="Minimal 6 karakter" 
                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-unair-blue focus:ring-1 focus:ring-unair-blue outline-none text-slate-800 text-sm transition-all bg-slate-50"
                />
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Password</label>
                <input 
                  v-model="form.password_confirmation"
                  type="password" 
                  required
                  placeholder="Ketik ulang password" 
                  class="w-full px-4 py-2.5 rounded-xl border focus:ring-1 outline-none text-slate-800 text-sm transition-all bg-slate-50"
                  :class="passwordMismatch ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-slate-200 focus:border-unair-blue focus:ring-unair-blue'"
                />
                <span v-if="passwordMismatch" class="text-[10px] text-red-500 font-semibold mt-1 block">Password tidak cocok.</span>
              </div>
            </div>

            <div class="pt-2">
              <button 
                type="submit" 
                :disabled="loading || passwordMismatch || !!nimError"
                class="w-full py-3.5 px-4 rounded-xl bg-unair-dark text-white font-bold text-sm tracking-wide shadow-lg shadow-unair-dark/20 hover:bg-unair-blue hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 disabled:pointer-events-none transition-all duration-200"
              >
                <span v-if="loading">Mendaftarkan...</span>
                <span v-else>Daftar Sekarang</span>
              </button>
            </div>
          </form>

          <!-- Back to Login Link -->
          <div class="mt-6 text-center text-xs text-slate-500">
            Sudah terdaftar sebagai anggota? 
            <router-link to="/login" class="text-unair-blue font-bold hover:underline">
              Masuk di sini
            </router-link>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();

const form = reactive({
  nama: '',
  nim: '',
  email: '',
  no_telepon: '',
  password: '',
  password_confirmation: ''
});

const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const validationErrors = ref({});

// Client side validation computed properties
const nimError = computed(() => {
  if (!form.nim) return '';
  const cleanNim = form.nim.replace(/\s+/g, '');
  if (!/^\d+$/.test(cleanNim)) {
    return 'NIM harus berupa angka saja.';
  }
  if (cleanNim.length < 8 || cleanNim.length > 16) {
    return `NIM harus terdiri dari 8 hingga 16 digit (saat ini ${cleanNim.length} digit).`;
  }
  return '';
});

const passwordMismatch = computed(() => {
  return form.password && form.password_confirmation && form.password !== form.password_confirmation;
});

// Format raw backend errors to string list
const formattedErrors = computed(() => {
  if (!validationErrors.value) return [errorMessage.value];
  const list = [];
  Object.values(validationErrors.value).forEach(errArray => {
    if (Array.isArray(errArray)) {
      list.push(...errArray);
    } else {
      list.push(errArray);
    }
  });
  return list.length > 0 ? list : [errorMessage.value];
});

const handleRegister = async () => {
  if (nimError.value || passwordMismatch.value) return;

  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  validationErrors.value = {};

  try {
    const res = await authStore.register({
      nama: form.nama,
      nim: form.nim,
      email: form.email,
      no_telepon: form.no_telepon || null,
      password: form.password
    });
    if (res.success) {
      successMessage.value = res.message;
    }
  } catch (err) {
    errorMessage.value = err.message || 'Pendaftaran gagal. Silakan coba lagi.';
    if (err.errors) {
      validationErrors.value = err.errors;
    }
  } finally {
    loading.value = false;
  }
};
</script>
