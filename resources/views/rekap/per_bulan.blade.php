@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:20px 22px; margin-bottom:18px; box-shadow:0 18px 30px rgba(24,94,168,.16); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .btn-primary { display:inline-flex; align-items:center; padding:10px 14px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border-radius:12px; text-decoration:none; font-weight:800; font-size:14px; }
    .btn-secondary { display:inline-flex; align-items:center; padding:10px 14px; background:#fff; color:#215d90; border-radius:12px; text-decoration:none; font-weight:800; font-size:14px; border:1px solid #cfe0f1; }
    .filter-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:14px; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:10px; align-items:end; margin-bottom:14px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:auto; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-head { background:linear-gradient(135deg,#eff6ff,#ecfeff); }
    .table-head th { color:#215d90; }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 8px;font-size:26px;font-weight:800;">Rekap Pembayaran Per Bulan</h2>
            <p style="margin:0;color:rgba(255,255,255,.84);font-size:14px;">Lihat total pembayaran bulanan dan export laporan PDF.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('rekap.bulan.csv', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn-primary">Export CSV</a>
            <a href="{{ route('laporan.pdf', ['tahun' => $tahun, 'bulan' => $bulan]) }}" class="btn-secondary">Download PDF</a>
        </div>
    </div>

    <form method="GET" action="{{ route('rekap.bulan') }}" class="filter-card">
        <div>
            <label style="display:block;font-size:13px;color:#475569;margin-bottom:5px;">Tahun</label>
            <select name="tahun" style="width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:8px 10px;background:#fff;">
                <option value="">Semua</option>
                @foreach($tahunOptions as $tahunItem)
                    <option value="{{ $tahunItem }}" @selected((string) $tahun === (string) $tahunItem)>{{ $tahunItem }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block;font-size:13px;color:#475569;margin-bottom:5px;">Bulan</label>
            <select name="bulan" style="width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:8px 10px;background:#fff;">
                <option value="">Semua</option>
                <option value="1" @selected((string) $bulan === '1')>Januari</option>
                <option value="2" @selected((string) $bulan === '2')>Februari</option>
                <option value="3" @selected((string) $bulan === '3')>Maret</option>
                <option value="4" @selected((string) $bulan === '4')>April</option>
                <option value="5" @selected((string) $bulan === '5')>Mei</option>
                <option value="6" @selected((string) $bulan === '6')>Juni</option>
                <option value="7" @selected((string) $bulan === '7')>Juli</option>
                <option value="8" @selected((string) $bulan === '8')>Agustus</option>
                <option value="9" @selected((string) $bulan === '9')>September</option>
                <option value="10" @selected((string) $bulan === '10')>Oktober</option>
                <option value="11" @selected((string) $bulan === '11')>November</option>
                <option value="12" @selected((string) $bulan === '12')>Desember</option>
            </select>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button type="submit" class="btn-primary" style="border:none;">Filter</button>
            <a href="{{ route('rekap.bulan') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-card">
        <div style="padding:14px 14px 8px;font-size:16px;font-weight:700;color:#0f172a;">Data Rekap Bulanan</div>
        <table style="width:100%;border-collapse:collapse;min-width:650px;font-size:14px;">
            <thead class="table-head">
                <tr>
                    <th style="text-align:left;padding:10px 14px;border:1px solid #e2e8f0;">No</th>
                    <th style="text-align:left;padding:10px 14px;border:1px solid #e2e8f0;">Bulan</th>
                    <th style="text-align:left;padding:10px 14px;border:1px solid #e2e8f0;">Tahun</th>
                    <th style="text-align:right;padding:10px 14px;border:1px solid #e2e8f0;">Total Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapBulanan as $key => $data)
                    <tr>
                        <td style="padding:10px 14px;border:1px solid #f1f5f9;color:#334155;">{{ $key+1 }}</td>
                        <td style="padding:10px 14px;border:1px solid #f1f5f9;color:#0f172a;">{{ \Carbon\Carbon::create()->month((int) $data->bulan)->translatedFormat('F') }}</td>
                        <td style="padding:10px 14px;border:1px solid #f1f5f9;color:#334155;">{{ $data->tahun }}</td>
                        <td style="padding:10px 14px;border:1px solid #f1f5f9;color:#0f172a;text-align:right;font-weight:700;">Rp {{ number_format($data->total,0,',','.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:14px;border:1px solid #f1f5f9;color:#64748b;">Belum ada data rekap</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection