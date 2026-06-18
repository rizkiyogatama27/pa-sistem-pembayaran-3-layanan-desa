@extends('layouts.app')

@section('content')
<style>
	.page-wrap { max-width: 1180px; margin: 0 auto; padding: 26px 18px 44px; }
	.dashboard-title { font-size: 24px; font-weight: 900; color: #18324d; margin: 0; }
	.dashboard-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
	.hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color: #fff; border-radius: 20px; padding: 26px; margin: 18px 0; box-shadow: 0 18px 30px rgba(24, 94, 168, .16); position: relative; overflow: hidden; }
	.hero-card::before,
	.hero-card::after { content: ''; position: absolute; border-radius: 999px; background: rgba(255,255,255,.08); }
	.hero-card::before { width: 220px; height: 220px; right: -90px; top: -120px; }
	.hero-card::after { width: 180px; height: 180px; left: -80px; bottom: -90px; }
	.hero-card > * { position: relative; z-index: 1; }
	.hero-card__top { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; flex-wrap:wrap; }
	.hero-card__actions { display:flex; gap:10px; flex-wrap:wrap; }
	.hero-btn { display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; border-radius:12px; text-decoration:none; font-weight:800; font-size:14px; }
	.hero-btn--light { background:#fff; color:#215d90; }
	.hero-btn--ghost { background:rgba(255,255,255,.14); color:#fff; border:1px solid rgba(255,255,255,.16); }
	.metric-card, .panel-card { background: #fff; border: 1px solid #dce6f1; border-radius: 16px; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
	.metric-card { padding: 14px; min-height: 98px; display: flex; flex-direction: column; justify-content: center; }
	.metric-card__top { display:flex; justify-content:space-between; gap:10px; align-items:flex-start; margin-bottom:10px; }
	.metric-icon { width: 38px; height: 38px; border-radius: 12px; display:grid; place-items:center; font-size: 18px; }
	.metric-chip { display:inline-flex; align-items:center; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:800; }
	.panel-card { padding: 16px 18px; }
	.panel-card--soft { background: linear-gradient(135deg,#ffffff,#f8fbfe); }
    .tag { display:inline-flex; align-items:center; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:800; }
    .tag-blue { background:#dbeafe; color:#215d90; }
    .tag-green { background:#d6f5dc; color:#146a2f; }
    .tag-amber { background:#fdecc8; color:#9a4a00; }
    .tag-purple { background:#ede9fe; color:#5b21b6; }
	.action-link { display:inline-flex; align-items:center; padding:10px 14px; border-radius:12px; background:rgba(255,255,255,.14); color:#fff; text-decoration:none; font-weight:800; border:1px solid rgba(255,255,255,.16); }
	.action-link-dark { display:inline-flex; align-items:center; padding:10px 14px; border-radius:12px; background:#fff; color:#215d90; text-decoration:none; font-weight:800; border:1px solid #cfe0f1; }
	.table-shell { background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 10px 22px rgba(15, 23, 42, .05); }
	.table-shell__head { padding:14px 14px 8px; font-size:16px; font-weight:800; color:#0f172a; }
	.quick-links { display:flex; flex-wrap:wrap; gap:10px; margin: 16px 0 18px; }
	.quick-link { display:inline-flex; align-items:center; padding:10px 14px; border-radius:12px; background:#fff; color:#215d90; text-decoration:none; font-weight:800; border:1px solid #cfe0f1; box-shadow:0 8px 16px rgba(15, 23, 42, .04); }
</style>

<div class="page-wrap">
	<div>
		<h2 class="dashboard-title">Dashboard Admin</h2>
		<div class="dashboard-subtitle">Pantau data warga, pembayaran, dan akses fitur utama dari satu halaman.</div>
	</div>

	<div class="hero-card">
	    <div class="hero-card__top">
	    	<div>
	    		<h2 style="margin:0 0 8px;font-size:30px;font-weight:900;letter-spacing:.2px;">Selamat Datang, Admin Desa <i class="fa-solid fa-handshake"></i></h2>
	    		<p style="margin:0;color:rgba(255,255,255,.84);font-size:14px;max-width:720px;">Semua data pembayaran dan donasi Desa Pangean ada di sini. Pantau secara real-time dari satu dashboard terpusat.</p>
	    	</div>
	    	<div class="hero-card__actions">
	    		<a href="{{ route('pembayaran.wajib') }}" class="hero-btn hero-btn--light">+ Tambah Tagihan</a>
	    		<a href="{{ route('rekap.bulan') }}" class="hero-btn hero-btn--ghost">Lihat Rekap</a>
	    	</div>
	    </div>
	</div>

	<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:14px;margin-bottom:18px;">
		<div class="panel-card">
			<div style="font-size:12px;color:#64748b;margin-bottom:4px;">Pemasukan Lunas Bulan Ini</div>
			<div style="font-size:30px;font-weight:900;color:#215d90;">Rp {{ number_format($totalPemasukanBulanIni, 0, ',', '.') }}</div>
		</div>
		<div class="panel-card">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
				<span style="font-size:12px;color:#64748b;">Status Pembayaran</span>
			</div>
			<div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:4px;">
				<span style="color:#475569;">Lunas</span>
				<strong style="color:#215d90;">{{ $totalLunas }}</strong>
			</div>
			<div style="display:flex;justify-content:space-between;font-size:14px;">
				<span style="color:#475569;">Pending</span>
				<strong style="color:#b45309;">{{ $totalPending }}</strong>
			</div>
			<div style="display:flex;justify-content:space-between;font-size:14px;margin-top:4px;">
				<span style="color:#475569;">Tunggakan</span>
				<strong style="color:#b91c1c;">{{ $totalTunggakan }}</strong>
			</div>
		</div>
	</div>

	<div class="panel-card panel-card--soft" style="margin-bottom:18px;">
		<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
			<div>
				<div style="font-size:13px;font-weight:800;color:#215d90;text-transform:uppercase;letter-spacing:.08em;">Ringkasan Donasi & Event</div>
				<div style="font-size:20px;font-weight:800;color:#0f172a;margin-top:4px;">Satu panel untuk melihat progres kampanye donasi</div>
			</div>
			<a href="{{ route('event-donasi.laporan') }}" class="action-link-dark">Buka Laporan Donasi</a>
		</div>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:14px;">
			<div class="metric-card">
				<div style="font-size:12px;color:#64748b;">Event Aktif</div>
				<div style="font-size:26px;font-weight:900;color:#215d90;">{{ $eventDonasiAktif }}</div>
			</div>
			<div class="metric-card">
				<div style="font-size:12px;color:#64748b;">Event Selesai</div>
				<div style="font-size:26px;font-weight:800;color:#0f172a;">{{ $eventDonasiSelesai }}</div>
			</div>
			<div class="metric-card">
				<div style="font-size:12px;color:#64748b;">Terkumpul</div>
				<div style="font-size:26px;font-weight:800;color:#188f78;">Rp {{ number_format($eventDonasiTerkumpul, 0, ',', '.') }}</div>
			</div>
			<div class="metric-card">
				<div style="font-size:12px;color:#64748b;">Penyumbang Unik</div>
				<div style="font-size:26px;font-weight:800;color:#b45309;">{{ $eventDonasiPenyumbang }}</div>
			</div>
		</div>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
			@forelse($eventDonasiTerbaru as $event)
				@php
					$target = (float) ($event->target_dana ?? 0);
					$terkumpul = (float) ($event->total_terkumpul ?? 0);
					$progress = $target > 0 ? min(100, round(($terkumpul / $target) * 100)) : 0;
				@endphp
				<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:14px;">
					@if($event->cover_image_url)
						<img src="{{ $event->cover_image_url }}" alt="{{ $event->nama_event }}" style="width:100%;height:140px;object-fit:cover;border-radius:8px;margin-bottom:10px;display:block;">
					@endif
					<div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;margin-bottom:8px;">
						<div>
							<div style="font-size:14px;font-weight:800;color:#0f172a;">{{ $event->nama_event }}</div>
							<div style="font-size:12px;color:#64748b;">{{ ucfirst($event->status) }} · {{ $event->kontribusis_count }} kontribusi</div>
						</div>
						<span class="tag tag-blue">{{ $progress }}%</span>
					</div>
					<div style="font-size:13px;color:#475569;margin-bottom:6px;">Rp {{ number_format($terkumpul, 0, ',', '.') }} / Rp {{ number_format($target, 0, ',', '.') }}</div>
					<div style="height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
						<progress max="100" value="{{ $progress }}" style="width:100%;height:10px;border:none;border-radius:999px;overflow:hidden;"></progress>
					</div>
				</div>
			@empty
				<div style="background:#fff;border:1px dashed #cbd5e1;border-radius:12px;padding:18px;color:#64748b;">Belum ada event donasi yang bisa ditampilkan.</div>
			@endforelse
		</div>
	</div>



	<div class="table-shell">
		<div class="table-shell__head">Transaksi Terbaru</div>
		<table style="width:100%;border-collapse:collapse;font-size:14px;min-width:700px;">
			<thead>
				<tr style="background:#f8fafc;color:#334155;">
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
								<span style="display:inline-block;background:#dbeafe;color:#215d90;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;">Lunas</span>
							@else
								<span style="display:inline-block;background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:700;">Pending</span>
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

	   {{-- Menu Aktivitas Admin Terbaru dan Reminder WhatsApp Terbaru dihapus sesuai permintaan --}}
</div>
@endsection