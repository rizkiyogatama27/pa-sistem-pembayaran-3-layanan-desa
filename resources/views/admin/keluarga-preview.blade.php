@extends('layouts.app')

@section('title', 'Data Keluarga')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:hidden; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .panel-head { padding:16px 20px; border-bottom:1px solid #e5edf5; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
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
            <h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">Data Keluarga</h2>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">Kelola data keluarga desa, termasuk identitas KK dan jumlah anggota.</p>
        </div>
        <a href="#" onclick="alert('Contoh aksi Tambah Keluarga. Jika kamu ACC, tombol ini akan diarahkan ke form create real.'); return false;" class="btn-danger">
            + Tambah Keluarga
        </a>
    </div>

    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
        Halaman ini masih tahap awal. Jika sudah sesuai, saya lanjutkan menjadi CRUD penuh (index/create/edit/delete + validasi relasi).
    </div>

    <div class="panel-card">
        <div class="panel-head">
            <form action="{{ route('admin.keluarga.preview') }}" method="GET" style="display:flex;gap:8px;max-width:600px;flex:1;">
                <input
                    type="text"
                    name="q"
                    value="{{ $search ?? '' }}"
                    placeholder="Cari No KK, Nama Keluarga, atau Alamat"
                    style="flex:1;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;"
                >
                <button type="submit" class="btn-primary">Cari</button>
                <a href="{{ route('admin.keluarga.preview') }}" class="btn-secondary">Reset</a>
            </form>
            <div class="text-xs text-gray-500">Total: {{ $keluargas->total() }} keluarga</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No KK</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Keluarga</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Alamat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($keluargas as $keluarga)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $keluargas->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $keluarga->no_kk }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $keluarga->nama_keluarga }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $keluarga->alamat ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $keluarga->wargas_count }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-2">
                                    <a href="#" onclick="alert('Contoh aksi Edit. Jika kamu ACC, ini jadi route edit real.'); return false;" class="btn-muted">Edit</a>
                                    <a href="#" onclick="alert('Contoh aksi Detail Anggota. Jika kamu ACC, ini jadi halaman detail keluarga.'); return false;" class="btn-muted">Detail</a>
                                    <a href="#" onclick="alert('Contoh aksi Hapus. Jika kamu ACC, akan ditambah validasi relasi sebelum hapus.'); return false;" class="btn-danger">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:16px;text-align:center;color:#64748b;">Belum ada data keluarga. Tambahkan data warga dengan keluarga, atau lanjutkan implementasi CRUD keluarga.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px;border-top:1px solid #e2e8f0;">
            {{ $keluargas->links() }}
        </div>
    </div>
</div>
@endsection
