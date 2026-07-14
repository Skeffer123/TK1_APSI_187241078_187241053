<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #0d3b66;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #0d3b66;
            font-size: 16pt;
        }
        .header h3 {
            margin: 3px 0 0 0;
            color: #f4d35e;
            font-size: 12pt;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            color: #666;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .meta-info td {
            border: none;
            padding: 2px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #0d3b66;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 9pt;
            border: 1px solid #0d3b66;
        }
        table.data-table td {
            border: 1px solid #dddddd;
            padding: 6px 8px;
            font-size: 8.5pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .status-badge {
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8pt;
        }
        .status-dipinjam {
            color: #d97706; /* orange */
        }
        .status-dikembalikan {
            color: #059669; /* green */
        }
        .status-terlambat {
            color: #dc2626; /* red */
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9pt;
        }
        .footer-date {
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>PERPUSTAKAAN UNIVERSITAS AIRLANGGA</h2>
        <h3>LAPORAN REKAPITULASI PEMINJAMAN BUKU</h3>
        <p>Kampus C UNAIR Mulyorejo, Surabaya | Email: info@lib.unair.ac.id | Telp: +62 31-5920152</p>
    </div>

    <table class="meta-info">
        <tr>
            <td width="15%"><strong>Periode Laporan</strong></td>
            <td width="2%">:</td>
            <td>
                @if($periode_awal && $periode_akhir)
                    {{ \Carbon\Carbon::parse($periode_awal)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($periode_akhir)->isoFormat('D MMMM Y') }}
                @else
                    Semua Data Transaksi Peminjaman
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Tanggal Cetak</strong></td>
            <td>:</td>
            <td>{{ $tgl_cetak }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="18%">Nama Anggota</th>
                <th width="12%">NIM</th>
                <th width="24%">Buku Yang Dipinjam</th>
                <th width="11%" class="text-center">Tgl Pinjam</th>
                <th width="11%" class="text-center">Tgl Tempo</th>
                <th width="11%" class="text-center">Tgl Kembali</th>
                <th width="9%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $row->anggota ? $row->anggota->nama : '-' }}</strong></td>
                    <td>{{ $row->anggota ? $row->anggota->nim : '-' }}</td>
                    <td>{{ $row->buku ? $row->buku->judul : '-' }}</td>
                    <td class="text-center">{{ $row->tgl_pinjam ? $row->tgl_pinjam->isoFormat('D MMM Y') : '-' }}</td>
                    <td class="text-center">{{ $row->tgl_jatuh_tempo ? $row->tgl_jatuh_tempo->isoFormat('D MMM Y') : '-' }}</td>
                    <td class="text-center">
                        {{ $row->tgl_kembali ? $row->tgl_kembali->isoFormat('D MMM Y') : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $row->status }}">
                            {{ strtoupper($row->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data transaksi peminjaman untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-date">Surabaya, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
        <div><strong>Kepala Perpustakaan UNAIR</strong></div>
        <br><br><br>
        <div>( _________________________ )</div>
    </div>
</body>
</html>
