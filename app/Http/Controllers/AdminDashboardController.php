<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\AdminActivityLog;
use App\Models\Pembayaran;
use App\Models\EventDonasi;
use App\Models\EventDonasiKontribusi;
use App\Models\WhatsAppReminderLog;
use App\Models\User;
use App\Models\Warga;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalWarga = Warga::count();
        $totalUser = User::count();
        $totalJenisPembayaran = JenisPembayaran::count();
        $totalPembayaran = Pembayaran::count();
        $totalLunas = Pembayaran::where('status', 'paid')->count();
        $totalPending = Pembayaran::where('status', 'pending')->count();
        $totalTunggakan = Pembayaran::where('status', 'pending')
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '<', now())
            ->whereHas('jenisPembayaran', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->count();

        $totalPemasukanBulanIni = Pembayaran::query()
            ->where('status', 'paid')
            ->whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('jumlah');

        $pembayaranTerbaru = Pembayaran::with(['warga', 'jenisPembayaran'])
            ->latest('tanggal_bayar')
            ->limit(5)
            ->get();

        $reminderHariIni = WhatsAppReminderLog::query()
            ->whereDate('created_at', now())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reminderTerkirimHariIni = (int) ($reminderHariIni['sent'] ?? 0);
        $reminderGagalHariIni = (int) ($reminderHariIni['failed'] ?? 0);
        $reminderDilewatiHariIni = (int) ($reminderHariIni['skipped'] ?? 0);

        $eventDonasiAktif = EventDonasi::where('status', 'aktif')->count();
        $eventDonasiSelesai = EventDonasi::where('status', 'selesai')->count();
        $eventDonasiTotal = EventDonasi::count();
        $eventDonasiTerkumpul = (int) EventDonasiKontribusi::sum('nominal');
        $eventDonasiPenyumbang = (int) EventDonasiKontribusi::distinct('warga_id')->count('warga_id');
        $eventDonasiTerbaru = EventDonasi::query()
            ->withSum('kontribusis as total_terkumpul', 'nominal')
            ->withCount('kontribusis')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $aktivitasTerbaru = AdminActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalWarga',
            'totalUser',
            'totalJenisPembayaran',
            'totalPembayaran',
            'totalLunas',
            'totalPending',
            'totalTunggakan',
            'totalPemasukanBulanIni',
            'pembayaranTerbaru',
            'reminderTerkirimHariIni',
            'reminderGagalHariIni',
            'reminderDilewatiHariIni',
            'eventDonasiAktif',
            'eventDonasiSelesai',
            'eventDonasiTotal',
            'eventDonasiTerkumpul',
            'eventDonasiPenyumbang',
            'eventDonasiTerbaru',
            'aktivitasTerbaru'
        ));
    }
}
