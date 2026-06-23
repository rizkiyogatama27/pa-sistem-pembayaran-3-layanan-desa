@extends('layouts.app')

@section('content')
<style>
    .page-header { margin-bottom: 24px; }
    .page-title { font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.01em; }
    .page-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; }
    
    .status-alert { background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 600; margin-bottom: 24px; display: flex; align-items: center; gap: 8px; }
    
    .table-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); overflow: hidden; }
    .modern-table { width: 100%; border-collapse: collapse; text-align: left; }
    .modern-table th { background: #f8fafc; padding: 14px 16px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
    .modern-table td { padding: 16px; font-size: 14px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .modern-table tbody tr:hover { background: #f8fafc; }
    .modern-table tbody tr:last-child td { border-bottom: none; }
    
    .user-info { display: flex; flex-direction: column; }
    .user-name { font-weight: 700; color: #0f172a; }
    .user-id { font-size: 12px; color: #64748b; margin-top: 2px; }
    
    .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-verified { background: #dcfce3; color: #166534; }
    .badge-rejected { background: #fee2e2; color: #b91c1c; }
    
    .ocr-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 8px; font-size: 13px; }
    .ocr-val { font-size: 16px; font-weight: 800; color: #0f172a; }
    .ocr-meta { font-size: 11px; color: #64748b; margin-top: 2px; }
    .ocr-err { font-size: 11px; color: #ef4444; margin-top: 2px; font-weight: 600; }
    
    .photo-thumb { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid #cbd5e1; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; display: block; }
    .photo-thumb:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    
    .action-group { display: flex; flex-direction: column; gap: 8px; min-width: 200px; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; border: none; outline: none; text-decoration: none; }
    .btn-approve { background: #10b981; color: white; }
    .btn-approve:hover { background: #059669; }
    .btn-reject { background: #ef4444; color: white; }
    .btn-reject:hover { background: #dc2626; }
    .btn-audit { background: #f59e0b; color: white; }
    .btn-audit:hover { background: #d97706; }
    
    .reject-input { width: 100%; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; margin-bottom: 6px; outline: none; transition: border-color 0.2s; }
    .reject-input:focus { border-color: #ef4444; }
    .reject-form { display: flex; flex-direction: column; background: #fef2f2; padding: 8px; border-radius: 8px; border: 1px dashed #fca5a5; }
    
    .empty-state { padding: 48px 24px; text-align: center; color: #64748b; }
    .empty-icon { width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5; }
</style>

<div class="container mx-auto p-6" style="max-width: 1200px;">
    <div class="page-header">
        <h1 class="page-title">Verifikasi Laporan Meter</h1>
        <div class="page-subtitle">Periksa foto meteran warga dan cocokkan dengan angka hasil otomatis.</div>
    </div>

    @if(session('status'))
        <div class="status-alert">
            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="table-card">
        @if($readings->isEmpty())
            <div class="empty-state">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <div style="font-size:16px;font-weight:700;color:#334155;">Belum ada laporan masuk</div>
                <div style="font-size:14px;margin-top:4px;">Laporan meteran dari warga yang berstatus pending akan muncul di sini.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Info Warga & Tagihan</th>
                            <th>Meter Awal</th>
                            <th>Meter Akhir (Upload)</th>
                            <th>Deteksi Otomatis (OCR)</th>
                            <th>Foto Bukti</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($readings as $r)
                        <tr>
                            <td style="font-weight:700;color:#64748b;">#{{ $r->id }}</td>
                            <td>
                                <div class="user-info">
                                    <div class="user-name">{{ $r->warga?->nama ?? 'Tidak diketahui' }}</div>
                                    <div class="user-id">ID: {{ $r->warga_id }} &bull; {{ $r->pembayaran?->invoice ?? '-' }}</div>
                                </div>
                            </td>
                            <td>
                                <span style="font-size:16px;font-weight:700;color:#475569;">{{ $r->meter_awal }}</span>
                            </td>
                            <td>
                                <span style="font-size:16px;font-weight:800;color:#0ea5e9;">{{ $r->meter_akhir }}</span>
                            </td>
                            <td>
                                <div class="ocr-box">
                                    <div class="ocr-val">{{ $r->ocr_meter_akhir ?? 'Gagal' }}</div>
                                    <div class="ocr-meta">Mesin: {{ $r->ocr_engine ?? '-' }}</div>
                                    @if($r->ocr_confidence !== null)
                                        <div class="ocr-meta">Akurasi: {{ $r->ocr_confidence }}%</div>
                                    @endif
                                    @if($r->ocr_error)
                                        <div class="ocr-err">{{ $r->ocr_error }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if(empty($r->meter_photo))
                                    <span style="color:#94a3b8;font-size:12px;">Tidak ada</span>
                                @elseif(str_starts_with($r->meter_photo, 'http'))
                                    <a href="{{ $r->meter_photo }}" target="_blank" title="Klik untuk perbesar">
                                        <img src="{{ $r->meter_photo }}" class="photo-thumb" alt="Foto Meter" onerror="this.src='/logo-simp-mld.png'" />
                                    </a>
                                @else
                                    <div style="font-size:11px; color:#ef4444; border: 1px solid #fca5a5; background: #fef2f2; padding: 4px; border-radius: 4px; text-align: center; width: 60px; word-break: break-all;">
                                        Gagal Upload
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $r->status === 'pending_verification' ? 'badge-pending' : ($r->status === 'verified' ? 'badge-verified' : 'badge-rejected') }}">
                                    {{ str_replace('_', ' ', $r->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-group">
                                    <form action="{{ route('admin.meter.verify.approve', $r->id) }}" method="post" style="display: flex; flex-direction: column; gap: 6px;">
                                        @csrf
                                        <div style="display:flex; flex-direction: column; gap:4px; background:#f0fdf4; padding:6px; border-radius:6px; border:1px solid #bbf7d0;">
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                                <span style="font-size:11px; font-weight:700; color:#166534; white-space:nowrap;">Awal:</span>
                                                <input type="number" name="koreksi_meter_awal" value="{{ $r->meter_awal }}" class="reject-input" style="margin-bottom:0; font-weight:800; color:#0f172a; border-color:#86efac; text-align:center; padding:4px; width:80px;" title="Koreksi meter awal" required />
                                            </div>
                                            <div style="display:flex; align-items:center; justify-content:space-between; gap:4px;">
                                                <span style="font-size:11px; font-weight:700; color:#166534; white-space:nowrap;">Akhir:</span>
                                                <input type="number" name="koreksi_meter_akhir" value="{{ $r->meter_akhir }}" class="reject-input" style="margin-bottom:0; font-weight:800; color:#0f172a; border-color:#86efac; text-align:center; padding:4px; width:80px;" title="Koreksi meter akhir" required />
                                            </div>
                                        </div>
                                        <button class="btn btn-approve" style="width: 100%;">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Setujui (Approve)
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.meter.verify.reject', $r->id) }}" method="post" class="reject-form">
                                        @csrf
                                        <input type="text" name="reason" class="reject-input" placeholder="Alasan tolak (wajib)..." required />
                                        <button class="btn btn-reject" style="width: 100%;">
                                            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Tolak (Reject)
                                        </button>
                                    </form>

                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($readings->hasPages())
                <div style="padding: 16px; border-top: 1px solid #e2e8f0; background: #f8fafc;">
                    {{ $readings->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
