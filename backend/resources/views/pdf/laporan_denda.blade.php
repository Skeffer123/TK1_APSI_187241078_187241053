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
        .text-right {
            text-align: right;
        }
        .status-badge {
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8pt;
        }
        .status-belum_bayar {
            color: #dc2626; /* red */
        }
        .status-lunas {
            color: #059669; /* green */
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
        <h3>LAPORAN REKAPITULASI DENDA KETERLAMBATAN</h3>
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
                    Semua Data Denda
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
                <th width="24%">Buku Terkait</th>
                <th width="10%" class="text-center">Terlambat</th>
                <th width="12%" class="text-right">Total Denda</th>
                <th width="10%" class="text-center">Status Bayar</th>
                <th width="10%" class="text-center">Tgl Lunas</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($data as $index => $denda)
                @php $grandTotal += $denda->total_denda; @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $denda->peminjaman && $denda->peminjaman->anggota ? $denda->peminjaman->anggota->nama : '-' }}</strong></td>
                    <td>{{ $denda->peminjaman && $denda->peminjaman->anggota ? $denda->peminjaman->anggota->nim : '-' }}</td>
                    <td>{{ $denda->peminjaman && $denda->peminjaman->buku ? $denda->peminjaman->buku->judul : '-' }}</td>
                    <td class="text-center">{{ $denda->jumlah_hari }} Hari</td>
                    <td class="text-right">Rp{{ number_format($denda->total_denda, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $denda->status_bayar }}">
                            {{ $denda->status_bayar === 'belum_bayar' ? 'BELUM BAYAR' : 'LUNAS' }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ $denda->tgl_denda ? $denda->tgl_denda->isoFormat('D MMM Y') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data denda keterlambatan untuk periode ini.</td>
                </tr>
            @endforelse
            @if(count($data) > 0)
                <tr>
                    <td colspan="5" class="text-right"><strong>TOTAL DENDA AKUMULATIF:</strong></td>
                    <td class="text-right"><strong>Rp{{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                    <td colspan="2"></td>
                </tr>
            @endif
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
