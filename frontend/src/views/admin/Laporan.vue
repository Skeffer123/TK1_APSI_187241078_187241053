<template>
  <div>
    <!-- Title Section -->
    <div class="mb-8">
      <h3 class="text-2xl font-bold text-slate-800">Laporan Rekapitulasi</h3>
      <p class="text-xs text-slate-500 mt-1">Generate rekapitulasi data perpustakaan UNAIR dan cetak langsung dalam format PDF resmi.</p>
    </div>

    <!-- Controls Card -->
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm mb-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Jenis Laporan *</label>
          <select 
            v-model="reportType"
            class="w-full px-4 py-2.5 text-slate-800 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs bg-white"
          >
            <option value="buku">Rekapitulasi Data Buku</option>
            <option value="anggota">Rekapitulasi Data Anggota</option>
            <option value="peminjaman">Rekapitulasi Peminjaman Buku</option>
            <option value="denda">Rekapitulasi Denda Keterlambatan</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Periode Mulai</label>
          <input 
            v-model="dateStart"
            type="date"
            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Periode Akhir</label>
          <input 
            v-model="dateEnd"
            type="date"
            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-unair-blue text-xs"
          />
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-6 flex flex-wrap gap-2 justify-end border-t border-slate-100 pt-5">
        <button 
          @click="generatePreview"
          :disabled="generating"
          class="py-2.5 px-5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-bold text-xs shadow-sm transition-all"
        >
          {{ generating ? 'Generating...' : 'Tampilkan Preview' }}
        </button>
        <button 
          @click="downloadPdf"
          :disabled="generating || !previewData"
          class="py-2.5 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/10 disabled:opacity-50 disabled:pointer-events-none transition-all flex items-center space-x-1.5"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          <span>Export ke PDF</span>
        </button>
      </div>
    </div>

    <!-- Preview Section -->
    <div v-if="previewData" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden animate-fade-in">
      <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
        <div>
          <h4 class="font-bold text-slate-800 text-sm">Preview Laporan ({{ previewData.items.length }} Records)</h4>
          <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">
            {{ reportType }} | 
            <span v-if="dateStart && dateEnd">{{ formatDate(dateStart) }} - {{ formatDate(dateEnd) }}</span>
            <span v-else>Semua Periode</span>
          </p>
        </div>
      </div>

      <!-- Preview Table -->
      <div class="overflow-x-auto">
        <!-- 1. Buku Laporan Preview -->
        <table v-if="reportType === 'buku'" class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-3 px-6">Judul Buku</th>
              <th class="py-3 px-6">Pengarang</th>
              <th class="py-3 px-6">Penerbit</th>
              <th class="py-3 px-6 text-center">Tahun Terbit</th>
              <th class="py-3 px-6 text-center">Stok</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-for="book in previewData.items" :key="book.id_buku">
              <td class="py-3 px-6 font-bold text-slate-800">{{ book.judul }}</td>
              <td class="py-3 px-6 font-semibold">{{ book.pengarang }}</td>
              <td class="py-3 px-6">{{ book.penerbit || '-' }}</td>
              <td class="py-3 px-6 text-center">{{ book.tahun_terbit || '-' }}</td>
              <td class="py-3 px-6 text-center">{{ book.stok }}</td>
            </tr>
          </tbody>
        </table>

        <!-- 2. Anggota Laporan Preview -->
        <table v-else-if="reportType === 'anggota'" class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-3 px-6">Nama Anggota</th>
              <th class="py-3 px-6">NIM</th>
              <th class="py-3 px-6">Email</th>
              <th class="py-3 px-6 text-center">Status</th>
              <th class="py-3 px-6 text-center">Tgl Daftar</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-for="member in previewData.items" :key="member.id_anggota">
              <td class="py-3 px-6 font-bold text-slate-800">{{ member.nama }}</td>
              <td class="py-3 px-6 font-semibold">{{ member.nim }}</td>
              <td class="py-3 px-6">{{ member.email }}</td>
              <td class="py-3 px-6 text-center">
                <span class="font-bold uppercase" :class="member.status === 'aktif' ? 'text-emerald-600' : 'text-red-500'">
                  {{ member.status }}
                </span>
              </td>
              <td class="py-3 px-6 text-center">{{ formatDate(member.tanggal_daftar) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- 3. Peminjaman Laporan Preview -->
        <table v-else-if="reportType === 'peminjaman'" class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-3 px-6">Peminjam / NIM</th>
              <th class="py-3 px-6">Buku Yang Dipinjam</th>
              <th class="py-3 px-6 text-center">Tgl Pinjam</th>
              <th class="py-3 px-6 text-center">Tgl Tempo</th>
              <th class="py-3 px-6 text-center">Tgl Kembali</th>
              <th class="py-3 px-6 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-for="loan in previewData.items" :key="loan.id_peminjaman">
              <td class="py-3 px-6">
                <span class="font-bold text-slate-800 block">{{ loan.anggota?.nama || '-' }}</span>
                <span class="text-[9px] text-slate-400 block">NIM: {{ loan.anggota?.nim || '-' }}</span>
              </td>
              <td class="py-3 px-6 font-semibold">{{ loan.buku?.judul || '-' }}</td>
              <td class="py-3 px-6 text-center">{{ formatDate(loan.tgl_pinjam) }}</td>
              <td class="py-3 px-6 text-center">{{ formatDate(loan.tgl_jatuh_tempo) }}</td>
              <td class="py-3 px-6 text-center">{{ loan.tgl_kembali ? formatDate(loan.tgl_kembali) : 'Belum Kembali' }}</td>
              <td class="py-3 px-6 text-center font-bold uppercase" :class="loan.status === 'dikembalikan' ? 'text-emerald-600' : 'text-amber-600'">
                {{ loan.status }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- 4. Denda Laporan Preview -->
        <table v-else-if="reportType === 'denda'" class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
              <th class="py-3 px-6">Anggota / NIM</th>
              <th class="py-3 px-6">Buku Terkait</th>
              <th class="py-3 px-6 text-center">Terlambat</th>
              <th class="py-3 px-6 text-right">Total Denda</th>
              <th class="py-3 px-6 text-center">Status Bayar</th>
              <th class="py-3 px-6 text-center">Tgl Lunas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
            <tr v-for="fine in previewData.items" :key="fine.id_denda">
              <td class="py-3 px-6">
                <span class="font-bold text-slate-800 block">{{ fine.peminjaman?.anggota?.nama || '-' }}</span>
                <span class="text-[9px] text-slate-400 block">NIM: {{ fine.peminjaman?.anggota?.nim || '-' }}</span>
              </td>
              <td class="py-3 px-6 font-semibold">{{ fine.peminjaman?.buku?.judul || '-' }}</td>
              <td class="py-3 px-6 text-center">{{ fine.jumlah_hari }} Hari</td>
              <td class="py-3 px-6 text-right font-bold text-red-600">Rp{{ formatCurrency(fine.total_denda) }}</td>
              <td class="py-3 px-6 text-center">
                <span class="font-bold uppercase" :class="fine.status_bayar === 'lunas' ? 'text-emerald-600' : 'text-red-500'">
                  {{ fine.status_bayar }}
                </span>
              </td>
              <td class="py-3 px-6 text-center">{{ fine.tgl_denda ? formatDate(fine.tgl_denda) : '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../services/api';
import axios from 'axios';

const reportType = ref('buku');
const dateStart = ref('');
const dateEnd = ref('');
const previewData = ref(null);
const generating = ref(false);

const generatePreview = async () => {
  generating.value = true;
  previewData.value = null;
  try {
    const res = await api.get(`/laporan/${reportType.value}`, {
      params: {
        periode_awal: dateStart.value,
        periode_akhir: dateEnd.value
      }
    });
    if (res.success) {
      previewData.value = res.data;
    }
  } catch (err) {
    alert(err.message || 'Gagal membuat preview laporan.');
  } finally {
    generating.value = false;
  }
};

const downloadPdf = async () => {
  try {
    const token = localStorage.getItem('token');
    
    // Use raw axios to download as blob
    const res = await axios({
      url: `http://127.0.0.1:8000/api/laporan/${reportType.value}/pdf`,
      method: 'GET',
      responseType: 'blob',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/pdf'
      },
      params: {
        periode_awal: dateStart.value,
        periode_akhir: dateEnd.value
      }
    });

    const url = window.URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `laporan_${reportType.value}_${Date.now()}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (err) {
    console.error(err);
    alert('Gagal mengunduh file PDF.');
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
</script>
