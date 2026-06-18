<?php

namespace App\Http\Controllers;

use App\Models\EventDonasi;
use App\Models\EventDonasiKontribusi;
use App\Support\AdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventDonasiController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $events = EventDonasi::query()
            ->withCount('kontribusis')
            ->withSum('kontribusis as total_terkumpul', 'nominal')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_event', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('event-donasi.index', compact('events', 'search'));
    }

    public function laporan(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $events = EventDonasi::query()
            ->select('event_donasis.*')
            ->selectSub(function ($query) {
                $query->from('event_donasi_kontribusis')
                    ->selectRaw('COALESCE(SUM(nominal), 0)')
                    ->whereColumn('event_donasi_kontribusis.event_donasi_id', 'event_donasis.id');
            }, 'total_terkumpul')
            ->selectSub(function ($query) {
                $query->from('event_donasi_kontribusis')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('event_donasi_kontribusis.event_donasi_id', 'event_donasis.id');
            }, 'jumlah_kontribusi')
            ->selectSub(function ($query) {
                $query->from('event_donasi_kontribusis')
                    ->selectRaw('COUNT(DISTINCT warga_id)')
                    ->whereColumn('event_donasi_kontribusis.event_donasi_id', 'event_donasis.id');
            }, 'jumlah_penyumbang')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama_event', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderByDesc('total_terkumpul')
            ->paginate(10)
            ->withQueryString();

        $ringkasan = [
            'total_event' => EventDonasi::count(),
            'event_aktif' => EventDonasi::where('status', 'aktif')->count(),
            'total_terkumpul' => (int) EventDonasiKontribusi::sum('nominal'),
            'total_penyumbang' => (int) EventDonasiKontribusi::distinct('warga_id')->count('warga_id'),
        ];

        return view('event-donasi.laporan', compact('events', 'search', 'ringkasan'));
    }

    public function create(): View
    {
        return view('event-donasi.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tujuan' => ['required', 'string'],
            'cover_image' => ['nullable'],
            'target_dana' => ['required', 'integer', 'min:0'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', 'in:draft,aktif,selesai'],
        ]);

        // Remove cover_image dari validated (file object, bukan data model)
        unset($validated['cover_image']);
        $validated['cover_image_url'] = null; // Default null
        
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower($file->getClientOriginalExtension());
            
            if (in_array($ext, $allowed) && $file->getSize() <= 2048 * 1024) {
                try {
                    $path = $file->store('event-covers', 'public');
                    $validated['cover_image_url'] = basename($path);
                } catch (\Exception $e) {
                    // Silent fail
                }
            }
        }

        $validated['slug'] = Str::slug($validated['nama_event']) . '-' . Str::lower(Str::random(6));
        $event = EventDonasi::create($validated);

        AdminActivity::log('event-donasi', 'create', 'Membuat event donasi baru.', [
            'event_donasi_id' => $event->id,
            'nama_event' => $event->nama_event,
        ]);

        return redirect()->route('event-donasi.index')
            ->with('success', 'Event donasi berhasil dibuat.');
    }

    public function edit(EventDonasi $eventDonasi): View
    {
        return view('event-donasi.edit', compact('eventDonasi'));
    }

    public function update(Request $request, EventDonasi $eventDonasi): RedirectResponse
    {
        $validated = $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tujuan' => ['required', 'string'],
            'cover_image' => ['nullable'],
            'target_dana' => ['required', 'integer', 'min:0'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', 'in:draft,aktif,selesai'],
        ]);

        // Remove cover_image dari validated (file object, bukan data model)
        unset($validated['cover_image']);
        
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower($file->getClientOriginalExtension());
            
            if (in_array($ext, $allowed) && $file->getSize() <= 2048 * 1024) {
                try {
                    // Delete old image
                    $oldCoverImage = $eventDonasi->getRawOriginal('cover_image_url') ?: $eventDonasi->getRawOriginal('cover_image');
                    if (!empty($oldCoverImage)) {
                        Storage::disk('public')->delete('event-covers/' . ltrim($oldCoverImage, '/'));
                    }
                    
                    $path = $file->store('event-covers', 'public');
                    $validated['cover_image_url'] = basename($path);
                } catch (\Exception $e) {
                    // Silent fail - keep existing image
                }
            }
        }

        $eventDonasi->update($validated);

        AdminActivity::log('event-donasi', 'update', 'Memperbarui event donasi.', [
            'event_donasi_id' => $eventDonasi->id,
            'nama_event' => $eventDonasi->nama_event,
        ]);

        return redirect()->route('event-donasi.index')
            ->with('success', 'Event donasi berhasil diperbarui.');
    }

    public function destroy(EventDonasi $eventDonasi): RedirectResponse
    {
        $eventId = $eventDonasi->id;
        $eventName = $eventDonasi->nama_event;
        $kontribusiCount = $eventDonasi->kontribusis()->count();
        $coverImage = $eventDonasi->getRawOriginal('cover_image_url') ?: $eventDonasi->getRawOriginal('cover_image');

        DB::transaction(function () use ($eventDonasi) {
            $eventDonasi->kontribusis()->delete();
            $eventDonasi->delete();
        });

        if (!empty($coverImage)) {
            Storage::disk('public')->delete('event-covers/' . ltrim($coverImage, '/'));
        }

        AdminActivity::log('event-donasi', 'delete', 'Menghapus event donasi.', [
            'event_donasi_id' => $eventId,
            'nama_event' => $eventName,
            'kontribusi_deleted' => $kontribusiCount,
        ]);

        return redirect()->route('event-donasi.index')
            ->with('success', 'Event donasi berhasil dihapus.');
    }
}
