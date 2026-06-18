@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Rekap Pembayaran per Warga</h2>
            <p class="text-sm text-gray-500 mt-1">Lihat rincian pembayaran setiap warga beserta totalnya.</p>
        </div>
        <a href="{{ route('rekap.warga.csv', ['q' => $q, 'status' => $status]) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-sky-700 text-white hover:bg-sky-800">Export CSV</a>
    </div>

    <form method="GET" action="{{ route('rekap.warga') }}" class="bg-white rounded-xl border border-gray-200 p-4 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Nama Warga</label>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Contoh: Rizki" class="w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                <option value="">Semua</option>
                <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                <option value="paid" @selected(($status ?? '') === 'paid')>Lunas</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800">Filter</button>
            <a href="{{ route('rekap.warga') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Reset</a>
        </div>
    </form>

    @php
        $totalBaris = 0;
        $totalNominal = 0;
        foreach ($wargas as $warga) {
            foreach ($warga->pembayarans as $item) {
                $totalBaris++;
                $totalNominal += (int) $item->jumlah;
            }
        }
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Transaksi Ditampilkan</p>
            <p class="text-3xl font-semibold mt-1">{{ $totalBaris }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-sm text-gray-500">Total Nominal</p>
            <p class="text-3xl font-semibold mt-1">Rp {{ number_format($totalNominal,0,',','.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Warga</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jenis Pembayaran</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @php $no = 1; @endphp
                    @foreach($wargas as $warga)
                        @forelse($warga->pembayarans as $p)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $no++ }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $warga->nama }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $p->jenisPembayaran->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if(($p->status ?? 'pending') === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Lunas</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($p->jumlah,0,',','.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $no++ }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ $warga->nama }}</td>
                                <td colspan="3" class="px-4 py-3 text-sm text-gray-500">Belum ada pembayaran</td>
                            </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection