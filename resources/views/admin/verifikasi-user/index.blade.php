@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 34px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color: #fff; border-radius: 20px; padding: 18px 20px; display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .table-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow-x:auto; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .table-head { background: linear-gradient(135deg, #eff6ff, #ecfeff); }
    .table-head th { color:#215d90; }
    .btn-approve { background:linear-gradient(135deg, #1d5fb8, #14b8a6); color:#fff; }
    .btn-reject { background:#ef4444; color:#fff; }
</style>

<div class="page-wrap space-y-6">
    <div class="hero-card">
        <div>
            <h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">Verifikasi User Baru</h2>
            <p style="margin:0;font-size:14px;color:#cbd5e1;">Daftar user yang mendaftar dan menunggu verifikasi admin.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">{{ session('error') }}</div>
    @endif

    <div class="table-card">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="table-head">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">NIK</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No. KK</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @php $adaUserWarga = false; @endphp
                @foreach($users as $user)
                    @if($user->role !== 'admin')
                        @php $adaUserWarga = true; @endphp
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->nik ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $user->kk ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.verifikasi-user.verifikasi', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-approve px-4 py-2 rounded-lg font-bold border border-transparent shadow text-sm transition duration-150">Verifikasi</button>
                                </form>
                                <form action="{{ route('admin.verifikasi-user.tolak', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-reject px-4 py-2 rounded-lg font-bold border border-transparent shadow text-sm transition duration-150 ml-2">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
                @if(!$adaUserWarga)
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">Tidak ada user baru yang perlu diverifikasi.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
