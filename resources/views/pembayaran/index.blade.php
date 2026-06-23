@extends('layouts.app')

@section('content')
<style>
	.page-wrap {
		max-width: 1120px;
		margin: 0 auto;
		padding: 24px 16px 34px;
	}

	.hero-card {
		background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%);
		color: #fff;
		border-radius: 20px;
		padding: 20px;
		box-shadow: 0 18px 30px rgba(24, 94, 168, .16);
		display: flex;
		justify-content: space-between;
		align-items: center;
		gap: 12px;
		flex-wrap: wrap;
	}

	.hero-chip {
		display: inline-flex;
		align-items: center;
		padding: 10px 14px;
		background: rgba(255,255,255,.14);
		color: #fff;
		border-radius: 12px;
		font-weight: 800;
		font-size: 14px;
		border: 1px solid rgba(255,255,255,.16);
	}

	.filter-card,
	.table-card {
		background: #fff;
		border: 1px solid #dce6f1;
		border-radius: 18px;
		box-shadow: 0 10px 22px rgba(15, 23, 42, .05);
	}

	.filter-card {
		padding: 14px;
		background: #f8fbfe;
	}

	.filter-card label {
		display: block;
		font-size: 11px;
		font-weight: 800;
		letter-spacing: .08em;
		text-transform: uppercase;
		color: #215d90;
		margin-bottom: 5px;
	}

	.filter-card select {
		width: 100%;
		height: 48px;
		background: #fff;
		border: 1px solid #cfe0f1;
		color: #18324d;
		border-radius: 12px;
		padding: 10px 12px;
		font-size: 15px;
		outline: none;
	}

	.table-head {
		background: linear-gradient(135deg, #eff6ff, #ecfeff);
	}

	.table-head th {
		color: #215d90;
	}

	.status-paid {
		background: #d6f5dc;
		color: #146a2f;
	}

	.status-pending {
		background: #fdecc8;
		color: #9a4a00;
	}

	.status-badge {
		display: inline-flex;
		align-items: center;
		padding: 4px 10px;
		border-radius: 999px;
		font-size: 12px;
		font-weight: 800;
	}

	.btn-aksi {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 5px;
		padding: 6px 12px !important;
		font-size: 12px !important;
		font-weight: 700;
		border-radius: 8px !important;
		text-decoration: none;
		transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
		border: 1px solid transparent;
		cursor: pointer;
		line-height: 1 !important;
		white-space: nowrap;
	}
	.btn-aksi:active {
		transform: scale(0.96);
	}
	.btn-invoice {
		background: #f8fafc; color: #64748b; border-color: #e2e8f0;
	}
	.btn-invoice:hover {
		background: #f1f5f9; color: #334155; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.02);
	}
	.btn-edit {
		background: #f0f9ff; color: #0284c7; border-color: #bae6fd;
	}
	.btn-edit:hover {
		background: #e0f2fe; color: #0369a1; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(2,132,199,0.06);
	}
	.btn-bayar {
		background: linear-gradient(135deg, #0ea5e9, #2563eb); color: #fff; border-color: transparent;
		box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
	}
	.btn-bayar:hover {
		box-shadow: 0 6px 14px rgba(37, 99, 235, 0.35); transform: translateY(-1px);
	}
	.btn-wa {
		background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-color: transparent;
		box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
	}
	.btn-wa:hover {
		box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35); transform: translateY(-1px);
	}
	.btn-hapus {
		background: #fff1f2; color: #e11d48; border-color: #fecdd3;
	}
	.btn-hapus:hover {
		background: #ffe4e6; color: #be123c; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(225,29,72,0.06);
	}

	#payment-table td,
	#payment-table th {
		vertical-align: top;
	}

	#payment-table .aksi-wrap {
		display: flex;
		gap: 8px;
		align-items: center;
		flex-wrap: nowrap;
	}

	#payment-table .aksi-wrap form {
		margin: 0;
		display: inline-block;
	}

	#payment-table td.col-status,
	#payment-table td.col-aksi {
		vertical-align: middle !important;
	}
</style>
@php
	$isDonasiPage = ($selectedKategori ?? 'wajib') === 'donasi';
	$panelTitle = $isDonasiPage ? 'Donasi Sukarela' : 'Pembayaran Rutin';
	$panelDescription = $isDonasiPage
		? 'Donasi bersifat tidak wajib dan dikelola secara manual atau melalui crowdfunding.'
		: 'Tagihan wajib seperti air dan sampah dibuat otomatis untuk warga terdaftar.';
@endphp
<div class="page-wrap space-y-6">
	<div class="hero-card">
		<div>
			<h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">{{ $panelTitle }}</h2>
			<p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">{{ $panelDescription }}</p>
		</div>
		   {{-- Badge Auto Billing/Manual dihilangkan sesuai permintaan --}}
		<span class="hero-chip">{{ $selectedKategori === 'donasi' ? 'Manual / Crowdfunding' : 'Auto Billing' }}</span>
	</div>

	<div class="flex items-center justify-between gap-3 flex-wrap">
		<div>
			<h2 class="text-2xl font-semibold text-gray-900">Data {{ $panelTitle }}</h2>
			<p class="text-sm text-gray-500 mt-1">Filter data berdasarkan periode, jenis pembayaran, dan status.</p>
		</div>
		<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
			<a href="{{ route('rekap.tunggakan') }}" style="display:inline-flex;align-items:center;padding:10px 14px;border-radius:12px;background:#fff;color:#215d90;text-decoration:none;font-weight:800;border:1px solid #cfe0f1;box-shadow:0 8px 16px rgba(15,23,42,.04);">
				Lihat Tunggakan Air
			</a>
			@if(($selectedKategori ?? 'wajib') !== 'donasi')
				<form action="{{ route('pembayaran.reminder-whatsapp.bulk') }}" method="POST" onsubmit="return confirm('Kirim reminder WhatsApp ke semua warga yang sudah jatuh tempo?');" style="display:inline;">
					@csrf
					<input type="hidden" name="kategori" value="{{ $selectedKategori ?? 'wajib' }}">
					<button type="submit" style="display:inline-flex;align-items:center;padding:10px 14px;border-radius:12px;background:linear-gradient(135deg,#1d5fb8,#14b8a6);color:#fff;font-weight:800;border:none;box-shadow:0 8px 16px rgba(29,95,184,.14);cursor:pointer;">
						Automasi Pengingat WA
					</button>
				</form>
				<form action="{{ route('pembayaran.reminder-whatsapp.bulk') }}" method="POST" onsubmit="return confirm('Jalankan automasi pengingat WA ulang walau sudah pernah dikirim hari ini?');" style="display:inline;">
					@csrf
					<input type="hidden" name="kategori" value="{{ $selectedKategori ?? 'wajib' }}">
					<input type="hidden" name="force" value="1">
					<button type="submit" style="display:inline-flex;align-items:center;padding:10px 14px;border-radius:12px;background:#fff;color:#215d90;font-weight:800;border:1px solid #cfe0f1;box-shadow:0 8px 16px rgba(15,23,42,.04);cursor:pointer;">
						Automasi WA Ulang
					</button>
				</form>
			@endif
		</div>
	</div>

	@if(session('success'))
		<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
			{{ session('success') }}
		</div>
	@endif

	@if(session('error'))
		<div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
			{{ session('error') }}
		</div>
	@endif

	@if(isset($generatedCount) && $generatedCount > 0)
		<div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sky-700">
			Sistem otomatis menambahkan {{ $generatedCount }} tagihan baru untuk periode {{ $selectedPeriode }}.
		</div>
	@endif

	<form method="GET" action="{{ route('pembayaran.index') }}" class="filter-card" style="background: #141413; border: 1px solid #2c2c28;">
		<input type="hidden" name="kategori" value="{{ $selectedKategori ?? 'wajib' }}">
		<div style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;">
			<div style="min-width:220px;flex:1 1 260px;">
				<label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b8b8b0;margin-bottom:5px;">Bulan</label>
				<select name="bulan" style="width:100%;height:48px;background:#1f1f1c;border:1px solid #4b4b46;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2);">
					@foreach(range(1,12) as $b)
						<option value="{{ $b }}" {{ request('bulan', now()->month) == $b ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
					@endforeach
				</select>
			</div>
			<div style="min-width:120px;flex:1 1 120px;">
				<label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b8b8b0;margin-bottom:5px;">Tahun</label>
				<select name="tahun" style="width:100%;height:48px;background:#1f1f1c;border:1px solid #4b4b46;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2);">
					@for($y = date('Y')-3; $y <= date('Y'); $y++)
						<option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
					@endfor
				</select>
			</div>

			<div style="min-width:220px;flex:1 1 260px;">
				<label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b8b8b0;margin-bottom:5px;">Jenis</label>
				<select name="jenis_pembayaran_id" style="width:100%;height:48px;background:#1f1f1c;border:1px solid #4b4b46;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2);">
					<option value="">Semua Jenis</option>
					@foreach($jenisPembayarans as $jenis)
						<option value="{{ $jenis->id }}" @selected((string) $selectedJenisPembayaranId === (string) $jenis->id)>
							{{ $jenis->nama }}
						</option>
					@endforeach
				</select>
			</div>

			<div style="min-width:220px;flex:1 1 260px;">
				<label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#b8b8b0;margin-bottom:5px;">Status</label>
				<select name="status" style="width:100%;height:48px;background:#1f1f1c;border:1px solid #4b4b46;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2);">
					<option value="">Semua Status</option>
					<option value="belum_bayar" @selected($selectedStatus === 'belum_bayar')>Belum Bayar</option>
					<option value="pending" @selected($selectedStatus === 'pending')>Pending</option>
					<option value="paid" @selected($selectedStatus === 'paid')>Sudah Bayar</option>
				</select>
			</div>

			<div style="display:flex;align-items:end;gap:8px;">
				<button
					type="submit"
					style="height:48px;padding:0 22px;border-radius:11px;background:#3d3d37;color:#ffffff;font-weight:700;border:1px solid #5a5a53;cursor:pointer;font-size:15px;box-shadow:0 4px 12px rgba(0,0,0,0.2);"
				>
					Cari
				</button>
				<a
					href="{{ route('pembayaran.index', ['kategori' => $selectedKategori ?? 'wajib']) }}"
					style="height:48px;padding:0 22px;border-radius:11px;border:1px solid #5a5a53;color:#ffffff;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;background:#2c2c28;font-size:15px;font-weight:600;box-shadow:0 2px 4px rgba(0,0,0,0.1);"
				>
					Reset
				</a>
			</div>
		</div>
	</form>


	<div class="table-card overflow-hidden">
		<div class="overflow-x-auto">
			<table id="payment-table" class="min-w-full divide-y divide-gray-200">
				<thead class="table-head">
					<tr>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Warga & Invoice</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis & Detail</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jatuh Tempo</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nominal</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100 bg-white">
					@forelse ($pembayarans as $p)
						@php
							$isJenisAir = str_contains(strtolower((string) optional($p->jenisPembayaran)->nama), 'air');
						@endphp
						<tr>
							<td class="px-4 py-3 text-sm">
								<div class="font-semibold text-gray-800" style="font-size: 14px;">{{ $p->warga->nama ?? '-' }}</div>
								<div class="text-xs text-gray-400 mt-1" style="font-family: monospace;">{{ $p->invoice ?? 'Belum ada' }}</div>
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								<div class="font-semibold text-gray-800">{{ $p->jenisPembayaran->nama ?? '-' }}</div>
								@if($p->periode || $p->pemakaian_air !== null)
									<div class="text-xs text-gray-500 mt-1">
										{{ $p->periode ?? '-' }}
										@if($p->pemakaian_air !== null)
											| Pmk: {{ $p->pemakaian_air }}m³
										@endif
										@if($p->denda > 0)
											| +Rp{{ number_format($p->denda,0,',','.') }}
										@endif
									</div>
								@endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								@if($p->jatuh_tempo)
									<div>{{ \Illuminate\Support\Carbon::parse($p->jatuh_tempo)->translatedFormat('d M Y') }}</div>
									@if($p->status !== 'paid' && \Illuminate\Support\Carbon::parse($p->jatuh_tempo)->isPast())
										<div class="text-xs font-semibold text-rose-600 mt-1">Terlambat</div>
									@endif
								@else
									<span class="text-gray-400">-</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								<div class="font-semibold text-gray-800">Rp {{ number_format($p->jumlah,0,',','.') }}</div>
								@if($isJenisAir && $p->meter_awal !== null && $p->meter_akhir !== null)
									<div class="text-xs text-gray-400 mt-1">{{ $p->meter_awal ?? 0 }} - {{ $p->meter_akhir ?? 0 }} x Rp {{ number_format($p->tarif_per_meter,0,',','.') }}</div>
								@endif
							</td>
							<td class="px-4 py-3 text-sm col-status" style="vertical-align: middle;">
								@if($p->status == 'paid')
									<span class="status-badge status-paid">Lunas</span>
									@if($p->tanggal_bayar)
										<div class="text-xs text-gray-400 mt-1">{{ \Illuminate\Support\Carbon::parse($p->tanggal_bayar)->translatedFormat('d/m/y') }}</div>
									@endif
								@elseif((int) $p->jumlah <= 0)
									<span class="status-badge" style="background:#e0e7ef;color:#64748b;">Draft</span>
								@else
									<span class="status-badge status-pending">Belum Bayar</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm col-aksi" style="vertical-align: middle;">
								<div class="aksi-wrap">
									<a href="{{ route('pembayaran.invoice', $p->id) }}" class="btn-aksi btn-invoice"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Invoice</a>
									<a href="{{ route('pembayaran.edit', $p->id) }}" class="btn-aksi btn-edit"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>Edit</a>
									@if($p->status !== 'paid')
										<a href="{{ route('pembayaran.cash.form', $p->id) }}" class="btn-aksi btn-bayar"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>Bayar Tunai</a>
										@php
											$noHp = $p->warga->no_hp ?? '';
											// Normalisasi nomor HP ke format internasional (62)
											$noHp = preg_replace('/\D/', '', $noHp);
											if (str_starts_with($noHp, '0')) {
												$noHp = '62' . substr($noHp, 1);
											}
											
											$msg = "Halo " . ($p->warga->nama ?? '-') . ",\n\n" .
												   "Ini pengingat tagihan " . ($p->jenisPembayaran->nama ?? '-') . " periode " . ($p->periode ?? '-') . ".\n" .
												   "Total tagihan: Rp " . number_format((int) $p->jumlah, 0, ',', '.') . "\n" .
												   "Jatuh tempo: " . ($p->jatuh_tempo ? \Illuminate\Support\Carbon::parse($p->jatuh_tempo)->translatedFormat('d M Y') : '-') . "\n" .
												   "Silakan login ke portal untuk melakukan pembayaran.\n\n" .
												   "Terima kasih.";
											$waUrl = "https://api.whatsapp.com/send?phone=" . $noHp . "&text=" . urlencode($msg);
										@endphp
										<a href="{{ $waUrl }}" target="_blank" class="btn-aksi btn-wa"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>Kirim WA</a>
										
										<form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data pembayaran ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="btn-aksi btn-hapus"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Hapus</button>
										</form>
									@endif
								</div>
							</td>
						</tr>
					@empty
						<tr>
								<td colspan="11" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if($pembayarans->hasPages())
		<div style="padding:16px 20px; border-top:1px solid #2c2c28; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
			<span style="font-size:13px;color:#9ca3af;">
				Menampilkan {{ $pembayarans->firstItem() }}–{{ $pembayarans->lastItem() }} dari {{ $pembayarans->total() }} data
			</span>
			<div style="display:flex;gap:6px;align-items:center;">
				@if($pembayarans->onFirstPage())
					<span style="height:36px;padding:0 14px;border-radius:8px;background:#2c2c28;color:#6b7280;display:inline-flex;align-items:center;font-size:13px;">← Sebelumnya</span>
				@else
					<a href="{{ $pembayarans->previousPageUrl() }}" style="height:36px;padding:0 14px;border-radius:8px;background:#3d3d37;color:#ffffff;display:inline-flex;align-items:center;font-size:13px;text-decoration:none;border:1px solid #5a5a53;">← Sebelumnya</a>
				@endif

				<span style="font-size:13px;color:#9ca3af;padding:0 8px;">Hal {{ $pembayarans->currentPage() }} / {{ $pembayarans->lastPage() }}</span>

				@if($pembayarans->hasMorePages())
					<a href="{{ $pembayarans->nextPageUrl() }}" style="height:36px;padding:0 14px;border-radius:8px;background:#3d3d37;color:#ffffff;display:inline-flex;align-items:center;font-size:13px;text-decoration:none;border:1px solid #5a5a53;">Selanjutnya →</a>
				@else
					<span style="height:36px;padding:0 14px;border-radius:8px;background:#2c2c28;color:#6b7280;display:inline-flex;align-items:center;font-size:13px;">Selanjutnya →</span>
				@endif
			</div>
		</div>
		@endif
	</div>
</div>

@endsection