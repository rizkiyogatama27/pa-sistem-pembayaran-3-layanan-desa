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

	.action-link {
		display: inline-flex;
		align-items: center;
		padding: 6px 12px;
		border-radius: 10px;
		border: 1px solid #cfe0f1;
		color: #215d90;
		text-decoration: none;
		background: #fff;
	}

	.action-danger {
		display: inline-flex;
		align-items: center;
		padding: 6px 12px;
		border-radius: 10px;
		border: 1px solid transparent;
		color: #fff;
		background: #ef4444;
	}

	#payment-table td,
	#payment-table th {
		vertical-align: top;
	}

	#payment-table .aksi-wrap {
		display: flex;
		gap: 6px;
		align-items: center;
		flex-wrap: nowrap;
	}

	#payment-table .aksi-wrap form {
		margin: 0;
	}

	#payment-table .aksi-wrap a,
	#payment-table .aksi-wrap button {
		white-space: nowrap;
	}

	#payment-table .aksi-wrap a,
	#payment-table .aksi-wrap button {
		padding: 6px 10px !important;
		font-size: 13px !important;
		line-height: 1 !important;
		border-radius: 10px !important;
	}

	#payment-table .aksi-wrap form {
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
						Kirim WA Massal
					</button>
				</form>
				<form action="{{ route('pembayaran.reminder-whatsapp.bulk') }}" method="POST" onsubmit="return confirm('Kirim ulang WhatsApp walau sudah pernah dikirim hari ini?');" style="display:inline;">
					@csrf
					<input type="hidden" name="kategori" value="{{ $selectedKategori ?? 'wajib' }}">
					<input type="hidden" name="force" value="1">
					<button type="submit" style="display:inline-flex;align-items:center;padding:10px 14px;border-radius:12px;background:#fff;color:#215d90;font-weight:800;border:1px solid #cfe0f1;box-shadow:0 8px 16px rgba(15,23,42,.04);cursor:pointer;">
						Kirim Ulang WA
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

	<div style="margin-top:16px; margin-bottom:16px; display:flex; justify-content:flex-end;">
		<form method="POST" action="{{ route('pembayaran.reminder-whatsapp.all') }}" onsubmit="return confirm('Anda yakin ingin mengirimkan pesan tagihan via WhatsApp ke SEMUA warga yang tampil di tabel ini (belum lunas)?')">
			@csrf
			<input type="hidden" name="kategori" value="{{ $selectedKategori ?? 'wajib' }}">
			<input type="hidden" name="bulan" value="{{ request('bulan', now()->month) }}">
			<input type="hidden" name="tahun" value="{{ request('tahun', now()->year) }}">
			<input type="hidden" name="jenis_pembayaran_id" value="{{ request('jenis_pembayaran_id') }}">
			<input type="hidden" name="status" value="{{ request('status') }}">
			
			<button type="submit" style="height:48px;padding:0 22px;border-radius:11px;background:#eab308;color:#ffffff;font-weight:800;border:none;cursor:pointer;font-size:15px;box-shadow:0 4px 12px rgba(234,179,8,0.3); display:inline-flex; align-items:center; gap:8px;">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
				Blast WA Otomatis
			</button>
		</form>
	</div>

	<div class="table-card overflow-hidden">
		<div class="overflow-x-auto">
			<table id="payment-table" class="min-w-full divide-y divide-gray-200">
				<thead class="table-head">
					<tr>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Invoice</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Warga</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis Pembayaran</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Detail Air</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jatuh Tempo</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Metode</th>
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
							<td class="px-4 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $p->invoice ?? 'Belum ada' }}</td>
							<td class="px-4 py-3 text-sm text-gray-800">{{ $p->warga->nama ?? '-' }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $p->jenisPembayaran->nama ?? '-' }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								@if($p->periode || $p->pemakaian_air !== null)
									<div class="font-semibold text-gray-900">{{ $p->periode ?? '-' }}</div>
									<div class="text-xs text-gray-500">
										@if($p->pemakaian_air !== null)
											Pemakaian {{ $p->pemakaian_air }} m3
										@endif
										@if($p->denda > 0)
											| Denda Rp {{ number_format($p->denda,0,',','.') }}
										@endif
									</div>
								@else
									<span class="text-gray-400">-</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								@if($p->jatuh_tempo)
									<div>{{ \Illuminate\Support\Carbon::parse($p->jatuh_tempo)->translatedFormat('d M Y') }}</div>
									@if($p->status !== 'paid' && \Illuminate\Support\Carbon::parse($p->jatuh_tempo)->isPast())
										<div class="text-xs font-semibold text-rose-600">Terlambat</div>
									@endif
								@else
									<span class="text-gray-400">-</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $p->tanggal_bayar }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								<div>Rp {{ number_format($p->jumlah,0,',','.') }}</div>
								@if($isJenisAir && $p->meter_awal !== null && $p->meter_akhir !== null)
									<div class="text-xs text-gray-500">{{ $p->meter_awal ?? 0 }} - {{ $p->meter_akhir ?? 0 }} x Rp {{ number_format($p->tarif_per_meter,0,',','.') }}</div>
								@endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								@if($p->status === 'paid')
									<span class="status-badge status-paid">Lunas</span>
								@else
									<span class="status-badge" style="background:#e5e7eb;color:#6b7280;">-</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm col-status" style="vertical-align: middle;">
								@if($p->status == 'paid')
									<span class="status-badge status-paid">Lunas</span>
								@elseif((int) $p->jumlah <= 0)
									<span class="status-badge" style="background:#e0e7ef;color:#64748b;">Draft</span>
								@else
									<span class="status-badge status-pending">Belum Bayar</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm col-aksi" style="vertical-align: middle;">
								<div class="aksi-wrap">
									<a href="{{ route('pembayaran.invoice', $p->id) }}" class="action-link">Invoice</a>
									<a href="{{ route('pembayaran.edit', $p->id) }}" class="action-link">Edit</a>
									@if($p->status !== 'paid')
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
										<a href="{{ $waUrl }}" target="_blank" style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:10px;border:1px solid transparent;color:#fff;background:#10b981;font-weight:700;text-decoration:none;">Kirim WA</a>
										
										<form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data pembayaran ini?')">
											@csrf
											@method('DELETE')
											<button type="submit" class="action-danger">Hapus</button>
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