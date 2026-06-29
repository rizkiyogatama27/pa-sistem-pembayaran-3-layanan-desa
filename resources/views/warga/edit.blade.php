@extends('layouts.app')

@section('content')

<style>
	.page-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px 40px; }
	.hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
	.panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:24px; margin-top:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
	.field-label { display:block; font-size:14px; font-weight:700; color:#215d90; margin-bottom:4px; }
	.field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
	.btn-primary { background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:0; padding:10px 16px; border-radius:12px; font-weight:800; }
	.btn-danger { background:#ef4444; color:#fff; border:0; padding:10px 16px; border-radius:12px; font-weight:800; }
	.btn-secondary { display:inline-flex; align-items:center; border:1px solid #cfe0f1; color:#215d90; padding:10px 16px; border-radius:12px; font-weight:800; text-decoration:none; background:#fff; }
</style>

<div class="page-wrap">
	<div class="hero-card">
		<h2 class="text-2xl font-semibold" style="margin:0;">Edit Data Warga</h2>
		<p class="text-sm" style="margin:6px 0 0;color:rgba(255,255,255,.84);">Perbarui informasi warga.</p>
	</div>

	<div class="panel-card space-y-6">
		<div>
			<h2 class="text-2xl font-semibold text-gray-900">Form Warga</h2>
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

		<form action="{{ route('warga.update', $warga->id) }}" method="POST" class="space-y-4">
			@csrf
			@method('PUT')

			<div class="rounded-lg border border-gray-200 p-4 bg-gray-50 space-y-3">
				<div class="text-sm font-semibold text-gray-800">Data Keluarga (Opsional)</div>
				<p class="text-xs text-gray-500">Pilih keluarga yang sudah ada, atau isi No. KK + Nama Keluarga untuk membuat keluarga baru.</p>

				<div>
					<label class="field-label">Pilih Keluarga</label>
					<select
						name="keluarga_id"
						class="field-input"
					>
						<option value="">- Tidak dipilih -</option>
						@foreach ($keluargas as $keluarga)
							<option value="{{ $keluarga->id }}" @selected(old('keluarga_id', $warga->keluarga_id) == $keluarga->id)>
								{{ $keluarga->no_kk }} - {{ $keluarga->nama_keluarga }}
							</option>
						@endforeach
					</select>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
					<div>
						<label class="field-label">No. KK Baru</label>
						<input
							type="text"
							name="no_kk_keluarga"
							value="{{ old('no_kk_keluarga') }}"
							class="field-input"
							placeholder="Contoh: 3578123456789001"
						>
					</div>
					<div>
						<label class="field-label">Nama Keluarga Baru</label>
						<input
							type="text"
							name="nama_keluarga"
							value="{{ old('nama_keluarga') }}"
							class="field-input"
							placeholder="Contoh: Keluarga Bapak Suyono"
						>
					</div>
				</div>
			</div>

			<div>
				<label class="field-label">NIK (Nomor Induk Kependudukan)</label>
				<input 
					type="text" 
					name="nik" 
					value="{{ old('nik', $warga->nik) }}" 
					required 
					class="field-input"
				>
			</div>

			<div>
				<label class="field-label">Nama Warga</label>
				<input 
					type="text" 
					name="nama" 
					value="{{ old('nama', $warga->nama) }}" 
					required 
					class="field-input"
					placeholder="Contoh: Nama Warga"
				>
			</div>

			<div>
				<label class="field-label">Alamat</label>
				<textarea 
					name="alamat" 
					required 
					rows="3"
					class="field-input"
				>{{ old('alamat', $warga->alamat) }}</textarea>
			</div>

			<div>
				<label class="field-label">No HP</label>
				<input 
					type="text" 
					name="no_hp" 
					value="{{ old('no_hp', $warga->no_hp) }}" 
					class="field-input"
					placeholder="Contoh: 08123456789"
				>
			</div>

			<div>
				<label class="field-label">Status Warga</label>
				<select name="status" class="field-input">
					<option value="aktif" @selected(old('status', $warga->status) === 'aktif')>Aktif</option>
					<option value="nonaktif" @selected(old('status', $warga->status) === 'nonaktif')>Non-Aktif (Riwayat tetap disimpan)</option>
					<option value="pindah" @selected(old('status', $warga->status) === 'pindah')>Pindah Domisili</option>
					<option value="meninggal" @selected(old('status', $warga->status) === 'meninggal')>Meninggal</option>
				</select>
				<p class="text-xs text-gray-500 mt-1">Warga yang tidak aktif tidak akan dibuatkan tagihan baru otomatis.</p>
			</div>

			<div class="pt-4 border-t border-gray-200">
				<div class="text-sm text-gray-600 mb-3">Perbarui data warga yang diperlukan.</div>
				<div class="flex items-center gap-3 flex-wrap">
					<button
						type="submit"
						class="btn-primary"
					>
						Simpan
					</button>
					<a
						href="{{ route('warga.index') }}"
						class="btn-secondary"
					>
						Kembali
					</a>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection