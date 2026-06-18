<!DOCTYPE html>
<html>
<head>
<title>Laporan Pembayaran Desa</title>

<style>
body{
font-family: Arial;
}

table{
width:100%;
border-collapse: collapse;
}

table, th, td{
border:1px solid black;
}

th, td{
padding:8px;
text-align:center;
}

h2{
text-align:center;
}
</style>

</head>

<body>

<h2>LAPORAN PEMBAYARAN DESA</h2>

@if(!empty($tahun) || !empty($bulan))
<p style="text-align:center; margin-top:-8px;">
Filter:
@if(!empty($bulan)) Bulan {{ \Carbon\Carbon::create()->month((int) $bulan)->translatedFormat('F') }} @endif
@if(!empty($tahun)) Tahun {{ $tahun }} @endif
</p>
@endif

<table>

<thead>
<tr>
<th>No</th>
<th>Nama Warga</th>
<th>Jenis Pembayaran</th>
<th>Jumlah</th>
<th>Tanggal</th>
</tr>
</thead>

<tbody>

@foreach($pembayarans as $p)

<tr>
<td>{{ $loop->iteration }}</td>
<td>{{ $p->warga->nama }}</td>
<td>{{ $p->jenisPembayaran->nama }}</td>
<td>Rp {{ number_format($p->jumlah) }}</td>
<td>{{ \Carbon\Carbon::parse($p->tanggal_bayar)->translatedFormat('d F Y') }}</td>
</tr>

@endforeach

</tbody>

</table>

</body>
</html>