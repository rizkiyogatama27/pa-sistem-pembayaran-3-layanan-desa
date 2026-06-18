@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
    
    .hero-card {
        background: linear-gradient(135deg, #1f2937, #0f172a);
        color: #f8fafc;
        border-radius: 24px;
        padding: 32px 36px;
        margin-bottom: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.1);
        position: relative;
        overflow: hidden;
    }
    .hero-card::before {
        content: "";
        position: absolute;
        top: -50%; left: -50%;
        width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
        pointer-events: none;
    }

    .filter-card {
        background: #141413;
        border: 1px solid #2c2c28;
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .filter-card label { display: block; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #b8b8b0; margin-bottom: 8px; }
    .filter-card input, .filter-card select { 
        width: 100%; 
        height: 48px;
        background: #1f1f1c; 
        border: 1px solid #4b4b46; 
        color: #f5f5f2; 
        border-radius: 12px; 
        padding: 0 14px; 
        font-size: 15px; 
        outline: none;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        transition: border-color 0.2s;
    }
    .filter-card input:focus, .filter-card select:focus { border-color: #0ea5e9; }

    .btn-primary { 
        height: 48px;
        padding: 0 24px; 
        background: #3d3d37; 
        color: #ffffff; 
        border: 1px solid #5a5a53;
        border-radius: 12px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: all 0.2s;
    }
    .btn-primary:hover { background: #4b4b44; }

    .btn-secondary { 
        height: 48px;
        padding: 0 24px; 
        background: #2c2c28; 
        color: #ffffff; 
        border: 1px solid #5a5a53; 
        border-radius: 12px; 
        text-decoration: none; 
        font-weight: 600; 
        font-size: 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .btn-secondary:hover { background: #3d3d37; }

    .table-card { 
        background: #fff; 
        border: 1px solid #e2e8f0; 
        border-radius: 20px; 
        box-shadow: 0 10px 25px rgba(15,23,42,0.04); 
        overflow: hidden; 
    }
    .table-card table { width: 100%; border-collapse: collapse; min-width: 900px; font-size: 14px; }
    .table-card th { 
        text-align: left; 
        padding: 16px 20px; 
        background: #f8fafc; 
        color: #64748b; 
        font-weight: 800;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0; 
    }
    .table-card td { 
        padding: 16px 20px; 
        border-bottom: 1px solid #f1f5f9; 
        color: #334155;
    }
    .table-card tr:last-child td { border-bottom: none; }
    .table-card tr:hover td { background: #f8fafc; }
    
    .badge-action {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 12px;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .badge-module {
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
    }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <h2 style="margin:0 0 10px;font-size:32px;font-weight:900;position:relative;z-index:1;"><i class="fa-solid fa-shield-halved" style="margin-right:10px;opacity:0.9;"></i> Keamanan & Audit</h2>
        <p style="margin:0;color:#94a3b8;font-size:15px;position:relative;z-index:1;max-width:600px;">Kontrol akses dan log aktivitas admin. Pantau semua perubahan data untuk transparansi dan keamanan sistem.</p>
    </div>

    <form method="GET" action="{{ route('admin.activity.index') }}" class="filter-card" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:end;">
        <div>
            <label>Cari Aksi / Deskripsi</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Contoh: login, update...">
        </div>
        <div>
            <label>Modul</label>
            <select name="module">
                <option value="">Semua Modul</option>
                @foreach($moduleOptions as $moduleItem)
                    <option value="{{ $moduleItem }}" @selected($module === $moduleItem)>{{ ucfirst($moduleItem) }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            <a href="{{ route('admin.activity.index') }}" class="btn-secondary" style="flex:1;"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        </div>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Admin</th>
                    <th>Modul</th>
                    <th>Aksi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="color:#64748b;font-weight:500;">
                            <div>{{ $log->created_at?->translatedFormat('d M Y') }}</div>
                            <div style="font-size:12px;margin-top:4px;">{{ $log->created_at?->translatedFormat('H:i') }} WIB</div>
                        </td>
                        <td>
                            <div style="font-weight:600;color:#0f172a;">{{ $log->user->name ?? 'System' }}</div>
                        </td>
                        <td>
                            <span class="badge-module">{{ ucfirst($log->module) }}</span>
                        </td>
                        <td>
                            <span class="badge-action">{{ $log->action }}</span>
                        </td>
                        <td style="color:#475569;line-height:1.5;">{{ $log->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:15px;">
                            <i class="fa-solid fa-folder-open" style="font-size:32px;margin-bottom:12px;opacity:0.5;"></i><br>
                            Belum ada data aktivitas admin.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">{{ $logs->links() }}</div>
</div>
@endsection
