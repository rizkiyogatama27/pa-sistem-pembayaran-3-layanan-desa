@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:20px 22px; box-shadow:0 18px 30px rgba(24,94,168,.16); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .filter-card, .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .filter-card { padding:14px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; align-items:end; margin-bottom:14px; }
    .filter-card label { display:block; font-size:13px; color:#215d90; margin-bottom:5px; font-weight:800; }
    .filter-card input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:8px 10px; }
    .btn-primary { padding:9px 13px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; }
    .btn-secondary { padding:9px 13px; background:#fff; color:#215d90; border:1px solid #cfe0f1; border-radius:12px; text-decoration:none; font-weight:800; }
    .metric-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:14px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:auto; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
    .table-head th { color:#215d90; }
</style>

<div class="page-wrap space-y-6">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 8px;font-size:26px;font-weight:800;">Rekap Tunggakan Air</h2>
            <p style="margin:0;color:rgba(255,255,255,.84);font-size:14px;">Menampilkan tagihan Air yang sudah lewat jatuh tempo dan masih pending.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <a href="{{ route('rekap.tunggakan.csv', ['q' => $q]) }}" class="btn-primary">Export CSV</a>
            <a href="{{ route('rekap.bulan') }}" class="btn-secondary">Kembali ke Rekap Bulan</a>
            <form method="POST" action="{{ route('pembayaran.reminder-whatsapp.all') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-primary" style="background:linear-gradient(135deg,#059669,#10b981);">Kirim WA Semua Warga</button>
            </form>
        </div>
    </div>

    <form method="GET" action="{{ route('rekap.tunggakan') }}" class="filter-card">
        <div style="grid-column:span 2;min-width:0;">
            <label style="display:block;font-size:13px;color:#475569;margin-bottom:5px;">Cari Nama Warga</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Contoh: Rizki" style="width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:8px 10px;">
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="submit" class="btn-primary">Filter</button>
            <a href="{{ route('rekap.tunggakan') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px 12px;border-radius:10px;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:10px;">{{ session('error') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
        <div class="metric-card">
            <div style="font-size:12px;color:#b91c1c;">Total Tunggakan</div>
            <div style="font-size:28px;font-weight:800;color:#7f1d1d;">{{ $totalTagihan }}</div>
        </div>
        <div class="metric-card">
            <div style="font-size:12px;color:#b91c1c;">Total Nominal</div>
            <div style="font-size:28px;font-weight:800;color:#7f1d1d;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
        </div>
        <div class="metric-card">
            <div style="font-size:12px;color:#b91c1c;">Total Denda</div>
            <div style="font-size:28px;font-weight:800;color:#7f1d1d;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="table-card">
        <div style="padding:14px 14px 8px;font-size:16px;font-weight:700;color:#0f172a;">Ringkasan per Warga</div>
        <table style="width:100%;border-collapse:collapse;font-size:14px;min-width:700px;">
            <thead class="table-head">
                <tr>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">No</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Nama Warga</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Jumlah Tagihan</th>
                    <th style="text-align:right;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Total Tunggakan</th>
                    <th style="text-align:right;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Total Denda</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perWarga as $index => $item)
                    @php
                        $sudahKirimHariIni = collect($tunggakan)
                            ->where('warga_id', $item->warga->id)
                            ->every(function ($tagihan) {
                                if (! $tagihan->last_whatsapp_reminder_at) {
                                    return false;
                                }

                                return \Illuminate\Support\Carbon::parse($tagihan->last_whatsapp_reminder_at)->isToday();
                            });
                    @endphp
                    <tr>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ $index + 1 }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;">{{ $item->warga->nama ?? '-' }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ $item->jumlah_tagihan }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;text-align:right;font-weight:700;">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;text-align:right;font-weight:700;">Rp {{ number_format($item->total_denda, 0, ',', '.') }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                            <form action="{{ route('pembayaran.reminder-whatsapp.warga', $item->warga->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @if($sudahKirimHariIni)
                                    <button type="submit" disabled style="padding:7px 10px;background:#94a3b8;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:not-allowed;">
                                        Sudah Dikirim Hari Ini
                                    </button>
                                @else
                                    <button type="submit" style="padding:7px 10px;background:#075985;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                                        Kirim WA Semua
                                    </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:14px;border-bottom:1px solid #f1f5f9;color:#64748b;">Tidak ada tunggakan air.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <div style="padding:14px 14px 8px;font-size:16px;font-weight:700;color:#0f172a;">Detail Tagihan</div>
        <table style="width:100%;border-collapse:collapse;font-size:14px;min-width:950px;">
            <thead class="table-head">
                <tr>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Invoice</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Warga</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Periode</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Jatuh Tempo</th>
                    <th style="text-align:right;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Jumlah</th>
                    <th style="text-align:right;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Denda</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Status Reminder</th>
                    <th style="text-align:left;padding:10px 14px;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tunggakan as $item)
                    <tr>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;">{{ $item->invoice ?? '-' }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ $item->warga->nama ?? '-' }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">{{ $item->periode ?? '-' }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#b91c1c;font-weight:700;">{{ $item->jatuh_tempo ? \Illuminate\Support\Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y') : '-' }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;text-align:right;font-weight:700;">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a;text-align:right;font-weight:700;">Rp {{ number_format($item->denda, 0, ',', '.') }}</td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;color:#334155;">
                            @if($item->last_whatsapp_reminder_at)
                                Terkirim {{ \Illuminate\Support\Carbon::parse($item->last_whatsapp_reminder_at)->translatedFormat('d M Y') }}
                            @else
                                Belum terkirim
                            @endif
                        </td>
                        <td style="padding:10px 14px;border-bottom:1px solid #f1f5f9;">
                            <form action="{{ route('pembayaran.reminder-whatsapp', $item->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" style="padding:7px 10px;background:#0369a1;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;">
                                    Kirim WA
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:14px;border-bottom:1px solid #f1f5f9;color:#64748b;">Tidak ada detail tunggakan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection