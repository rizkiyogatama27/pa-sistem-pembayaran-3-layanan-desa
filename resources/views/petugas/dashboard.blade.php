@extends('layouts.app')

@section('content')
<style>
	.page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
	.hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:20px 22px; margin-bottom:18px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
	.metric-card, .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
	.metric-card { padding:14px; }
	.badge-ok { display:inline-block; background:#d6f5dc; color:#146a2f; padding:2px 10px; border-radius:999px; font-size:12px; font-weight:800; }
	.badge-wait { display:inline-block; background:#fdecc8; color:#9a4a00; padding:2px 10px; border-radius:999px; font-size:12px; font-weight:800; }
	.table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
	.table-head th { color:#215d90; }
</style>

<div class="page-wrap">
	<div class="hero-card">
		<h2 style="margin:0 0 8px;font-size:28px;font-weight:800;letter-spacing:.2px;">Dashboard Petugas</h2>
		<p style="margin:0;color:rgba(255,255,255,.84);font-size:14px;">Pantau transaksi, status pembayaran, dan data ringkas operasional.</p>
	</div>

	<div class="section" style="margin-bottom:18px;">
		<div style="font-size:12px;color:#64748b;">Panduan</div>
		<h2 style="margin:6px 0 12px;font-size:20px;font-weight:800;color:#0f172a;">Tata Cara Pembayaran</h2>
		<div class="panel-card" style="padding:14px;">
			<ol style="margin:0;padding-left:1.2rem;line-height:1.8;color:#334155;">
				<li><strong>Masuk / Daftar:</strong> Arahkan warga untuk login atau registrasi melalui tombol "Cek Tagihan Saya".</li>
				<li><strong>Periksa Tagihan:</strong> Buka halaman Tagihan dan pilih tagihan yang perlu dibayar.</li>
				<li><strong>Pilih Metode Pembayaran:</strong> Pilih QRIS atau Virtual Account lalu lanjutkan ke penyedia pembayaran.</li>
				<li><strong>Selesaikan Pembayaran:</strong> Ikuti instruksi pada penyedia pembayaran hingga transaksi berhasil.</li>
				<li><strong>Konfirmasi:</strong> Cek status pembayaran di halaman Tagihan; hubungi admin jika belum terupdate.</li>
			</ol>
		</div>
	</div>

	<div class="panel-card" style="padding:16px 18px;margin-bottom:18px;">
		<div style="font-size:13px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:.08em;">Pemasukan</div>
		<div style="font-size:30px;font-weight:800;color:#188f78;margin-top:4px;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
	</div>

	<div class="panel-card" style="overflow:auto;">
		<div style="padding:14px 14px 8px;font-size:16px;font-weight:700;color:#0f172a;">Transaksi Terbaru</div>
		<table style="width:100%;border-collapse:collapse;font-size:14px;min-width:700px;">
			<thead class="table-head">
				<tr>
					<th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Tanggal</th>
					<th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Warga</th>
					<th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Jenis</th>
					<th style="text-align:right;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Jumlah</th>
					<th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Status</th>
				</tr>
			</thead>
			<tbody>
				@forelse($pembayaranTerbaru as $item)
					<tr>
						<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ \Illuminate\Support\Carbon::parse($item->tanggal_bayar)->format('d M Y') }}</td>
						<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;">{{ $item->warga->nama ?? '-' }}</td>
						<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ $item->jenisPembayaran->nama ?? '-' }}</td>
						<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;text-align:right;">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
						<td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
							@if(($item->status ?? 'pending') === 'paid')
									<span class="badge-ok">Lunas</span>
							@else
								<span class="badge-wait">Pending</span>
							@endif
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="5" style="padding:16px 14px;color:#64748b;border-bottom:1px solid #f1f5f9;">Belum ada transaksi pembayaran.</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</div>
</div>
@endsection