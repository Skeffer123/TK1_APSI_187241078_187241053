import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    role: localStorage.getItem('role') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
    loading: false,
    error: null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.role === 'admin',
    isAnggota: (state) => state.role === 'anggota',
  },

  actions: {
    async login(loginKey, password) {
      this.loading = true;
      this.error = null;
      try {
        const res = await api.post('/login', { login_key: loginKey, password });
        if (res.success) {
          this.token = res.data.token;
          this.role = res.data.role;
          this.user = res.data.user;

          localStorage.setItem('token', this.token);
          localStorage.setItem('role', this.role);
          localStorage.setItem('user', JSON.stringify(this.user));
        }
        return res;
      } catch (err) {
        this.error = err.message || 'Login gagal. Periksa kembali kredensial Anda.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async register(userData) {
      this.loading = true;
      this.error = null;
      try {
        const res = await api.post('/register', userData);
        return res;
      } catch (err) {
        this.error = err.message || 'Registrasi gagal. Silakan coba lagi.';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await api.post('/logout');
      } catch (err) {
        console.error('Server logout failed:', err);
      } finally {
        this.token = null;
        this.role = null;
        this.user = null;
        
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('user');
      }
    },

    async fetchMe() {
      try {
        const res = await api.get('/me');
        if (res.success) {
          this.role = res.data.role;
          this.user = res.data.user;
          localStorage.setItem('role', this.role);
          localStorage.setItem('user', JSON.stringify(this.user));
        }
      } catch (err) {
        this.logout();
      }
    }
  }
});
