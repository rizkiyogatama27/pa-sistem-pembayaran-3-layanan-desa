@extends('layouts.app')

@section('title', 'Laporan Donasi')

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
    .metric-card, .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .metric-card { padding:14px; min-height:92px; display:flex; flex-direction:column; justify-content:center; }
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
</style>

<div class="page-wrap space-y-6">
    <div>
        <h2 class="dashboard-title">Laporan Donasi</h2>
        <div class="dashboard-subtitle">Ringkasan seluruh event donasi dalam satu tampilan.</div>
    </div>

    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:30px;font-weight:900;">Laporan Donasi</h2>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);max-width:720px;">Lihat ringkasan pengumpulan donasi, progres event, dan kontribusi dalam satu laporan yang lebih jelas.</p>
        </div>
        <a href="{{ route('event-donasi.index') }}" class="btn-secondary">Kembali ke Event</a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
        <div class="metric-card">
            <div style="font-size:12px;color:#64748b;">Total Event</div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;">{{ $ringkasan['total_event'] }}</div>
        </div>
        <div class="metric-card">
            <div style="font-size:12px;color:#64748b;">Event Aktif</div>
            <div style="font-size:26px;font-weight:800;color:#0f172a;">{{ $ringkasan['event_aktif'] }}</div>
        </div>
        <div class="metric-card">
            <div style="font-size:12px;color:#64748b;">Total Terkumpul</div>
            <div style="font-size:26px;font-weight:800;color:#215d90;">Rp {{ number_format($ringkasan['total_terkumpul'],0,',','.') }}</div>
        </div>
        <div class="metric-card">
            <div style="font-size:12px;color:#64748b;">Penyumbang Unik</div>
            <div style="font-size:26px;font-weight:800;color:#188f78;">{{ $ringkasan['total_penyumbang'] }}</div>
        </div>
    </div>

    <div class="panel-card overflow-hidden">
        <div class="panel-head">
            <form action="{{ route('event-donasi.laporan') }}" method="GET" style="display:flex;gap:8px;max-width:600px;flex:1;">
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari nama event atau tujuan" class="filter-input">
                <button type="submit" class="btn-primary">Cari</button>
                <a href="{{ route('event-donasi.laporan') }}" class="btn-secondary">Reset</a>
            </form>
            <div class="text-xs text-gray-500">Periode laporan event donasi</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Target</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Terkumpul</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Kontribusi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Penyumbang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Progres</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($events as $event)
                        @php
                            $target = max((int) $event->target_dana, 1);
                            $terkumpul = (int) ($event->total_terkumpul ?? 0);
                            $kontribusi = (int) ($event->jumlah_kontribusi ?? 0);
                            $penyumbang = (int) ($event->jumlah_penyumbang ?? 0);
                            $progress = min((int) round(($terkumpul / $target) * 100), 100);
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $events->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                <div>{{ $event->nama_event }}</div>
                                <div class="text-xs text-gray-500">{{ $event->tujuan }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($event->target_dana,0,',','.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($terkumpul,0,',','.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusi }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $penyumbang }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data laporan donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 20px;border-top:1px solid #e2e8f0;">{{ $events->links() }}</div>
    </div>
</div>
@endsection
