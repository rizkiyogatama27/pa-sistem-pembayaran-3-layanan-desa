<?php

namespace App\Http\Controllers;

use App\Models\DonationPayment;
use App\Models\EventDonasi;
use App\Models\EventDonasiKontribusi;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserEventDonasiController extends Controller
{
    public function index(): View
    {
        $events = EventDonasi::query()
            ->withSum('kontribusis as total_terkumpul', 'nominal')
            ->withCount('kontribusis')
            ->where('status', 'aktif')
            ->orderByDesc('id')
            ->get();

        return view('user.event-donasi.index', compact('events'));
    }

    public function show(EventDonasi $eventDonasi): View
    {
        $eventDonasi->loadSum('kontribusis', 'nominal');

        $baseKontribusiQuery = EventDonasiKontribusi::query()
            ->with('warga')
            ->where('event_donasi_id', $eventDonasi->id)
            ->where('status', 'paid');

        $kontribusiTerbaru = (clone $baseKontribusiQuery)
            ->orderByDesc('tanggal_donasi')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $kontribusiTerbesar = (clone $baseKontribusiQuery)
            ->orderByDesc('nominal')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $jumlahKontribusi = (clone $baseKontribusiQuery)->count();
        $jumlahPenyumbang = (clone $baseKontribusiQuery)->distinct('warga_id')->count('warga_id');

        $warga = $this->resolveWarga();

        return view('user.event-donasi.show', compact(
            'eventDonasi',
            'kontribusiTerbaru',
            'kontribusiTerbesar',
            'jumlahKontribusi',
            'jumlahPenyumbang',
            'warga'
        ));
    }

    public function store(Request $request, EventDonasi $eventDonasi): RedirectResponse
    {
        // Validate the donation form
        $validated = $request->validate([
            'nominal' => ['required', 'integer', 'min:1000'],
            'catatan' => ['nullable', 'string'],
            'anonim' => ['nullable', 'boolean'],
        ]);

        if ($eventDonasi->status !== 'aktif') {
            return back()->with('error', 'Event ini belum aktif atau sudah selesai.');
        }

        $warga = $this->resolveWarga();

        if (!$warga) {
            return back()->with('error', 'Data warga belum terhubung ke akun ini.');
        }

        if (empty($warga->no_hp)) {
            return back()->with('error', 'Nomor HP warga belum diisi. Lengkapi dulu agar notifikasi bisa dikirim.');
        }

        // Create donation payment record
        $donationPayment = DonationPayment::create([
            'event_donasi_id' => $eventDonasi->id,
            'warga_id' => $warga->id,
            'is_anonymous' => $request->boolean('anonim'),
            'jumlah' => (int) $validated['nominal'],
            'catatan' => trim((string) ($validated['catatan'] ?? '')),
            'status' => 'pending',
        ]);

        // Redirect to payment gateway
        return redirect()->route('donation-payment.pay', $donationPayment);
    }

    public function history(): View
    {
        $warga = $this->resolveWarga();

        $kontribusis = EventDonasiKontribusi::query()
            ->with('eventDonasi')
            ->when($warga, function ($query) use ($warga) {
                $query->where('warga_id', $warga->id);
            }, function ($query) {
                $query->whereRaw('1 = 0');
            })
            ->orderByDesc('tanggal_donasi')
            ->paginate(10);

        return view('user.event-donasi.riwayat', compact('kontribusis'));
    }

    private function resolveWarga(): ?Warga
    {
        $user = Auth::user();

        if (($user?->verification_status ?? 'pending') === 'approved' && $user?->warga_id) {
            return Warga::query()->whereKey($user->warga_id)->first();
        }

        if ($user?->keluarga_id) {
            return Warga::query()->where('keluarga_id', $user->keluarga_id)->orderBy('nama')->first();
        }

        return null;
    }
}
