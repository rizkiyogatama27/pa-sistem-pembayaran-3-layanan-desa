@extends('layouts.app')

@section('title', 'Penyumbang Event Donasi')

@section('content')
<div class="page-wrap" style="max-width:900px;margin:24px auto;padding:18px;">
    <div style="background:linear-gradient(135deg,#185ea8 0%,#1e62a1 42%,#188f78 100%);color:#fff;padding:18px;border-radius:12px;margin-bottom:14px;">
        <h2 style="margin:0;font-size:20px;font-weight:800;">Penyumbang untuk: {{ $eventDonasi->nama_event }}</h2>
        <p style="margin:6px 0 0;color:rgba(255,255,255,.9);">{{ $eventDonasi->tujuan }}</p>
    </div>

    <div style="background:#fff;border:1px solid #e6eef8;border-radius:12px;padding:12px;margin-bottom:12px;">
        <div style="font-weight:700;margin-bottom:6px;">Ringkasan</div>
        <div style="color:#333;font-size:14px;">Total Terkumpul: Rp {{ number_format($eventDonasi->kontribusis()->where('status','paid')->sum('nominal'),0,',','.') }}</div>
    </div>

    <div style="background:#fff;border:1px solid #e6eef8;border-radius:12px;padding:12px;">
        <div style="font-weight:700;margin-bottom:8px;">Daftar Penyumbang (Terverifikasi)</div>
        @if($kontribusis->isEmpty())
            <div style="color:#6b7280;padding:10px;">Belum ada penyumbang yang terverifikasi.</div>
        @else
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#374151;font-weight:700;background:#f8fafc;border-bottom:1px solid #eef2f7;">
                        <th style="padding:8px">No</th>
                        <th style="padding:8px">Nama</th>
                        <th style="padding:8px">Tanggal</th>
                        <th style="padding:8px">Nominal</th>
                        <th style="padding:8px">Metode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kontribusis as $kontribusi)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:8px;vertical-align:top">{{ $kontribusis->firstItem() + $loop->index }}</td>
                            <td style="padding:8px;vertical-align:top">{{ $kontribusi->is_anonymous ? 'Hamba Allah' : ($kontribusi->warga->nama ?? 'Anonim') }}</td>
                            <td style="padding:8px;vertical-align:top">{{ $kontribusi->tanggal_donasi?->format('d M Y') }}</td>
                            <td style="padding:8px;vertical-align:top">Rp {{ number_format($kontribusi->nominal,0,',','.') }}</td>
                            <td style="padding:8px;vertical-align:top">{{ $kontribusi->metode ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:12px">{{ $kontribusis->links() }}</div>
        @endif
    </div>

    <div style="margin-top:12px;text-align:right;"><a href="{{ route('event-donasi.index') }}" class="btn" style="background:#fff;border:1px solid #dbe3ee;padding:8px 12px;border-radius:8px;color:#0f172a;text-decoration:none;">Kembali ke daftar event</a></div>
</div>
@endsection
