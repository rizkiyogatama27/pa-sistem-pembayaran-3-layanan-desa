<?php

namespace App\Http\Controllers;

use App\Models\EventDonasi;
use App\Models\EventDonasiKontribusi;
use App\Models\Warga;
use App\Support\AdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventDonasiKontribusiController extends Controller
{
    public function index(EventDonasi $eventDonasi): View
    {
        $kontribusis = EventDonasiKontribusi::query()
            ->with('warga')
            ->where('event_donasi_id', $eventDonasi->id)
            ->orderByDesc('tanggal_donasi')
            ->paginate(10);

        $wargas = Warga::query()->orderBy('nama')->get();

        return view('event-donasi.kontribusi', compact('eventDonasi', 'kontribusis', 'wargas'));
    }

    // Public listing of verified contributors (no admin actions)
    public function publicIndex(EventDonasi $eventDonasi): View
    {
        $kontribusis = EventDonasiKontribusi::query()
            ->with('warga')
            ->where('event_donasi_id', $eventDonasi->id)
            ->where('status', 'paid')
            ->orderByDesc('tanggal_donasi')
            ->paginate(10);

        return view('event-donasi.public-kontribusi', compact('eventDonasi', 'kontribusis'));
    }


    public function store(Request $request, EventDonasi $eventDonasi): RedirectResponse
    {
        $validated = $request->validate([
            'warga_id' => ['nullable', 'exists:wargas,id'],
            'tanggal_donasi' => ['required', 'date'],
            'nominal' => ['required', 'integer', 'min:1'],
            'metode' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['event_donasi_id'] = $eventDonasi->id;
        $validated['status'] = 'pending';
        $validated['invoice'] = 'DON-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

        $kontribusi = EventDonasiKontribusi::create($validated);

        AdminActivity::log('event-donasi', 'add_contribution', 'Menambahkan kontribusi ke event donasi.', [
            'event_donasi_id' => $eventDonasi->id,
            'kontribusi_id' => $kontribusi->id,
            'nominal' => $kontribusi->nominal,
        ]);

        return redirect()->route('event-donasi.kontribusi.index', $eventDonasi)
            ->with('success', 'Kontribusi donasi berhasil ditambahkan dan menunggu verifikasi admin.');
    }

    // Admin: verifikasi kontribusi
    public function verify(EventDonasi $eventDonasi, EventDonasiKontribusi $kontribusi): RedirectResponse
    {
        if ($kontribusi->event_donasi_id !== $eventDonasi->id) {
            return back()->with('error', 'Data kontribusi tidak sesuai dengan event.');
        }
        $kontribusi->status = 'paid';
        $kontribusi->save();
        AdminActivity::log('event-donasi', 'verify_contribution', 'Verifikasi kontribusi donasi.', [
            'event_donasi_id' => $eventDonasi->id,
            'kontribusi_id' => $kontribusi->id,
        ]);
        return back()->with('success', 'Kontribusi berhasil diverifikasi.');
    }

    // Admin: batalkan verifikasi
    public function unverify(EventDonasi $eventDonasi, EventDonasiKontribusi $kontribusi): RedirectResponse
    {
        if ($kontribusi->event_donasi_id !== $eventDonasi->id) {
            return back()->with('error', 'Data kontribusi tidak sesuai dengan event.');
        }
        $kontribusi->status = 'pending';
        $kontribusi->save();
        AdminActivity::log('event-donasi', 'unverify_contribution', 'Membatalkan verifikasi kontribusi donasi.', [
            'event_donasi_id' => $eventDonasi->id,
            'kontribusi_id' => $kontribusi->id,
        ]);
        return back()->with('success', 'Verifikasi kontribusi dibatalkan.');
    }

    public function destroy(EventDonasi $eventDonasi, EventDonasiKontribusi $kontribusi): RedirectResponse
    {
        if ($kontribusi->event_donasi_id !== $eventDonasi->id) {
            return back()->with('error', 'Data kontribusi tidak sesuai dengan event.');
        }

        $kontribusi->delete();

        AdminActivity::log('event-donasi', 'delete_contribution', 'Menghapus kontribusi donasi.', [
            'event_donasi_id' => $eventDonasi->id,
            'kontribusi_id' => $kontribusi->id,
        ]);

        return redirect()->route('event-donasi.kontribusi.index', $eventDonasi)
            ->with('success', 'Kontribusi donasi berhasil dihapus.');
    }
}
