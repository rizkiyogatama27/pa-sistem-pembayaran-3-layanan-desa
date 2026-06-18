@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:hidden; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
    .table-head th { color:#215d90; }
    .btn-primary { display:inline-flex; align-items:center; padding:8px 16px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border-radius:12px; text-decoration:none; font-weight:800; border:0; }
    .btn-muted { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; background:#fff; font-weight:800; }
    .btn-danger { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; background:#ef4444; color:#fff; text-decoration:none; font-weight:800; border:0; }
</style>

<div class="page-wrap space-y-6">
	<div class="flex items-center justify-between gap-3 flex-wrap">
		<div>
			<h2 class="text-2xl font-semibold text-gray-900">Jenis Pembayaran</h2>
			<p class="text-sm text-gray-500 mt-1">Kelola nama tagihan, keterangan, dan nominal.</p>
		</div>

		<a href="{{ route('jenis-pembayaran.create') }}" class="btn-primary">
			+ Tambah Pembayaran
		</a>
	</div>

	@if(session('success'))
		<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
			{{ session('success') }}
		</div>
	@endif

	<div class="panel-card">
		<div class="overflow-x-auto">
			<table class="min-w-full divide-y divide-gray-200">
				<thead class="table-head">
					<tr>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Pembayaran</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nominal</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Keterangan</th>
						<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
					</tr>
				</thead>
				<tbody class="divide-y divide-gray-100 bg-white">
					@forelse ($jenisPembayarans as $j)
						<tr>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
							<td class="px-4 py-3 text-sm text-gray-800">{{ $j->nama }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($j->nominal,0,',','.') }}</td>
							<td class="px-4 py-3 text-sm text-gray-700">{{ $j->keterangan ?? '-' }}</td>
							<td class="px-4 py-3 text-sm">
								<div class="flex flex-wrap gap-2">
									<a href="{{ route('jenis-pembayaran.edit', $j->id) }}" class="btn-muted">Edit</a>
									<form action="{{ route('jenis-pembayaran.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jenis pembayaran ini?')">
										@csrf
										@method('DELETE')
										<button type="submit" class="btn-danger">Hapus</button>
									</form>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada jenis pembayaran</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>
</div>

@endsection