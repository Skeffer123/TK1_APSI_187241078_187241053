import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Lazy load components
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';

// Admin Views
import AdminLayout from '../components/AdminLayout.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminBuku from '../views/admin/Buku.vue';
import AdminAnggota from '../views/admin/Anggota.vue';
import AdminTransaksi from '../views/admin/Transaksi.vue';
import AdminLaporan from '../views/admin/Laporan.vue';

// Anggota Views
import AnggotaLayout from '../components/AnggotaLayout.vue';
import AnggotaDashboard from '../views/anggota/Dashboard.vue';
import AnggotaKatalog from '../views/anggota/Katalog.vue';
import AnggotaPeminjaman from '../views/anggota/Peminjaman.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guestOnly: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { guestOnly: true }
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboard },
      { path: 'buku', name: 'AdminBuku', component: AdminBuku },
      { path: 'anggota', name: 'AdminAnggota', component: AdminAnggota },
      { path: 'transaksi', name: 'AdminTransaksi', component: AdminTransaksi },
      { path: 'laporan', name: 'AdminLaporan', component: AdminLaporan }
    ]
  },
  {
    path: '/anggota',
    component: AnggotaLayout,
    meta: { requiresAuth: true, role: 'anggota' },
    children: [
      { path: '', redirect: '/anggota/dashboard' },
      { path: 'dashboard', name: 'AnggotaDashboard', component: AnggotaDashboard },
      { path: 'katalog', name: 'AnggotaKatalog', component: AnggotaKatalog },
      { path: 'peminjaman', name: 'AnggotaPeminjaman', component: AnggotaPeminjaman }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/login'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;
  const userRole = authStore.role;

  if (to.meta.requiresAuth) {
    if (!isAuthenticated) {
      return next({ name: 'Login' });
    }
    
    // Check role specific access
    if (to.meta.role && to.meta.role !== userRole) {
      if (userRole === 'admin') {
        return next({ name: 'AdminDashboard' });
      } else {
        return next({ name: 'AnggotaDashboard' });
      }
    }
    
    next();
  } else if (to.meta.guestOnly && isAuthenticated) {
    // If logged in, redirect away from login page
    if (userRole === 'admin') {
      next({ name: 'AdminDashboard' });
    } else {
      next({ name: 'AnggotaDashboard' });
    }
  } else {
    next();
  }
});

export default router;
