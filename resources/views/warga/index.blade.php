@extends('layouts.app')

@section('content')

<style>
	.page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
	.hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); }
	.panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:hidden; box-shadow:0 10px 22px rgba(15,23,42,.05); }
	.panel-head { padding:16px 20px; border-bottom:1px solid #e5edf5; }
	.filter-input { flex:1; padding:8px 12px; border:1px solid #cfe0f1; border-radius:12px; font-size:14px; }
	.btn-primary { padding:8px 14px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; }
	.btn-secondary { padding:8px 14px; background:#fff; color:#215d90; border:1px solid #cfe0f1; border-radius:12px; text-decoration:none; font-weight:800; }
	.btn-danger { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; background:#ef4444; color:#fff; text-decoration:none; font-weight:800; }
	.btn-muted { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; background:#fff; font-weight:800; }
	.table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
	.table-head th { color:#215d90; }
</style>

<div class="page-wrap space-y-6">
	<div class="hero-card">
		<div>
			<h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">Data Warga Desa</h2>
			<p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">Kelola data warga, NIK, alamat, dan nomor HP.</p>
		</div>
		<a href="{{ route('warga.create') }}" class="btn-danger">
			+ Tambah Warga
		</a>
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

	<div class="panel-card">
		<div class="panel-head">
			<form action="{{ route('warga.index') }}" method="GET" style="display:flex;gap:8px;max-width:600px;">
				<input
					type="text"
					name="q"
					value="{{ $search ?? '' }}"
					placeholder="Cari nama, NIK, atau alamat"
					style="flex:1;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;"
				>
				<button type="submit" class="btn-primary">Cari</button>
				<a href="{{ route('warga.index') }}" class="btn-secondary">Reset</a>
			</form>
		</div>

		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200">
				<thead class="table-head">
					<tr>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No KK</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Keluarga</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">NIK</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Alamat & HP</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100 bg-white">
					@forelse ($wargas as $w)
						<tr>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $wargas->firstItem() + $loop->index }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $w->keluarga?->no_kk ?? '-' }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $w->keluarga?->nama_keluarga ?? '-' }}</td>
							<td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $w->nama }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $w->nik }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								<div>{{ $w->alamat }}</div>
								@if($w->no_hp)
									<div class="text-xs text-gray-500 mt-1">{{ $w->no_hp }}</div>
								@endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								@if($w->status === 'aktif')
									<span style="background:#dcfce7;color:#166534;padding:4px 8px;border-radius:6px;font-size:12px;font-weight:700;">Aktif</span>
								@elseif($w->status === 'nonaktif')
									<span style="background:#fee2e2;color:#991b1b;padding:4px 8px;border-radius:6px;font-size:12px;font-weight:700;">Non-Aktif</span>
								@else
									<span style="background:#f1f5f9;color:#475569;padding:4px 8px;border-radius:6px;font-size:12px;font-weight:700;text-transform:capitalize;">{{ $w->status }}</span>
								@endif
							</td>
							<td class="px-4 py-3 text-sm">
								<a href="{{ route('warga.edit',$w->id) }}" class="btn-muted">Edit & Status</a>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="8" style="padding:16px;text-align:center;color:#64748b;">Belum ada data warga</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<div style="padding:16px 20px;border-top:1px solid #e2e8f0;">
			{{ $wargas->links() }}
		</div>
	</div>
</div>

@endsection