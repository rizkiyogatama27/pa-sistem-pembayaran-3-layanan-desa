<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\EventDonasi;
use App\Models\Pembayaran;
use App\Models\Warga;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $warga = null;

        if (($user?->verification_status ?? 'pending') === 'approved' && $user?->warga_id) {
            $warga = Warga::query()->whereKey($user->warga_id)->first();
        }

        $pembayarans = collect();

        if ($warga) {
            $pembayarans = Pembayaran::with('jenisPembayaran')
                ->where('warga_id', $warga->id)
                ->orderByDesc('tanggal_bayar')
                ->get();
        }

        $totalTagihan = $pembayarans->count();
        $totalLunas = $pembayarans->where('status', 'paid')->count();
        $totalBelumLunas = max($totalTagihan - $totalLunas, 0);
        $tagihanTerbaru = $pembayarans->take(5);
        $totalBulanIni = $pembayarans
            ->filter(fn ($item) => Carbon::parse($item->tanggal_bayar)->isSameMonth(now()))
            ->sum('jumlah');
        $pembayaranTerakhirLunas = $pembayarans->firstWhere('status', 'paid');

        $jenisTarget = ['Sampah', 'Air', 'Donasi'];
        $layananPembayaran = collect($jenisTarget)->map(function ($namaJenis) use ($pembayarans) {
            $item = $pembayarans->first(function ($pembayaran) use ($namaJenis) {
                $nama = strtolower((string) optional($pembayaran->jenisPembayaran)->nama);

                return str_contains($nama, strtolower($namaJenis));
            });

            return [
                'nama' => $namaJenis,
                'item' => $item,
            ];
        });

        $eventDonasiAktif = EventDonasi::query()
            ->withSum('kontribusis as total_terkumpul', 'nominal')
            ->where('status', 'aktif')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $hasDonasiBerjalan = $pembayarans->contains(function ($item) {
            $namaJenis = strtolower((string) optional($item->jenisPembayaran)->nama);

            return str_contains($namaJenis, 'donasi')
                && ($item->status ?? 'pending') === 'pending'
                && (int) ($item->jumlah ?? 0) > 0;
        });

        $showDonasiShortcut = $eventDonasiAktif->isNotEmpty() || $hasDonasiBerjalan;

        $riwayatAir = $pembayarans->filter(function ($item) {
            $nama = strtolower((string) optional($item->jenisPembayaran)->nama);
            return str_contains($nama, 'air') && $item->pemakaian_air !== null;
        })
        ->sortBy('periode')
        ->take(-6)
        ->values()
        ->map(function ($item) {
            $periodeStr = $item->periode ?: Carbon::parse($item->tanggal_bayar)->format('Y-m');
            // Format periode e.g., "2026-06" to "Jun 2026"
            try {
                $formatted = Carbon::createFromFormat('Y-m', $periodeStr)->translatedFormat('M Y');
            } catch (\Exception $e) {
                $formatted = $periodeStr;
            }
            return [
                'periode' => $formatted,
                'pemakaian' => $item->pemakaian_air
            ];
        });

        return view('user.dashboard', compact(
            'warga',
            'totalTagihan',
            'totalLunas',
            'totalBelumLunas',
            'tagihanTerbaru',
            'totalBulanIni',
            'pembayaranTerakhirLunas',
            'layananPembayaran',
            'eventDonasiAktif',
            'showDonasiShortcut',
            'riwayatAir'
        ));
    }

    public function tagihan()
    {
        $user = Auth::user();
        $jenis = trim((string) request('jenis', ''));
        $periode = trim((string) request('periode', ''));
        $status = trim((string) request('status', ''));

        $warga = null;

        if (($user?->verification_status ?? 'pending') === 'approved' && $user?->warga_id) {
            $warga = Warga::query()->whereKey($user->warga_id)->first();
        }

        $pembayarans = collect();
        $jenisOptions = JenisPembayaran::query()->orderBy('nama')->pluck('nama');

        if ($warga) {
            $pembayarans = Pembayaran::with('jenisPembayaran')
                ->where('warga_id', $warga->id)
                ->where(function ($baseQuery) use ($status) {
                    if ($status === 'draft') {
                        return;
                    }

                    $baseQuery->where('status', '!=', 'pending')
                        ->orWhere(function ($draftQuery) {
                            $draftQuery->where('status', 'pending')
                                ->where('jumlah', '>', 0);
                        });
                })
                ->when($jenis, function ($query, $selectedJenis) {
                    $query->whereHas('jenisPembayaran', function ($jenisQuery) use ($selectedJenis) {
                        $jenisQuery->whereRaw('LOWER(nama) like ?', ['%' . strtolower($selectedJenis) . '%']);
                    });
                })
                ->when($periode, function ($query, $selectedPeriode) {
                    $query->where(function ($periodQuery) use ($selectedPeriode) {
                        $periodQuery->where('periode', $selectedPeriode)
                            ->orWhere(function ($subQuery) use ($selectedPeriode) {
                                $subQuery->whereNull('periode')
                                    ->whereRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m') = ?", [$selectedPeriode]);
                            });
                    });
                })
                ->when($status === 'paid', function ($query) {
                    $query->where('status', 'paid');
                })
                ->when(in_array($status, ['pending', 'belum_bayar', 'menunggu_verifikasi'], true), function ($query) use ($status) {
                    $query->where('status', 'pending')->where('jumlah', '>', 0);
                })
                ->when($status === 'draft', function ($query) {
                    $query->where('status', 'pending')
                        ->where(function ($draftQuery) {
                            $draftQuery->whereNull('jumlah')->orWhere('jumlah', '<=', 0);
                        });
                })
                ->when($status === 'rejected', function ($query) {
                    $query->where('status', 'rejected');
                })
                ->orderByDesc('tanggal_bayar')
                ->get();
        }

        $hasEventDonasiAktif = EventDonasi::query()
            ->where('status', 'aktif')
            ->exists();

        $hasDonasiBerjalan = false;
        if ($warga) {
            $hasDonasiBerjalan = Pembayaran::query()
                ->where('warga_id', $warga->id)
                ->where('status', 'pending')
                ->where('jumlah', '>', 0)
                ->whereHas('jenisPembayaran', function ($query) {
                    $query->whereRaw('LOWER(nama) like ?', ['%donasi%']);
                })
                ->exists();
        }

        $showDonasiShortcut = $hasEventDonasiAktif || $hasDonasiBerjalan;

        return view('user.tagihan', [
            'warga' => $warga,
            'pembayarans' => $pembayarans,
            'jenis' => $jenis,
            'periode' => $periode,
            'status' => $status,
            'jenisOptions' => $jenisOptions,
            'showDonasiShortcut' => $showDonasiShortcut,
        ]);
    }

    public function riwayat()
    {
        $user = Auth::user();
        $warga = null;

        if (($user?->verification_status ?? 'pending') === 'approved' && $user?->warga_id) {
            $warga = Warga::query()->whereKey($user->warga_id)->first();
        }

        $riwayat = collect();

        if ($warga) {
            $riwayat = Pembayaran::with('jenisPembayaran')
                ->where('warga_id', $warga->id)
                ->where('status', 'paid')
                ->orderByDesc('tanggal_bayar')
                ->get();
        }

        return view('user.riwayat', compact('riwayat'));
    }
}
