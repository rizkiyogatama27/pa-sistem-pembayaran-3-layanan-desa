@extends('layouts.app')

@section('content')
<style>
    .portal-wrap { max-width: 1140px; margin: 0 auto; padding: 24px 20px 40px; }
    .portal-title { font-size: 24px; font-weight: 900; color: #0f766e; letter-spacing: .01em; }
    .portal-subtitle { font-size: 15px; color: #4b5563; margin-top: 4px; }
    .hero-card { background: linear-gradient(135deg, #1aa77f 0%, #2563eb 100%); color: #fff; border-radius: 18px; padding: 18px; box-shadow: 0 10px 24px rgba(21, 126, 98, .13); display: flex; align-items: center; gap: 18px; }
    .hero-logo { width: 54px; height: 54px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(16, 185, 129, .13); }
    .hero-logo img { width: 38px; height: 38px; }
    .hero-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 0; }
    .hero-mini { background: rgba(255,255,255,.18); border-radius: 12px; padding: 12px; text-align: center; }
    .hero-mini b { display: block; font-size: 26px; line-height: 1; margin-bottom: 2px; }
    .hero-mini small { font-size: 13px; color: #e0f2fe; }
    .section-label { font-size: 14px; margin: 22px 0 12px; font-weight: 800; letter-spacing: .03em; color: #2563eb; text-transform: uppercase; }
    .status-pill { display: inline-block; margin-top: 8px; padding: 3px 12px; border-radius: 99px; font-size: 13px; font-weight: 700; }
    .pill-pending { background: #fef9c3; color: #b45309; }
    .pill-paid { background: #bbf7d0; color: #166534; }
    .pill-draft { background: #e0e7ef; color: #64748b; }
    .portal-empty { margin-top: 12px; border: 1.5px dashed #facc15; background: #fefce8; color: #b45309; padding: 12px 14px; border-radius: 12px; }
    .portal-error { margin-top: 12px; border: 1.5px dashed #fca5a5; background: #fef2f2; color: #b91c1c; padding: 12px 14px; border-radius: 12px; }
    .filter-card { margin-top: 18px; border: 1px solid #dbeafe; background: #f8fafc; border-radius: 14px; padding: 12px; }
    .filter-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .filter-grid label { display: block; font-size: 12px; font-weight: 700; letter-spacing: .03em; color: #2563eb; margin-bottom: 4px; text-transform: uppercase; }
    .filter-grid input, .filter-grid select { width: 100%; border: 1.5px solid #93c5fd; border-radius: 8px; padding: 8px 10px; font-size: 14px; background: #fff; }
    .btn-filter { display: inline-flex; align-items: center; justify-content: center; padding: 9px 14px; border-radius: 8px; background: #2563eb; color: #fff; text-decoration: none; border: 1px solid #2563eb; font-size: 14px; font-weight: 700; transition: background .2s; }
    .btn-filter:hover { background: #1d4ed8; }
    .btn-filter-reset { display: inline-flex; align-items: center; justify-content: center; padding: 9px 14px; border-radius: 8px; background: #fff; color: #2563eb; text-decoration: none; border: 1.5px solid #93c5fd; font-size: 14px; font-weight: 700; transition: background .2s; }
    .btn-filter-reset:hover { background: #f1f5f9; }
    .tagihan-card { border: 1.5px solid #dbeafe; border-radius: 16px; background: #fff; overflow-x: auto; margin-bottom: 18px; }
    .tagihan-table { width: 100%; min-width: 980px; border-collapse: collapse; }
    .tagihan-table thead th { font-size: 13px; text-transform: uppercase; color: #2563eb; letter-spacing: .03em; padding: 13px; border-bottom: 2px solid #e0e7ef; text-align: left; background: #f1f5f9; }
    .tagihan-table tbody td { padding: 13px; border-bottom: 1px solid #e0e7ef; font-size: 15px; color: #1e293b; }
    .tagihan-table tbody tr:last-child td { border-bottom: 0; }
    .btn-bayar { display: inline-block; padding: 7px 14px; border-radius: 8px; background: #22c55e; color: #fff; text-decoration: none; font-weight: 800; font-size: 13px; box-shadow: 0 2px 8px rgba(34,197,94,.08); transition: background .2s; }
    .btn-bayar:hover { background: #16a34a; }
    .btn-kembali { display: inline-block; margin-top: 10px; padding: 8px 16px; border-radius: 8px; border: 1.5px solid #93c5fd; color: #2563eb; text-decoration: none; font-size: 14px; font-weight: 800; background: #f8fafc; transition: background .2s; }
    .btn-kembali:hover { background: #e0e7ef; }
    @media (max-width: 900px) {
        .tagihan-table { min-width: 700px; }
    }
    @media (max-width: 720px) {
        .hero-card { flex-direction: column; align-items: flex-start; gap: 10px; }
        .hero-grid { grid-template-columns: 1fr; }
        .filter-grid { grid-template-columns: 1fr; }
        .tagihan-table { min-width: 500px; }
    }
</style>

<div class="portal-wrap">
    <div class="portal-title">Tagihan Saya</div>
    <div class="portal-subtitle">Kelola dan bayar tagihan bulanan dengan cepat</div>

    <div class="hero-card" style="margin-top: 18px;">
        <div class="hero-logo">
            <img src="/logo-simp-mld.png" alt="Logo" />
        </div>
        <div style="flex:1;">
            <div style="font-size: 15px; opacity: .97; font-weight:700;">Ringkasan Tagihan</div>
            <div style="font-size: 28px; font-weight: 900; line-height: 1.2; margin-top: 2px; color:#fff;">{{ $warga->nama ?? auth()->user()->name }}</div>
            <div class="hero-grid">
                <div class="hero-mini">
                    <b><svg style="width:26px;height:26px;display:inline-block;vertical-align:text-bottom;margin-right:4px;color:#93c5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg> {{ $pembayarans->count() }}</b>
                    <small>Total tagihan</small>
                </div>
                <div class="hero-mini">
                    <b><svg style="width:26px;height:26px;display:inline-block;vertical-align:text-bottom;margin-right:4px;color:#fca5a5;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> {{ $pembayarans->where('status', 'pending')->count() }}</b>
                    <small>Belum bayar</small>
                </div>
                <div class="hero-mini">
                    <b><svg style="width:26px;height:26px;display:inline-block;vertical-align:text-bottom;margin-right:4px;color:#86efac;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Rp {{ number_format($pembayarans->sum('jumlah'), 0, ',', '.') }}</b>
                    <small>Total nominal</small>
                </div>
            </div>
        </div>
    </div>

    @if(!$warga)
        <div class="portal-empty">Data warga belum terhubung ke akun ini. Hubungi admin untuk menghubungkan akun user ke data warga.</div>
    @endif

    @if(session('error'))
        <div class="portal-error">{{ session('error') }}</div>
    @endif

    @if(session('status'))
        <div style="margin-top: 12px; border: 1.5px solid #bbf7d0; background: #f0fdf4; color: #166534; padding: 12px 14px; border-radius: 12px; display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px;">
            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('status') }}
        </div>
    @endif



    <form method="GET" action="{{ route('user.tagihan') }}" class="filter-card">
        <div class="filter-grid">
            <div>
                <label>Periode</label>
                <input type="month" name="periode" value="{{ $periode ?: now()->format('Y-m') }}">
            </div>
            <div>
                <label>Jenis</label>
                <select name="jenis">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisOptions as $jenisNama)
                        <option value="{{ $jenisNama }}" @selected($jenis === $jenisNama)>{{ $jenisNama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected($status === 'draft')>Draft</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="paid" @selected($status === 'paid')>Lunas</option>
                </select>
            </div>
            <div style="display:flex; align-items:end; gap:8px;">
                <button type="submit" class="btn-filter">Cari</button>
                <a href="{{ route('user.tagihan') }}" class="btn-filter-reset">Reset</a>
            </div>
        </div>
    </form>

    <div class="section-label" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <span>Daftar Tagihan</span>
        <span style="font-size:12px; color:#64748b; text-transform:none;">{{ $warga?->nama ?? '-' }}</span>
    </div>

    <div class="tagihan-card">
        @if($pembayarans->isEmpty())
            <div style="padding:16px; color:#64748b;">Belum ada data tagihan.</div>
        @else
            <table class="tagihan-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Invoice</th>
                        <th>Jenis Pembayaran</th>
                        <th>Detail</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembayarans as $item)
                        @php
                            $normalizedStatus = strtolower(trim((string) ($item->status ?? 'pending')));
                            $isPaid = $normalizedStatus === 'paid';
                            $isDraft = (int) $item->jumlah <= 0;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->invoice ?? '-' }}</td>
                            <td>{{ $item->jenisPembayaran->nama ?? '-' }}</td>
                            <td>
                                @if($item->periode || $item->pemakaian_air !== null)
                                    <div style="font-weight:700; color:#0f172a;">{{ $item->periode ?? '-' }}</div>
                                    <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                        @if($item->pemakaian_air !== null)
                                            Pemakaian {{ $item->pemakaian_air }} m3
                                        @endif
                                        @if($item->denda > 0)
                                            | Denda Rp {{ number_format($item->denda, 0, ',', '.') }}
                                        @endif
                                    </div>
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->jatuh_tempo)
                                    <div style="font-weight:700; color:#0f172a;">{{ \Illuminate\Support\Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y') }}</div>
                                    @if($item->status !== 'paid' && \Illuminate\Support\Carbon::parse($item->jatuh_tempo)->isPast())
                                        <div style="font-size:12px; color:#b91c1c; font-weight:700; margin-top:2px;">Terlambat</div>
                                    @endif
                                @else
                                    <span style="color:#94a3b8; font-size:12px;">-</span>
                                @endif
                            </td>
                            <td>{{ $item->tanggal_bayar }}</td>
                            <td style="font-weight:800; color:#0f6f63;">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            <td>
                                <span class="status-pill {{ $isPaid ? 'pill-paid' : ($isDraft ? 'pill-draft' : 'pill-pending') }}">{{ $isPaid ? 'Lunas' : ($isDraft ? 'Draft' : 'Pending') }}</span>
                            </td>
                            <td>
                                @if($isDraft)
                                    <button type="button" data-pembayaran-id="{{ $item->id }}" class="js-open-scan inline-block px-3 py-1 rounded-md bg-gray-100 text-gray-700 font-bold text-sm">Isi meter (Scan)</button>
                                @elseif(!$isPaid)
                                    <a href="{{ route('pembayaran.pay', $item->id) }}" class="btn-bayar">Bayar</a>
                                @else
                                    <span style="color:#94a3b8; font-size:12px; font-weight:700;">Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <a href="{{ url('/user/dashboard') }}" class="btn-kembali">Kembali</a>

    {{-- HAPUS MENU BAWAH USER --}}
    {{--
    <div style="position:sticky;bottom:8px;margin-top:20px;background:#fff;border:1px solid #dbe4ea;border-radius:14px;padding:8px 10px;display:flex;justify-content:space-around;gap:8px;">
        <a href="{{ url('/user/dashboard') }}" style="text-decoration:none;color:#107466;font-weight:700;font-size:13px;">Beranda</a>
        <a href="#" style="text-decoration:none;color:#7a5a9e;font-weight:700;font-size:13px;">Donasi</a>
        <a href="#" style="text-decoration:none;color:#7c3aed;font-weight:700;font-size:13px;">Event</a>
        <a href="#" style="text-decoration:none;color:#4f6478;font-weight:700;font-size:13px;">Riwayat</a>
        <a href="#" style="text-decoration:none;color:#4f6478;font-weight:700;font-size:13px;">Riwayat Donasi</a>
        <a href="#" style="text-decoration:none;color:#4f6478;font-weight:700;font-size:13px;">Profil</a>
    </div>
    --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
        // create modal HTML
        const modalHtml = `
        <div id="scan-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:60;align-items:center;justify-content:center;">
            <div style="background:#fff;padding:18px;border-radius:12px;max-width:520px;width:100%;">
                <h3 style="font-weight:800;margin-bottom:8px">Scan Foto Meter</h3>
                <form id="scan-form">
                    <input type="hidden" name="pembayaran_id" id="scan-pembayaran-id" />
                    <div style="margin-bottom:8px;">
                        <label>Meter akhir (input manual jika perlu)</label>
                        <input id="scan-meter-akhir" name="meter_akhir" type="number" class="w-full p-2 border" />
                    </div>
                    <div style="margin-bottom:8px;">
                        <label>Foto meter</label>
                        <input id="scan-photo" name="meter_photo" type="file" accept="image/*" class="w-full" required />
                    </div>
                    <div id="scan-preview" style="margin-bottom:8px;display:none;"></div>
                    <div id="scan-result" style="margin-bottom:8px;display:none;background:#f8fafc;padding:8px;border-radius:6px;"></div>
                    <div style="display:flex;gap:8px;justify-content:flex-end;">
                        <button type="button" id="scan-cancel" style="padding:8px 12px;border-radius:8px;border:1px solid #d1d5db;background:#fff">Batal</button>
                        <button type="submit" id="scan-submit" style="padding:8px 12px;border-radius:8px;background:#2563eb;color:#fff">Kirim & Scan</button>
                    </div>
                </form>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

        const modal = document.getElementById('scan-modal');
        const form = document.getElementById('scan-form');
        const photoInput = document.getElementById('scan-photo');
        const preview = document.getElementById('scan-preview');
        const resultBox = document.getElementById('scan-result');
        const pembayaranIdInput = document.getElementById('scan-pembayaran-id');
        const meterInput = document.getElementById('scan-meter-akhir');

        document.querySelectorAll('.js-open-scan').forEach(btn => {
                btn.addEventListener('click', function () {
                        const pid = this.getAttribute('data-pembayaran-id');
                        pembayaranIdInput.value = pid;
                        meterInput.value = '';
                        preview.innerHTML = '';
                        preview.style.display = 'none';
                        resultBox.style.display = 'none';
                        modal.style.display = 'flex';
                });
        });

        document.getElementById('scan-cancel').addEventListener('click', function () {
                modal.style.display = 'none';
        });

        photoInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const url = URL.createObjectURL(file);
                preview.style.display = 'block';
                preview.innerHTML = `<img src="${url}" style="max-width:100%;border-radius:8px;" />`;
        });

        form.addEventListener('submit', function (e) {
                e.preventDefault();
                resultBox.style.display = 'none';
                const fd = new FormData(form);
                // append CSRF token if available
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (tokenMeta && !fd.has('_token')) fd.append('_token', tokenMeta.getAttribute('content'));

                const submitBtn = document.getElementById('scan-submit');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengirim...';

                fetch("{{ route('meter.self-report.store') }}", {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                }).then(r => r.json()).then(data => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Kirim & Scan';
                        if (data && data.ok) {
                                resultBox.style.display = 'block';
                                resultBox.style.backgroundColor = '#f0fdf4';
                                resultBox.style.color = '#166534';
                                resultBox.style.border = '1px solid #bbf7d0';
                                resultBox.innerHTML = `
                                    <div style="font-weight:700; display:flex; align-items:center; gap:6px;">
                                        <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Berhasil Mengirim Laporan!
                                    </div>
                                    <div style="margin-top:4px; font-size:13px;">Foto meteran telah masuk ke antrean verifikasi Admin Desa. Halaman akan dimuat ulang...</div>`;
                                setTimeout(() => { window.location.reload(); }, 2000);
                        } else {
                                resultBox.style.display = 'block';
                                resultBox.innerHTML = '<div style="color:#b91c1c;font-weight:700">Gagal: ' + (data.message || 'Unknown error') + '</div>';
                        }
                }).catch(err => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Kirim & Scan';
                        resultBox.style.display = 'block';
                        resultBox.innerHTML = '<div style="color:#b91c1c;font-weight:700">Error: ' + (err.message || err) + '</div>';
                });
        });
});
</script>
@endpush
