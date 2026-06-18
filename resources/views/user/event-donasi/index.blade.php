@extends('layouts.app')

@section('content')
<style>
    .donasi-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 34px; }
    .hero-card {
        background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%);
        color: #fff;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 18px 30px rgba(24, 94, 168, .16);
        position: relative;
        overflow: hidden;
    }
    .hero-card::before,
    .hero-card::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
    }
    .hero-card::before { width: 180px; height: 180px; right: -60px; top: -90px; }
    .hero-card::after { width: 140px; height: 140px; left: -70px; bottom: -70px; }
    .hero-card > * { position: relative; z-index: 1; }
    .hero-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
    .hero-stat { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.14); border-radius: 14px; padding: 12px; min-height: 88px; display: flex; flex-direction: column; justify-content: center; }
    .hero-stat .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .9; }
    .hero-stat .value { font-size: 22px; line-height: 1.1; font-weight: 900; margin-top: 4px; }
    .hero-stat .caption { font-size: 12px; opacity: .85; margin-top: 4px; }
    .grid-donasi { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .event-card {
        display: block;
        background: #fff;
        border: 1px solid #dce6f1;
        border-radius: 16px;
        overflow: hidden;
        color: inherit;
        text-decoration: none;
        box-shadow: 0 10px 22px rgba(15, 23, 42, .05);
        transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        min-height: 100%;
    }
    .event-card:hover { transform: translateY(-2px); border-color: #b8c8dc; box-shadow: 0 16px 28px rgba(15, 23, 42, .08); }
    .event-badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28); color: #fff; font-size: 11px; font-weight: 800; letter-spacing: .04em; }
    .event-body { padding: 16px; display: flex; flex-direction: column; gap: 2px; }
    .event-title { font-size: 18px; font-weight: 900; color: #18324d; margin: 0; }
    .event-desc { margin-top: 4px; font-size: 13px; color: #64748b; line-height: 1.55; }
    .event-status { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 800; }
    .event-progress { margin-top: 14px; }
    .progress-track { width: 100%; height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden; }
    .progress-fill { height: 10px; background: linear-gradient(90deg, #1d5fb8, #14b8a6); border-radius: 999px; }
    .progress-meta { display: flex; justify-content: space-between; gap: 10px; margin-top: 8px; font-size: 12px; color: #64748b; }
    .empty-card { background: #fff; border: 1px dashed #a7c3df; border-radius: 16px; padding: 18px; color: #215d90; }
    @media (max-width: 768px) {
        .hero-stats, .grid-donasi { grid-template-columns: 1fr; }
    }
</style>

@php
    $totalEvent = isset($events) ? $events->count() : 0;
    $totalTerkumpul = isset($events) ? (int) $events->sum('total_terkumpul') : 0;
    $totalTarget = isset($events) ? max((int) $events->sum('target_dana'), 1) : 1;
    $overallProgress = min((int) round(($totalTerkumpul / $totalTarget) * 100), 100);
@endphp

<div class="donasi-wrap space-y-6">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:24px;font-weight:900;">Event Donasi Aktif</h2>
            <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">Ikut program donasi sukarela yang sedang berlangsung dengan tampilan yang lebih rapi dan serasi.</p>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="label">Event Aktif</div>
                <div class="value">{{ $totalEvent }}</div>
                <div class="caption">Program yang bisa dibuka</div>
            </div>
            <div class="hero-stat">
                <div class="label">Dana Terkumpul</div>
                <div class="value">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</div>
                <div class="caption">Gabungan semua event</div>
            </div>
            <div class="hero-stat">
                <div class="label">Progress Global</div>
                <div class="value">{{ $overallProgress }}%</div>
                <div class="caption">Terhadap target total</div>
            </div>
        </div>
    </div>

    <div class="grid-donasi">
        @forelse($events as $event)
            @php
                $target = max((int) $event->target_dana, 1);
                $terkumpul = (int) ($event->total_terkumpul ?? 0);
                $progress = min((int) round(($terkumpul / $target) * 100), 100);
            @endphp
            <a href="{{ route('user.event-donasi.show', $event->id) }}" class="event-card">
                @if(!empty($event->cover_image_url))
                    <div style="height:170px;position:relative;background-image:linear-gradient(180deg,rgba(15,23,42,.10),rgba(15,23,42,.56)),url('{{ $event->cover_image_url }}');background-size:cover;background-position:center;">
                        <span style="position:absolute;left:12px;top:12px;" class="event-badge">Event Aktif</span>
                    </div>
                @else
                    <div style="height:170px;background:radial-gradient(circle at 20% 20%,rgba(186,230,253,.95) 0%,transparent 36%),radial-gradient(circle at 85% 30%,rgba(167,243,208,.95) 0%,transparent 40%),linear-gradient(135deg,#0f172a,#1e40af 55%,#0f766e);position:relative;">
                        <span style="position:absolute;left:12px;top:12px;" class="event-badge">Event Aktif</span>
                    </div>
                @endif
                <div class="event-body">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="event-title">{{ $event->nama_event }}</div>
                            <div class="event-desc">{{ $event->tujuan }}</div>
                        </div>
                        <span class="event-status">Aktif</span>
                    </div>

                    <div class="event-progress">
                        <div class="progress-track">
                            <div class="progress-fill" style="width: <?php echo e($progress); ?>%;"></div>
                        </div>
                        <div class="progress-meta">
                            <span>Terkumpul Rp {{ number_format($terkumpul,0,',','.') }}</span>
                            <span>{{ $progress }}%</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="empty-card">Belum ada event donasi aktif.</div>
        @endforelse
    </div>
</div>
@endsection
