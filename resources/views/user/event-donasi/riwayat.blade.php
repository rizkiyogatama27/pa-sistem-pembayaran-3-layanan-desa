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
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }
    .hero-card p { margin: 0; font-size: 14px; color: rgba(255,255,255,.84); }
    .btn-event {
        display: inline-flex;
        align-items: center;
        padding: 10px 14px;
        background: rgba(255,255,255,.14);
        color: #fff;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 800;
        font-size: 14px;
        border: 1px solid rgba(255,255,255,.16);
    }
    .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; align-items: stretch; }
    .summary-card { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.14); border-radius: 14px; padding: 12px; min-height: 88px; display: flex; flex-direction: column; justify-content: center; }
    .summary-card .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .9; }
    .summary-card .value { font-size: 22px; font-weight: 900; margin-top: 4px; }
    .table-card { margin-top: 16px; background: #fff; border: 1px solid #dce6f1; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
    .table-head { background: linear-gradient(135deg, #eff6ff, #ecfeff); }
    .table-head th { color: #215d90; border-bottom: 1px solid #d7e6f3; }
    .table-row td { border-bottom: 1px solid #eef2f7; }
    .event-chip { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 800; }
    .amount-cell { font-weight: 900; color: #146a2f; }
    .empty-row { color: #64748b; }
    .table-footer { padding: 14px 16px; border-top: 1px solid #e5edf5; background: #f8fbfe; }
    @media (max-width: 768px) {
        .summary-grid { grid-template-columns: 1fr; }
    }
</style>

@php
    $totalDonasi = $kontribusis->count();
    $totalNominal = (int) $kontribusis->sum('nominal');
@endphp

<div class="donasi-wrap space-y-6">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:24px;font-weight:900;">Riwayat Donasi Saya</h2>
            <p>Kontribusi pribadi Anda ke event donasi yang tersedia.</p>
        </div>
        <a href="{{ route('user.event-donasi.index') }}" class="btn-event">Lihat Event</a>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="label">Total Donasi</div>
                <div class="value">{{ $totalDonasi }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Nominal Terkumpul</div>
                <div class="value">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Halaman Saat Ini</div>
                <div class="value">Riwayat</div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-head">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Nominal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($kontribusis as $kontribusi)
                        <tr class="table-row">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusis->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                                {{ $kontribusi->eventDonasi->nama_event ?? '-' }}
                                <div class="mt-1"><span class="event-chip">Donasi Tercatat</span></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusi->tanggal_donasi?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm amount-cell">Rp {{ number_format($kontribusi->nominal,0,',','.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $kontribusi->metode ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm empty-row">Belum ada riwayat donasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer">{{ $kontribusis->links() }}</div>
    </div>
</div>
@endsection
