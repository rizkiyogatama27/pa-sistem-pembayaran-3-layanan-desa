@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 34px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color: #fff; border-radius: 20px; padding: 20px; box-shadow: 0 18px 30px rgba(24, 94, 168, .16); display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .hero-chip { display:inline-flex; align-items:center; padding:10px 14px; background:rgba(255,255,255,.14); color:#fff; border-radius:12px; font-weight:800; font-size:14px; border:1px solid rgba(255,255,255,.16); }
    .panel { background:#fff; border:1px solid #dce6f1; border-radius:18px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .filter-card { background:#f8fbfe; border:1px solid #dce6f1; border-radius:18px; padding:14px; box-shadow:0 10px 22px rgba(15,23,42,.04); margin-bottom:14px; }
    .filter-card label { display:block; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#215d90; margin-bottom:5px; }
    .filter-card select { width:100%; height:48px; background:#fff; border:1px solid #cfe0f1; color:#18324d; border-radius:12px; padding:10px 12px; font-size:15px; outline:none; }
    .btn-primary { height:48px; padding:0 22px; border-radius:12px; background:linear-gradient(135deg, #1d5fb8, #14b8a6); color:#ffffff; font-weight:800; border:1px solid transparent; cursor:pointer; font-size:15px; box-shadow:0 10px 18px rgba(29,95,184,.14); }
    .btn-secondary { height:48px; padding:0 22px; border-radius:12px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; background:#fff; font-size:15px; font-weight:800; }
    .table-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:hidden; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-head { background:linear-gradient(135deg, #eff6ff, #ecfeff); }
    .table-head th { color:#215d90; }
    .status-paid { background:#d6f5dc; color:#146a2f; }
    .status-pending { background:#fdecc8; color:#9a4a00; }
    .status-badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; }
    .action-link { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; background:#fff; }
    .action-danger { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; border:1px solid transparent; color:#fff; background:#ef4444; }
</style>

<div class="page-wrap space-y-6">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">Donasi Sukarela</h2>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">Donasi bersifat tidak wajib, dikelola manual atau lewat crowdfunding.</p>
        </div>
        <span class="hero-chip">
            Manual / Crowdfunding
        </span>
    </div>

    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Data Donasi Warga</h2>
            <p class="text-sm text-gray-500 mt-1">Pantau donasi sukarela secara terpisah dari pembayaran rutin.</p>
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

    <form method="GET" action="{{ route('donasi.index') }}" class="filter-card">
        <input type="hidden" name="kategori" value="donasi">
        <div style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;">
            <div style="min-width:220px;flex:1 1 260px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#d8cff5;margin-bottom:5px;">Bulan</label>
                <select name="bulan" style="width:100%;height:48px;background:#221836;border:1px solid #4b3f67;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;">
                    @foreach(range(1,12) as $b)
                        <option value="{{ $b }}" {{ request('bulan', now()->month) == $b ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:120px;flex:1 1 120px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#d8cff5;margin-bottom:5px;">Tahun</label>
                <select name="tahun" style="width:100%;height:48px;background:#221836;border:1px solid #4b3f67;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;">
                    @for($y = date('Y')-3; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ request('tahun', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div style="min-width:220px;flex:1 1 260px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#d8cff5;margin-bottom:5px;">Jenis Donasi</label>
                <select name="jenis_pembayaran_id" style="width:100%;height:48px;background:#221836;border:1px solid #4b3f67;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisPembayarans as $jenis)
                        <option value="{{ $jenis->id }}" @selected((string) $selectedJenisPembayaranId === (string) $jenis->id)>
                            {{ $jenis->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width:220px;flex:1 1 260px;">
                <label style="display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#d8cff5;margin-bottom:5px;">Status</label>
                <select name="status" style="width:100%;height:48px;background:#221836;border:1px solid #4b3f67;color:#f5f5f2;border-radius:11px;padding:10px 12px;font-size:15px;outline:none;">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected($selectedStatus === 'pending')>Pending</option>
                    <option value="paid" @selected($selectedStatus === 'paid')>Sudah Bayar</option>
                </select>
            </div>

            <div style="display:flex;align-items:end;gap:8px;">
                <button
                    type="submit"
                    class="btn-primary"
                >
                    Cari
                </button>
                <a
                    href="{{ route('donasi.index') }}"
                    class="btn-secondary"
                >
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="table-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Invoice</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Warga</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis Donasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($pembayarans as $p)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $p->invoice ?? 'Belum ada' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $p->warga->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $p->jenisPembayaran->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($p->jumlah,0,',','.') }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($p->status == 'paid')
                                    <span class="status-badge status-paid">Lunas</span>
                                @else
                                    <span class="status-badge status-pending">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('pembayaran.invoice', $p->id) }}" class="action-link">Invoice</a>
                                    <a href="{{ route('pembayaran.edit', $p->id) }}" class="action-link">Edit</a>
                                    @if($p->status !== 'paid')
                                        <form action="{{ route('pembayaran.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data donasi ini?')" style="display:inline;">
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
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data donasi untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
