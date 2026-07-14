<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
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
            font-size: 18pt;
        }
        .header h3 {
            margin: 5px 0 0 0;
            color: #f4d35e;
            font-size: 14pt;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 9pt;
            color: #666;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 20px;
            font-size: 10pt;
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
            padding: 8px;
            font-size: 9.5pt;
            border: 1px solid #0d3b66;
        }
        table.data-table td {
            border: 1px solid #dddddd;
            padding: 8px;
            font-size: 9pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 9.5pt;
        }
        .footer-date {
            margin-bottom: 50px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>PERPUSTAKAAN UNIVERSITAS AIRLANGGA</h2>
        <h3>LAPORAN REKAPITULASI BUKU</h3>
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
                    Semua Data Buku
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
                <th width="5%" class="text-center">No</th>
                <th width="35%">Judul Buku</th>
                <th width="20%">Pengarang</th>
                <th width="15%">Penerbit</th>
                <th width="8%" class="text-center">Tahun</th>
                <th width="12%">Kategori</th>
                <th width="5%" class="text-center">Stok</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $buku)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $buku->judul }}</strong></td>
                    <td>{{ $buku->pengarang }}</td>
                    <td>{{ $buku->penerbit ?: '-' }}</td>
                    <td class="text-center">{{ $buku->tahun_terbit ?: '-' }}</td>
                    <td>{{ $buku->kategori ?: '-' }}</td>
                    <td class="text-center">{{ $buku->stok }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data buku untuk periode ini.</td>
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
