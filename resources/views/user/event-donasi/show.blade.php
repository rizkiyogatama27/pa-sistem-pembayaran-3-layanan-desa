@extends('layouts.app')

@section('content')
<style>
    .donasi-wrap { max-width: 1024px; margin: 0 auto; padding: 24px 16px 120px; }
    .cover-card { border-radius: 18px; overflow: hidden; border: 1px solid #dce6f1; background: #fff; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
    .cover-media { height: 220px; background: radial-gradient(circle at 20% 20%, rgba(186,230,253,.95) 0%, transparent 38%), radial-gradient(circle at 85% 30%, rgba(167,243,208,.95) 0%, transparent 40%), linear-gradient(135deg, #0f172a, #1e40af 55%, #0f766e); position: relative; }
    .cover-media::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,23,42,.14), rgba(15,23,42,.50)); }
    .cover-label { position: absolute; left: 14px; top: 14px; background: rgba(255,255,255,.14); color: #fff; border: 1px solid rgba(255,255,255,.28); border-radius: 999px; padding: 5px 10px; font-size: 11px; font-weight: 800; letter-spacing: .04em; }
    .cover-content { padding: 18px; }
    .title-main { font-size: 30px; line-height: 1.15; font-weight: 900; color: #18324d; margin: 0 0 8px; }
    .subtitle-main { color: #4b5563; margin: 0; font-size: 14px; }
    .stats-grid { margin-top: 16px; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .stats-item { background: #f8fbfe; border: 1px solid #dce6f1; border-radius: 14px; padding: 12px; min-height: 88px; display: flex; flex-direction: column; justify-content: center; }
    .stats-item .label { font-size: 11px; font-weight: 800; color: #215d90; text-transform: uppercase; letter-spacing: .04em; }
    .stats-item .value { font-size: 20px; font-weight: 900; color: #18324d; margin-top: 2px; }
    .progress-track { margin-top: 12px; height: 10px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .progress-fill { height: 100%; appearance: none; border: 0; }
    .progress-fill::-webkit-progress-bar { background: #e2e8f0; }
    .progress-fill::-webkit-progress-value { background: linear-gradient(90deg, #1d5fb8, #14b8a6); }
    .progress-fill::-moz-progress-bar { background: linear-gradient(90deg, #1d5fb8, #14b8a6); }
    .progress-caption { margin-top: 6px; display: flex; justify-content: space-between; gap: 8px; font-size: 12px; color: #475569; }
    .content-grid { margin-top: 14px; display: grid; grid-template-columns: 1.25fr .95fr; gap: 14px; }
    .panel { background: #fff; border: 1px solid #dce6f1; border-radius: 16px; padding: 16px; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
    .panel-title { margin: 0 0 10px; font-size: 22px; font-weight: 900; color: #18324d; }
    .story-copy { margin: 0; color: #334155; line-height: 1.7; white-space: pre-line; }
    .tabbar { display: inline-flex; gap: 6px; background: #eef6fc; border-radius: 999px; padding: 4px; border: 1px solid #d7e6f3; }
    .tab-btn { border: 0; border-radius: 999px; padding: 8px 12px; background: transparent; color: #215d90; font-size: 12px; font-weight: 800; cursor: pointer; }
    .tab-btn.active { background: linear-gradient(135deg, #1d5fb8, #14b8a6); color: #fff; }
    .donor-list { margin-top: 12px; display: grid; gap: 8px; max-height: 420px; overflow: auto; }
    .donor-item { border: 1px solid #e2edf7; border-radius: 12px; padding: 12px 14px; display: flex; justify-content: space-between; gap: 10px; background: #fdfefe; align-items: center; }
    .donor-name { font-weight: 900; color: #18324d; }
    .donor-date { margin-top: 2px; font-size: 12px; color: #64748b; }
    .donor-amount { font-weight: 900; color: #146a2f; white-space: nowrap; }
    .form-box { margin-top: 12px; border-top: 1px solid #e2e8f0; padding-top: 12px; }
    .quick-wrap { display: flex; flex-wrap: wrap; gap: 8px; }
    .quick-btn { border: 1px solid #cfe0f1; background: #fff; color: #215d90; border-radius: 999px; padding: 7px 11px; font-size: 12px; font-weight: 900; cursor: pointer; }
    .quick-btn.active { background: linear-gradient(135deg, #1d5fb8, #14b8a6); border-color: transparent; color: #fff; }
    .input { width: 100%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 10px 12px; font-size: 14px; }
    .cta-main { width: 100%; margin-top: 8px; border: 1px solid #1d5fb8; background: linear-gradient(135deg, #1d5fb8, #14b8a6); color: #fff; border-radius: 12px; padding: 11px 14px; font-size: 14px; font-weight: 900; box-shadow: 0 10px 18px rgba(29, 95, 184, .18); }
    .sticky-cta { position: fixed; left: 0; right: 0; bottom: 0; z-index: 30; background: rgba(255,255,255,.98); border-top: 1px solid #e2e8f0; padding: 10px 16px; display: none; }
    .sticky-cta a { display: inline-flex; width: 100%; justify-content: center; align-items: center; border-radius: 11px; padding: 11px 14px; background: linear-gradient(135deg, #1d5fb8, #14b8a6); color: #fff; text-decoration: none; font-weight: 900; }
    @media (max-width: 920px) {
        .content-grid { grid-template-columns: 1fr; }
        .title-main { font-size: 26px; }
    }
    @media (max-width: 640px) {
        .stats-grid { grid-template-columns: 1fr; }
        .donasi-wrap { padding-bottom: 96px; }
        .sticky-cta { display: block; }
    }
</style>

@php
    $totalTerkumpul = (int) ($eventDonasi->kontribusis_sum_nominal ?? 0);
    $target = max((int) $eventDonasi->target_dana, 1);
    $progress = min((int) round(($totalTerkumpul / $target) * 100), 100);
@endphp

<div class="donasi-wrap">
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700" style="margin-bottom:10px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700" style="margin-bottom:10px;">{{ session('error') }}</div>
    @endif

    <section class="cover-card">
        <div class="cover-media" @if(!empty($eventDonasi->cover_image_url)) style="background-image: linear-gradient(180deg, rgba(15,23,42,.15), rgba(15,23,42,.55)), url('{{ $eventDonasi->cover_image_url }}'); background-size: cover; background-position: center;" @endif>
            <span class="cover-label">Event Donasi Aktif</span>
        </div>
        <div class="cover-content">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;">
                <div>
                    <h1 class="title-main">{{ $eventDonasi->nama_event }}</h1>
                    <p class="subtitle-main">{{ $eventDonasi->tujuan }}</p>
                </div>
                <a href="{{ route('user.event-donasi.index') }}" style="display:inline-flex;align-items:center;padding:9px 12px;background:linear-gradient(135deg,#1d5fb8,#14b8a6);color:#fff;border-radius:10px;text-decoration:none;font-weight:800;font-size:13px;box-shadow:0 8px 16px rgba(29,95,184,.16);">Kembali</a>
            </div>

            <div class="stats-grid">
                <div class="stats-item">
                    <div class="label">Dana Terkumpul</div>
                    <div class="value">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</div>
                </div>
                <div class="stats-item">
                    <div class="label">Target Dana</div>
                    <div class="value">Rp {{ number_format($eventDonasi->target_dana, 0, ',', '.') }}</div>
                </div>
                <div class="stats-item">
                    <div class="label">Penyumbang / Donasi</div>
                    <div class="value">{{ $jumlahPenyumbang }} / {{ $jumlahKontribusi }}</div>
                </div>
            </div>

            <div class="progress-track">
                <progress value="{{ $progress }}" max="100" class="progress-fill" style="width:100%;"></progress>
            </div>
            <div class="progress-caption">
                <span>Progress {{ $progress }}%</span>
                <span>
                    @if($eventDonasi->tanggal_selesai)
                        Berakhir {{ $eventDonasi->tanggal_selesai->translatedFormat('d M Y') }}
                    @else
                        Tanpa batas tanggal
                    @endif
                </span>
            </div>
        </div>
    </section>

    <div class="content-grid">
        <section class="panel">
            <h3 class="panel-title">Cerita Penggalangan Dana</h3>
            <p class="story-copy">{{ $eventDonasi->tujuan }}</p>

            <div class="form-box" id="form-donasi">
                @php
                    $verificationStatus = auth()->user()?->verification_status ?? 'pending';
                @endphp

                @if($verificationStatus === 'pending')
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700" style="margin-bottom:8px;">Akun Anda masih menunggu verifikasi admin.</div>
                @elseif($verificationStatus === 'rejected')
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700" style="margin-bottom:8px;">Akun Anda ditolak admin. Hubungi admin untuk peninjauan ulang.</div>
                @elseif($warga)
                    <div style="font-size:13px;color:#475569;margin-bottom:8px;">Donasi akan dicatat atas nama <b>{{ $warga->nama }}</b>.</div>
                @else
                    <div class="rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700" style="margin-bottom:8px;">Data warga belum terhubung ke akun ini.</div>
                @endif

                <form action="{{ route('user.event-donasi.store', $eventDonasi->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nominal Cepat</label>
                        <div class="quick-wrap">
                            <button type="button" data-donation-amount="10000" class="quick-btn">Rp 10.000</button>
                            <button type="button" data-donation-amount="25000" class="quick-btn">Rp 25.000</button>
                            <button type="button" data-donation-amount="50000" class="quick-btn">Rp 50.000</button>
                            <button type="button" data-donation-amount="100000" class="quick-btn">Rp 100.000</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal</label>
                        <input id="donation-nominal-input" class="input" type="number" name="nominal" value="{{ old('nominal', 10000) }}" min="1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode</label>
                        <input class="input" type="text" name="metode" value="{{ old('metode', 'Transfer') }}" placeholder="Contoh: Transfer / Tunai / QRIS">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                        <textarea class="input" name="catatan" rows="3" placeholder="Tulis dukungan singkat">{{ old('catatan') }}</textarea>
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;">
                        <input type="checkbox" name="anonim" value="1" {{ old('anonim') ? 'checked' : '' }}>
                        Sembunyikan nama saya sebagai Hamba Allah / Donatur Baik
                    </label>
                    <button type="submit" class="cta-main">Donasi Sekarang</button>
                </form>
            </div>
        </section>

        <section class="panel">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
                <h3 style="margin:0;font-size:20px;font-weight:800;color:#111827;">Daftar Donatur</h3>
                <div class="tabbar">
                    <button type="button" class="tab-btn active" data-tab="terbaru">Terbaru</button>
                    <button type="button" class="tab-btn" data-tab="terbesar">Terbesar</button>
                </div>
            </div>

            <div id="list-terbaru" class="donor-list">
                @forelse($kontribusiTerbaru as $item)
                    <div class="donor-item">
                        <div>
                            <div class="donor-name">{{ $item->is_anonymous ? 'Hamba Allah' : ($item->warga->nama ?? 'Donatur') }}</div>
                            <div class="donor-date">{{ $item->tanggal_donasi?->translatedFormat('d M Y') }} · {{ $item->metode ?? '-' }}</div>
                        </div>
                        <div class="donor-amount">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                    </div>
                @empty
                    <div class="donor-item"><div class="donor-date">Belum ada donasi pada event ini.</div></div>
                @endforelse
            </div>

            <div id="list-terbesar" class="donor-list" style="display:none;">
                @forelse($kontribusiTerbesar as $item)
                    <div class="donor-item">
                        <div>
                            <div class="donor-name">{{ $item->is_anonymous ? 'Donatur Baik' : ($item->warga->nama ?? 'Donatur') }}</div>
                            <div class="donor-date">{{ $item->tanggal_donasi?->translatedFormat('d M Y') }} · {{ $item->metode ?? '-' }}</div>
                        </div>
                        <div class="donor-amount">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                    </div>
                @empty
                    <div class="donor-item"><div class="donor-date">Belum ada donasi pada event ini.</div></div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<div class="sticky-cta">
    <a href="#form-donasi">Donasi Sekarang</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nominalInput = document.getElementById('donation-nominal-input');
        const quickButtons = document.querySelectorAll('.quick-btn');
        const tabButtons = document.querySelectorAll('.tab-btn');
        const listTerbaru = document.getElementById('list-terbaru');
        const listTerbesar = document.getElementById('list-terbesar');

        quickButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const amount = button.getAttribute('data-donation-amount');
                if (nominalInput) {
                    nominalInput.value = amount;
                    nominalInput.focus();
                }

                quickButtons.forEach(function (item) {
                    item.classList.remove('active');
                });
                button.classList.add('active');
            });
        });

        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const tab = button.getAttribute('data-tab');
                tabButtons.forEach(function (item) {
                    item.classList.remove('active');
                });
                button.classList.add('active');

                if (tab === 'terbesar') {
                    listTerbaru.style.display = 'none';
                    listTerbesar.style.display = 'grid';
                } else {
                    listTerbaru.style.display = 'grid';
                    listTerbesar.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
