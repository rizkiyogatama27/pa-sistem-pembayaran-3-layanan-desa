@extends('layouts.app')

@section('content')
<style>
	.page-wrap { max-width: 1040px; margin: 0 auto; padding: 26px 18px 44px; }
	.dashboard-title { font-size: 24px; font-weight: 900; color: #18324d; margin: 0; }
	.dashboard-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
	.hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:26px; box-shadow:0 18px 30px rgba(24,94,168,.16); position:relative; overflow:hidden; }
	.hero-card::before,
	.hero-card::after { content:''; position:absolute; border-radius:999px; background:rgba(255,255,255,.08); }
	.hero-card::before { width:220px; height:220px; right:-90px; top:-120px; }
	.hero-card::after { width:180px; height:180px; left:-80px; bottom:-90px; }
	.hero-card > * { position:relative; z-index:1; }
	.panel-card { background:#fff; border:1px solid #dce6f1; border-radius:16px; padding:24px; margin-top:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
	.field-label { display:block; font-size:14px; font-weight:700; color:#215d90; margin-bottom:4px; }
	.field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
	.btn-primary { display:block; width:100%; padding:14px 18px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; box-shadow:0 10px 18px rgba(29,95,184,.14); }
	.btn-secondary { display:block; width:100%; padding:12px 18px; background:#fff; color:#215d90; border:1px solid #cfe0f1; border-radius:12px; font-weight:800; text-decoration:none; text-align:center; }
</style>

<div class="page-wrap">
	<div>
		<h2 class="dashboard-title">Tambah Pembayaran</h2>
		<div class="dashboard-subtitle">Buat tagihan baru untuk warga.</div>
	</div>

	<div class="hero-card">
		<h2 class="text-2xl font-semibold" style="margin:0;">Form Pembayaran</h2>
		<p class="text-sm" style="margin:6px 0 0;color:rgba(255,255,255,.84);max-width:720px;">Isi data tagihan dengan alur yang lebih ringkas dan konsisten dengan tampilan admin lainnya.</p>
	</div>

	<div class="panel-card space-y-6">
		<div>
			<h2 class="text-2xl font-semibold text-gray-900">Form Pembayaran</h2>
		</div>

		@if ($errors->any())
			<div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
				<ul class="list-disc pl-5 space-y-1 text-sm">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

		<form action="{{ route('pembayaran.store') }}" method="POST" class="space-y-4">
			@csrf

			@php
				$selectedJenis = old('jenis_pembayaran_id') ? $jenisPembayarans->firstWhere('id', (int) old('jenis_pembayaran_id')) : null;
				$selectedJenisName = $selectedJenis?->nama ?? '';
			@endphp

			<div>
				<label class="field-label">Warga</label>
				<select name="warga_id" required class="field-input">
					<option value="">-- pilih warga --</option>
					@forelse($wargas as $w)
						<option value="{{ $w->id }}">{{ $w->nama }}</option>
					@empty
						<option value="">Data warga masih kosong</option>
					@endforelse
				</select>
				<p class="mt-1 text-xs text-gray-500">Kalau belum ada warga, isi dulu di menu Warga.</p>
			</div>

			<div>
				<label class="field-label">Jenis Pembayaran</label>
				<select name="jenis_pembayaran_id" id="jenis_pembayaran_id" onchange="toggleAirFields()" required class="field-input">
					<option value="">-- pilih jenis --</option>
					@forelse($jenisPembayarans as $j)
						<option value="{{ $j->id }}" data-nama="{{ $j->nama }}" @selected(old('jenis_pembayaran_id') == $j->id)>{{ $j->nama }}</option>
					@empty
						<option value="">Data jenis pembayaran masih kosong</option>
					@endforelse
				</select>
				<p class="mt-1 text-xs text-gray-500">Tambah dulu minimal 1 jenis pembayaran di menu Jenis Pembayaran.</p>
			</div>

			<div id="air-fields" class="space-y-4" style="display:none;">
				<div class="rounded-lg border border-sky-100 bg-sky-50 p-4">
					<p class="text-sm font-semibold text-sky-900">Tagihan HIPPAM / Air</p>
					<p class="text-xs text-sky-700 mt-1">Total dihitung otomatis dari meter akhir dikurangi meter awal, ditambah biaya tetap Rp 5.000 dan denda. Denda otomatis Rp 2.500 jika sudah lewat jatuh tempo.</p>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="field-label">Periode</label>
						<input type="month" name="periode" value="{{ old('periode', now()->format('Y-m')) }}" class="field-input">
					</div>

					<div>
						<label class="field-label">Jatuh Tempo</label>
						<input type="date" id="jatuh_tempo" name="jatuh_tempo" value="{{ old('jatuh_tempo', now()->addDays(10)->toDateString()) }}" class="field-input">
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div>
						<label class="field-label">Meter Awal</label>
						<input type="number" name="meter_awal" value="{{ old('meter_awal') }}" min="0" class="field-input">
					</div>
					<div>
						<label class="field-label">Meter Akhir</label>
						<input type="number" name="meter_akhir" value="{{ old('meter_akhir') }}" min="0" class="field-input">
					</div>
					<div>
						<label class="field-label">Denda</label>
						<input type="number" id="denda" name="denda" value="{{ old('denda', 0) }}" min="0" class="field-input">
						<p class="mt-1 text-xs text-gray-500">Otomatis Rp 2.500 saat lewat jatuh tempo. Bisa diubah manual.</p>
					</div>
				</div>

				<div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
					<div class="font-semibold text-gray-900">Rumus otomatis</div>
					<div class="mt-1">Pemakaian x Rp 1.500 + biaya tetap Rp 5.000 + denda</div>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label class="field-label">Tanggal Bayar</label>
					<input type="date" name="tanggal_bayar" required class="field-input">
				</div>

				<div id="manual-jumlah-wrapper">
					<label class="field-label">Jumlah</label>
					<input type="number" name="jumlah" value="{{ old('jumlah') }}" min="1" class="field-input">
					<p class="mt-1 text-xs text-gray-500">Untuk tagihan non-air, isi manual. Untuk Air, total dihitung otomatis.</p>
				</div>
			</div>

			<div>
				<label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
				<textarea name="keterangan" rows="4" class="w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900"></textarea>
			</div>

			<div class="space-y-3 pt-2">
				<button
					type="submit"
					@disabled($wargas->isEmpty() || $jenisPembayarans->isEmpty())
					class="btn-primary"
				>
					Simpan Pembayaran
				</button>

				@if($wargas->isEmpty() || $jenisPembayarans->isEmpty())
					<p style="color:#b91c1c;font-size:13px;">Tombol simpan dinonaktifkan karena data warga atau jenis pembayaran belum lengkap.</p>
				@endif

				<a href="{{ route('pembayaran.index') }}" class="btn-secondary">
					Kembali
				</a>
			</div>
		</form>

		<script>
			function isiDendaOtomatis() {
				const dendaInput = document.getElementById('denda');
				const jatuhTempoInput = document.getElementById('jatuh_tempo');

				if (!dendaInput || !jatuhTempoInput || !jatuhTempoInput.value) {
					return;
				}

				const today = new Date();
				today.setHours(0, 0, 0, 0);
				const jatuhTempo = new Date(jatuhTempoInput.value + 'T00:00:00');

				// Hanya auto-set jika denda masih 0 agar override manual tetap dihormati.
				if (Number(dendaInput.value || 0) === 0) {
					dendaInput.value = jatuhTempo < today ? 2500 : 0;
				}
			}

			function toggleAirFields() {
				const jenisSelect = document.getElementById('jenis_pembayaran_id');
				const selected = jenisSelect.options[jenisSelect.selectedIndex];
				const jenisNama = (selected?.dataset?.nama || '').toLowerCase();
				const isAir = jenisNama.includes('air');
				document.getElementById('air-fields').style.display = isAir ? 'block' : 'none';
				document.getElementById('manual-jumlah-wrapper').style.display = isAir ? 'none' : 'block';
				const jumlahInput = document.querySelector('input[name="jumlah"]');
				if (jumlahInput) {
					jumlahInput.required = !isAir;
				}
				if (isAir) {
					isiDendaOtomatis();
				}
			}

			document.addEventListener('DOMContentLoaded', function () {
				toggleAirFields();
				const jatuhTempoInput = document.getElementById('jatuh_tempo');
				if (jatuhTempoInput) {
					jatuhTempoInput.addEventListener('change', isiDendaOtomatis);
				}
			});
		</script>
	</div>
</div>

@endsection