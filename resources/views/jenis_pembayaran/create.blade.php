@extends('layouts.app')

@section('content')
<style>
	.page-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px 40px; }
	.hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
	.panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:24px; margin-top:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
	.field-label { display:block; font-size:14px; font-weight:700; color:#215d90; margin-bottom:4px; }
	.field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
	.btn-primary { display:block; width:100%; padding:14px 18px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; box-shadow:0 10px 18px rgba(29,95,184,.14); }
	.btn-secondary { display:block; width:100%; padding:12px 18px; background:#fff; color:#215d90; border:1px solid #cfe0f1; border-radius:12px; font-weight:800; text-decoration:none; text-align:center; }
</style>

<div class="page-wrap">
	<div class="hero-card">
		<h2 class="text-2xl font-semibold" style="margin:0;">Tambah Jenis Pembayaran</h2>
		<p class="text-sm" style="margin:6px 0 0;color:rgba(255,255,255,.84);">Tambahkan kategori tagihan baru.</p>
	</div>

	<div class="panel-card space-y-6">
		<div>
			<h2 class="text-2xl font-semibold text-gray-900">Form Jenis Pembayaran</h2>
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

		<form action="{{ route('jenis-pembayaran.store') }}" method="POST" class="space-y-4">
			@csrf

			<div>
				<label class="field-label">Nama Pembayaran</label>
				<input type="text" name="nama" required class="field-input">
			</div>

			<div>
				<label class="field-label">Keterangan</label>
				<input type="text" name="keterangan" class="field-input">
			</div>

			<div>
				<label class="field-label">Nominal</label>
				<input type="number" name="nominal" required min="0" class="field-input">
			</div>

			<div class="space-y-3 pt-2">
				<button type="submit" class="btn-primary">
					Simpan Data Jenis Pembayaran
				</button>
				<a href="{{ route('jenis-pembayaran.index') }}" class="btn-secondary">
					Kembali
				</a>
			</div>
		</form>
	</div>
</div>

@endsection