@extends('layouts.app')

@section('title', 'Event Donasi')

@section('content')
<style>
    .page-wrap { max-width: 1180px; margin: 0 auto; padding: 26px 18px 44px; }
    .dashboard-title { font-size: 24px; font-weight: 900; color: #18324d; margin: 0; }
    .dashboard-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:26px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); position:relative; overflow:hidden; }
    .hero-card::before,
    .hero-card::after { content:''; position:absolute; border-radius:999px; background:rgba(255,255,255,.08); }
    .hero-card::before { width:220px; height:220px; right:-90px; top:-120px; }
    .hero-card::after { width:180px; height:180px; left:-80px; bottom:-90px; }
    .hero-card > * { position:relative; z-index:1; }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .panel-head { padding:16px 20px; border-bottom:1px solid #e5edf5; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
    .filter-input { flex:1; padding:10px 12px; border:1px solid #cfe0f1; border-radius:12px; font-size:14px; }
    .btn-primary { padding:10px 14px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; }
    .btn-secondary { padding:10px 14px; background:#fff; color:#215d90; border:1px solid #cfe0f1; border-radius:12px; text-decoration:none; font-weight:800; }
    .table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
    .table-head th { color:#215d90; }
    .tag { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; }
    .tag-active { background:#d6f5dc; color:#146a2f; }
    .tag-finished { background:#e0e7ef; color:#64748b; }
    .tag-draft { background:#fdecc8; color:#9a4a00; }
    .action-link { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; background:#fff; }
    .action-danger { display:inline-flex; align-items:center; padding:6px 12px; border-radius:10px; border:1px solid transparent; color:#fff; background:#ef4444; }
</style>

<div class="page-wrap space-y-6">
    <div>
        <h2 class="dashboard-title">Event Donasi</h2>
        <div class="dashboard-subtitle">Kelola program donasi, target dana, dan progres pengumpulan.</div>
    </div>

    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:30px;font-weight:900;">Kelola Event Donasi</h2>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);max-width:720px;">Tambah, edit, dan pantau program donasi dengan tampilan yang lebih bersih dan selaras dengan dashboard admin.</p>
        </div>
        <a href="{{ route('event-donasi.create') }}" class="action-link" style="background:rgba(255,255,255,.14);color:#fff;">
            + Tambah Event
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">{{ session('error') }}</div>
    @endif

    <div class="panel-card overflow-hidden">
        <div class="panel-head">
            <form action="{{ route('event-donasi.index') }}" method="GET" style="display:flex;gap:8px;max-width:600px;flex:1;">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama event, tujuan, status" class="filter-input">
                <button type="submit" class="btn-primary">Cari</button>
                <a href="{{ route('event-donasi.index') }}" class="btn-secondary">Reset</a>
            </form>
            <div class="text-xs text-gray-500">Total: {{ $events->total() }} event</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Terkumpul</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Progres</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($events as $event)
                        @php
                            $target = max((int) $event->target_dana, 1);
                            $terkumpul = (int) ($event->total_terkumpul ?? 0);
                            $progress = min((int) round(($terkumpul / $target) * 100), 100);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $events->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                @if(!empty($event->cover_image_url))
                                    <div style="margin-bottom:6px;">
                                        <img src="{{ $event->cover_image_url }}" alt="Cover" onerror="this.onerror=null;this.src='https://via.placeholder.com/90x48?text=No+Image'" style="width:90px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;box-shadow:0 1px 4px 0 rgba(0,0,0,0.04);">
                                    </div>
                                @endif
                                <div>{{ $event->nama_event }}</div>
                                <div class="text-xs text-gray-500">{{ $event->tujuan }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($event->target_dana,0,',','.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($terkumpul,0,',','.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <div class="w-28 bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 bg-emerald-500" style="width: <?php echo e($progress); ?>%;"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $progress }}%</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="tag {{ $event->status === 'aktif' ? 'tag-active' : ($event->status === 'selesai' ? 'tag-finished' : 'tag-draft') }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('event-donasi.kontribusi.index', $event->id) }}" class="action-link">Kontribusi</a>
                                    <a href="{{ route('event-donasi.edit', $event->id) }}" class="action-link">Edit</a>
                                    <form action="{{ route('event-donasi.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Hapus event donasi ini?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada event donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px;border-top:1px solid #e2e8f0;">{{ $events->links() }}</div>
    </div>
</div>
@endsection
