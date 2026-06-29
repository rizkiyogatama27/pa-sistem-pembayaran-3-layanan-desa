<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\Keluarga;
use App\Models\Pembayaran;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WargaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $wargas = Warga::query()
            ->with('keluarga')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhereHas('keluarga', function ($innerQuery) use ($search) {
                        $innerQuery->where('no_kk', 'like', "%{$search}%")
                            ->orWhere('nama_keluarga', 'like', "%{$search}%");
                    });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('warga.index', compact('wargas', 'search'));
    }

    public function create()
    {
        $keluargas = Keluarga::query()
            ->orderBy('nama_keluarga')
            ->get();

        return view('warga.create', compact('keluargas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:255', Rule::unique('wargas', 'nik')],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'keluarga_id' => ['nullable', 'exists:keluargas,id'],
            'no_kk_keluarga' => ['nullable', 'string', 'max:32', Rule::unique('keluargas', 'no_kk')],
            'nama_keluarga' => ['nullable', 'string', 'max:255'],
        ], [
            'nik.unique' => 'NIK sudah terdaftar. Gunakan NIK yang berbeda.',
        ]);

        $keluargaId = $this->resolveKeluargaId($validated);

        if (! empty($validated['keluarga_id']) && ! empty($validated['no_kk_keluarga'])) {
            return back()->withInput()->withErrors([
                'keluarga_id' => 'Pilih keluarga yang sudah ada ATAU isi data keluarga baru, jangan keduanya.',
            ]);
        }

        $warga = Warga::create([
            'keluarga_id' => $keluargaId,
            'nama' => $validated['nama'],
            'nik' => $validated['nik'],
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'] ?? null,
        ]);

        $periode = now()->format('Y-m');
        $tanggalTagihan = now()->toDateString();
        $jatuhTempo = now()->addDays(10)->toDateString();

        $jenisPembayarans = JenisPembayaran::query()
            ->where(function ($query) {
                $query->where('nominal', '>', 0)
                    ->orWhereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->whereRaw('LOWER(nama) not like ?', ['%donasi%'])
            ->get();

        foreach ($jenisPembayarans as $jenisPembayaran) {
            $isAir = str_contains(strtolower((string) $jenisPembayaran->nama), 'air');

            $payload = [
                'warga_id' => $warga->id,
                'jenis_pembayaran_id' => $jenisPembayaran->id,
                'tanggal_bayar' => $tanggalTagihan,
                'periode' => $periode,
                'status' => 'pending',
                'invoice' => 'INV-AUTO-' . now()->format('YmdHis') . '-' . $warga->id . '-' . $jenisPembayaran->id . '-' . random_int(100, 999),
            ];

            if ($isAir) {
                $payload = array_merge($payload, [
                    'meter_awal' => 0,
                    'meter_akhir' => 0,
                    'pemakaian_air' => 0,
                    'tarif_per_meter' => 1500,
                    'biaya_tetap' => 5000,
                    'denda' => 0,
                    'jatuh_tempo' => $jatuhTempo,
                    'jumlah' => 0,
                    'keterangan' => 'Tagihan HIPPAM otomatis periode ' . $periode . '. Silakan update meter akhir sebelum pembayaran.',
                ]);
            } else {
                $payload = array_merge($payload, [
                    'jumlah' => (int) $jenisPembayaran->nominal,
                    'keterangan' => 'Tagihan otomatis periode ' . $periode,
                ]);
            }

            Pembayaran::create($payload);
        }

        return redirect()->route('warga.index')
            ->with('success', 'Data warga berhasil ditambahkan dan tagihan bulan ini dibuat otomatis.');
    }

    // =====================
    // EDIT
    // =====================
    public function edit($id)
    {
        $warga = \App\Models\Warga::findOrFail($id);
        $keluargas = \App\Models\Keluarga::all();
        return view('warga.edit', compact('warga', 'keluargas'));
    }

    // =====================
    // UPDATE
    // =====================
    public function update(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:255', Rule::unique('wargas', 'nik')->ignore($warga->id)],
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:aktif,nonaktif,pindah,meninggal'],
            'keluarga_id' => ['nullable', 'exists:keluargas,id'],
            'no_kk_keluarga' => ['nullable', 'string', 'max:32', Rule::unique('keluargas', 'no_kk')->ignore($warga->keluarga_id)],
            'nama_keluarga' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['keluarga_id']) && ! empty($validated['no_kk_keluarga'])) {
            return back()->withInput()->withErrors([
                'keluarga_id' => 'Pilih keluarga yang sudah ada ATAU isi data keluarga baru, jangan keduanya.',
            ]);
        }

        $keluargaId = $this->resolveKeluargaId($validated);

        $warga->update([
            'nik' => $validated['nik'],
            'nama' => $validated['nama'],
            'alamat' => $validated['alamat'],
            'no_hp' => $validated['no_hp'] ?? null,
            'status' => $validated['status'],
            'keluarga_id' => $keluargaId,
        ]);

        return redirect()->route('warga.index')
                         ->with('success', 'Data warga berhasil diperbarui');
    }

    // =====================
    // DELETE (SOFT DELETE VIA STATUS)
    // =====================
    public function destroy($id)
    {
        $warga = Warga::findOrFail($id);
        $warga->update(['status' => 'nonaktif']);

        return redirect()->route('warga.index')
                         ->with('success', 'Data warga berhasil dinonaktifkan (Tidak dihapus permanen untuk menjaga riwayat tagihan).');
    }

    private function resolveKeluargaId(array $validated): ?int
    {
        if (! empty($validated['keluarga_id'])) {
            return (int) $validated['keluarga_id'];
        }

        if (empty($validated['no_kk_keluarga']) || empty($validated['nama_keluarga'])) {
            return null;
        }

        $keluarga = Keluarga::query()->firstOrCreate(
            ['no_kk' => trim((string) $validated['no_kk_keluarga'])],
            ['nama_keluarga' => trim((string) $validated['nama_keluarga'])]
        );

        return (int) $keluarga->id;
    }
}