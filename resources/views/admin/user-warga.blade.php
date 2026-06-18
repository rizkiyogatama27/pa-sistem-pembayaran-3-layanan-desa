@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color: #fff; border-radius: 20px; padding: 20px 22px; margin-bottom: 18px; box-shadow: 0 18px 30px rgba(24, 94, 168, .16); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .panel-card, .table-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .panel-card { padding:14px; }
    .info-box { background:#f0f8ff; border:1px solid #cfe0f1; color:#215d90; padding:12px 14px; border-radius:14px; margin-bottom:12px; line-height:1.6; }
    .action-link { display:inline-flex; align-items:center; padding:10px 14px; background:#fff; color:#215d90; border-radius:12px; text-decoration:none; font-weight:800; font-size:14px; border:1px solid #cfe0f1; }
    .table-head { background: linear-gradient(135deg, #eff6ff, #ecfeff); }
    .table-head th { color:#215d90; }
    .save-btn { padding:8px 12px; background:linear-gradient(135deg, #1d5fb8, #14b8a6); color:#fff; border:none; border-radius:10px; font-weight:800; cursor:pointer; }
    .select-field { min-width:220px; border:1px solid #cfe0f1; border-radius:10px; padding:8px 10px; background:#fff; }
    .status-badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; }
    .status-pending { background:#fdecc8; color:#9a4a00; }
    .status-approved { background:#d6f5dc; color:#146a2f; }
    .status-rejected { background:#fee2e2; color:#991b1b; }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 8px;font-size:26px;font-weight:800;">Pemetaan Akun User ke Data Warga</h2>
            <p style="margin:0;color:#cbd5e1;font-size:14px;">Halaman ini digunakan admin untuk menentukan akun login user milik warga yang mana, agar tagihan dan riwayat pembayaran tampil sesuai data yang benar.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="action-link">Kembali Dashboard</a>
    </div>

    <div class="info-box">
        <strong>Cara menggunakan:</strong>
        <div style="margin-top:4px;">1. Pilih akun user pada tabel.</div>
        <div>2. Sistem hanya menampilkan warga yang cocok berdasarkan NIK/KK atau relasi yang sudah ada.</div>
        <div>3. Klik <strong>Setujui</strong> jika data cocok, atau <strong>Tolak</strong> jika tidak sesuai.</div>
    </div>

    @php
        $hasPendingUsers = $users->where('verification_status', 'pending')->isNotEmpty();
    @endphp
    @if($wargas->isEmpty() && $hasPendingUsers)
        <div style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;padding:10px 12px;border-radius:10px;margin-bottom:12px;">
            Data warga masih kosong. Tambahkan data warga terlebih dahulu sebelum melakukan verifikasi dan penghubungan akun user.
        </div>
    @endif

    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px 12px;border-radius:10px;margin-bottom:12px;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:12px;">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;padding:10px 12px;border-radius:10px;margin-bottom:12px;">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="table-card overflow-auto">
        <div style="padding:14px 14px 8px;font-size:16px;font-weight:700;color:#0f172a;">Daftar Akun User dan Status Verifikasinya</div>
        <div style="padding:0 12px 12px;">
            @if($users->isEmpty())
                <p style="margin:0;color:#64748b;padding:8px;">Belum ada akun dengan role user yang dapat dipetakan.</p>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;min-width:860px;font-size:14px;">
                        <thead class="table-head">
                            <tr>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;">Nama Akun</th>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;">Email</th>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;">NIK</th>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;">KK</th>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;">Status</th>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;">Warga yang Terhubung</th>
                                <th style="text-align:left;padding:10px 12px;border:1px solid #e2e8f0;width:340px;">Pilih Warga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                @php
                                    $status = $user->verification_status ?? 'pending';
                                    $selectedWargaId = $user->warga_id;
                                    $matchingWargas = $user->matching_wargas ?? collect();
                                    $suggestedWarga = $user->suggested_warga_id ? $wargas->firstWhere('id', $user->suggested_warga_id) : null;
                                @endphp
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;color:#0f172a;">{{ $user->name }}</td>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;color:#334155;">{{ $user->email }}</td>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;color:#334155;">{{ $user->nik ?? '-' }}</td>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;color:#334155;">{{ $user->kk ?? '-' }}</td>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;">
                                        <span class="status-badge {{ $status === 'approved' ? 'status-approved' : ($status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                                            {{ $status === 'approved' ? 'Disetujui' : ($status === 'rejected' ? 'Ditolak' : 'Menunggu') }}
                                        </span>
                                    </td>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;color:#334155;">
                                        <div>{{ $user->warga->nama ?? '-' }}</div>
                                        @php
                                            $hasMatching = $matchingWargas->isNotEmpty();
                                            $availableWargas = $user->available_wargas ?? collect();
                                        @endphp
                                        @if($suggestedWarga && ! $user->warga)
                                            <div style="margin-top:4px;font-size:12px;color:#1d4ed8;font-weight:700;">Disarankan: {{ $suggestedWarga->nama }}</div>
                                        @elseif(! $hasMatching && $availableWargas->isNotEmpty())
                                            <div style="margin-top:4px;font-size:12px;color:#ea580c;">Tidak ada kecocokan NIK/KK, pilih manual.</div>
                                        @elseif($availableWargas->isEmpty() && $status === 'pending')
                                            <div style="margin-top:4px;font-size:12px;color:#dc2626;">Semua warga sudah terhubung ke akun lain.</div>
                                        @endif
                                    </td>
                                    <td style="padding:10px 12px;border:1px solid #f1f5f9;">
                                        <form action="{{ route('admin.user-warga.update', $user->id) }}" method="POST" style="display:flex;gap:8px;align-items:center;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="verification_status" value="approved">

                                            <select name="warga_id" class="select-field" @disabled($availableWargas->isEmpty())>
                                                <option value="">-- Belum Dipilih --</option>
                                                @foreach($availableWargas as $warga)
                                                    <option value="{{ $warga->id }}" @selected($selectedWargaId == $warga->id)>
                                                        {{ $warga->nama }} - {{ $warga->nik }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <button type="submit" class="save-btn">Setujui</button>
                                        </form>
                                        <form action="{{ route('admin.user-warga.update', $user->id) }}" method="POST" style="margin-top:8px;display:inline-block;">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="verification_status" value="rejected">
                                            <input type="hidden" name="warga_id" value="">
                                            <button type="submit" class="save-btn" style="background:linear-gradient(135deg,#b91c1c,#ef4444);">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
