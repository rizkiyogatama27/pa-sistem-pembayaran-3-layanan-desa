@extends('layouts.app')

@section('title', 'Kontribusi Event Donasi')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 34px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card, .table-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .status-pill { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; }
    .status-ok { background:#d6f5dc; color:#146a2f; }
    .status-wait { background:#fdecc8; color:#9a4a00; }
    .btn-primary { padding:8px 14px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; }
    .btn-secondary { padding:8px 14px; background:#fff; color:#215d90; border:1px solid #cfe0f1; border-radius:12px; text-decoration:none; font-weight:800; }
    .table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
    .table-head th { color:#215d90; }
</style>

<div class="page-wrap space-y-6">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">Kontribusi Event Donasi</h2>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">{{ $eventDonasi->nama_event }} - {{ $eventDonasi->tujuan }}</p>
        </div>
        <a href="{{ route('event-donasi.index') }}" class="btn-secondary">Kembali ke Event</a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 panel-card p-5 space-y-4">
            <div>
                <div class="text-sm font-semibold text-slate-600">Target Dana</div>
                <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($eventDonasi->target_dana,0,',','.') }}</div>
            </div>
            @php
                // Hanya kontribusi yang sudah diverifikasi (paid) yang dihitung
                $totalTerkumpul = (int) $eventDonasi->kontribusis()->where('status', 'paid')->sum('nominal');
                $target = max((int) $eventDonasi->target_dana, 1);
                $progress = min((int) round(($totalTerkumpul / $target) * 100), 100);
            @endphp
            <div>
                <div class="text-sm font-semibold text-slate-600">Terkumpul</div>
                <div class="text-2xl font-bold text-emerald-700">Rp {{ number_format($totalTerkumpul,0,',','.') }}</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2 overflow-hidden"><div class="h-2 bg-emerald-500" style="width: <?php echo e($progress); ?>%;"></div></div>
                <div class="text-xs text-gray-500 mt-1">{{ $progress }}% dari target</div>
            </div>

            <form action="{{ route('event-donasi.kontribusi.store', $eventDonasi->id) }}" method="POST" class="space-y-3 pt-2 border-t border-gray-200">
                @csrf
                {{-- Dropdown warga dihapus agar tidak terkesan memaksa donasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Donasi</label>
                        <input type="date" name="tanggal_donasi" value="{{ old('tanggal_donasi', now()->toDateString()) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:ring-gray-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal</label>
                        <input type="number" name="nominal" value="{{ old('nominal', 0) }}" min="1" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:ring-gray-900 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode</label>
                    <input type="text" name="metode" value="{{ old('metode') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:ring-gray-900 focus:outline-none" placeholder="Contoh: Tunai / Transfer / QRIS">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:ring-gray-900 focus:outline-none" placeholder="Catatan tambahan">{{ old('catatan') }}</textarea>
                </div>
                <button type="submit" style="width:100%;background:linear-gradient(135deg,#1d5fb8,#14b8a6);color:#fff;border:0;padding:10px 16px;border-radius:12px;font-weight:800;">Tambah Kontribusi</button>
            </form>
        </div>

        <div class="lg:col-span-2 table-card overflow-hidden">
            <div class="p-4 border-b border-gray-200 font-semibold text-gray-900">Daftar Kontribusi</div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="table-head">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Warga</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nominal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Metode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($kontribusis as $kontribusi)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusis->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $kontribusi->warga->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusi->tanggal_donasi?->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($kontribusi->nominal,0,',','.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusi->metode ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($kontribusi->status === 'paid')
                                        <span class="status-pill status-ok">Terverifikasi</span>
                                    @else
                                        <span class="status-pill status-wait">Menunggu Verifikasi</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        @if($kontribusi->status === 'pending')
                                            <form action="{{ route('event-donasi.kontribusi.verify', [$eventDonasi->id, $kontribusi->id]) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn-primary px-3 py-1.5 rounded-lg">Verifikasi</button>
                                            </form>
                                        @else
                                            <form action="{{ route('event-donasi.kontribusi.unverify', [$eventDonasi->id, $kontribusi->id]) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn-secondary px-3 py-1.5 rounded-lg">Batalkan</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('event-donasi.kontribusi.destroy', [$eventDonasi->id, $kontribusi->id]) }}" method="POST" onsubmit="return confirm('Hapus kontribusi ini?')" style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-600 text-white hover:bg-rose-700">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada kontribusi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200">{{ $kontribusis->links() }}</div>
        </div>
    </div>
</div>
@endsection
