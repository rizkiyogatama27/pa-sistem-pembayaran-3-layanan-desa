<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Warga;
use App\Models\JenisPembayaran;
use App\Models\WhatsAppReminderLog;
use App\Services\WhatsAppService;
use App\Support\AdminActivity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendWhatsAppReminderJob;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar pembayaran
     */
    public function index(Request $request)
    {
        return view('pembayaran.index', $this->buildListingData($request, 'wajib'));
    }

    public function wajibIndex(Request $request)
    {
        return view('pembayaran.index', $this->buildListingData($request, 'wajib'));
    }

    public function donasiIndex(Request $request)
    {
        return view('pembayaran.donasi', $this->buildListingData($request, 'donasi'));
    }

    private function buildListingData(Request $request, string $defaultKategori): array
    {
        $selectedPeriode = (string) $request->query('periode', now()->format('Y-m'));
        $selectedJenisPembayaranId = $request->query('jenis_pembayaran_id');
        $selectedStatus = (string) $request->query('status', '');
        $selectedKategori = (string) $request->query('kategori', $defaultKategori);

        if (! in_array($selectedKategori, ['wajib', 'donasi'], true)) {
            $selectedKategori = $defaultKategori;
        }

        $pembayarans = Pembayaran::with(['warga', 'jenisPembayaran'])
            ->where(function ($query) use ($selectedPeriode) {
                $query->where('periode', $selectedPeriode)
                    ->orWhere(function ($subQuery) use ($selectedPeriode) {
                        $subQuery->whereNull('periode')
                            ->whereRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m') = ?", [$selectedPeriode]);
                    });
            })
            ->when(! empty($selectedJenisPembayaranId), function ($query) use ($selectedJenisPembayaranId) {
                $query->where('jenis_pembayaran_id', $selectedJenisPembayaranId);
            })
            ->when($selectedKategori === 'donasi', function ($query) {
                $query->whereHas('jenisPembayaran', function ($jenisQuery) {
                    $jenisQuery->whereRaw('LOWER(nama) like ?', ['%donasi%']);
                });
            })
            ->when($selectedKategori === 'wajib', function ($query) {
                $query->whereHas('jenisPembayaran', function ($jenisQuery) {
                    $jenisQuery->whereRaw('LOWER(nama) not like ?', ['%donasi%']);
                });
            })
            ->when($selectedStatus === 'paid', function ($query) {
                $query->where('status', 'paid');
            })
            ->when($selectedStatus === 'pending', function ($query) {
                $query->where('status', 'pending')
                    ->where('jumlah', '>', 0);
            })
            ->when($selectedStatus === 'belum_bayar', function ($query) {
                $query->where('status', 'pending')
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('jumlah')
                            ->orWhere('jumlah', '<=', 0);
                    });
            })
            ->leftJoin('wargas', 'pembayarans.warga_id', '=', 'wargas.id')
            ->select('pembayarans.*')
            ->orderBy('wargas.nama')
            ->orderByDesc('pembayarans.id')
            ->paginate(25)
            ->withQueryString();

        $jenisPembayarans = JenisPembayaran::query()
            ->when($selectedKategori === 'donasi', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%donasi%']);
            })
            ->when($selectedKategori === 'wajib', function ($query) {
                $query->whereRaw('LOWER(nama) not like ?', ['%donasi%']);
            })
            ->orderBy('nama')
            ->get();

        return compact(
            'pembayarans',
            'jenisPembayarans',
            'selectedPeriode',
            'selectedJenisPembayaranId',
            'selectedStatus',
            'selectedKategori'
        );
    }

    /**
     * Menampilkan form tambah pembayaran
     */
    public function create()
    {
        return redirect()->route('pembayaran.index')
            ->with('success', 'Input manual dinonaktifkan. Tagihan sekarang dibuat otomatis berdasarkan data warga dan jenis pembayaran.');
    }

    /**
     * Menyimpan data pembayaran
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warga_id' => ['required', 'exists:wargas,id'],
            'jenis_pembayaran_id' => ['required', 'exists:jenis_pembayarans,id'],
            'tanggal_bayar' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $periodeCheck = (string) ($request->input('periode') ?: now()->format('Y-m'));
        $duplicateExists = Pembayaran::query()
            ->where('warga_id', (int) $validated['warga_id'])
            ->where('jenis_pembayaran_id', (int) $validated['jenis_pembayaran_id'])
            ->where('periode', $periodeCheck)
            ->exists();

        if ($duplicateExists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tagihan duplikat terdeteksi untuk warga, jenis pembayaran, dan periode yang sama.');
        }

        $jenisPembayaran = JenisPembayaran::findOrFail($validated['jenis_pembayaran_id']);

        if ($this->isAirJenis($jenisPembayaran)) {
            $airValidated = $request->validate([
                'periode' => ['nullable', 'string', 'max:20'],
                'meter_awal' => ['required', 'integer', 'min:0'],
                'meter_akhir' => ['required', 'integer', 'min:0', 'gte:meter_awal'],
                'denda' => ['nullable', 'integer', 'min:0'],
                'jatuh_tempo' => ['nullable', 'date'],
            ]);

            $meterAwal = (int) $airValidated['meter_awal'];
            $meterAkhir = (int) $airValidated['meter_akhir'];
            $pemakaianAir = max($meterAkhir - $meterAwal, 0);
            $tarifPerMeter = 1500;
            $biayaTetap = 5000;
            $dendaInput = (int) ($airValidated['denda'] ?? 0);

            $validated['periode'] = $airValidated['periode'] ?: now()->format('Y-m');
            $validated['meter_awal'] = $meterAwal;
            $validated['meter_akhir'] = $meterAkhir;
            $validated['pemakaian_air'] = $pemakaianAir;
            $validated['tarif_per_meter'] = $tarifPerMeter;
            $validated['biaya_tetap'] = $biayaTetap;
            $validated['jatuh_tempo'] = ! empty($airValidated['jatuh_tempo'])
                ? $airValidated['jatuh_tempo']
                : now()->addDays(10)->toDateString();
            $denda = $this->resolveDendaOtomatis($dendaInput, $validated['jatuh_tempo'], $validated['tanggal_bayar'] ?? null);
            $validated['denda'] = $denda;
            $validated['jumlah'] = ($pemakaianAir * $tarifPerMeter) + $biayaTetap + $denda;

            $keteranganTambahan = trim((string) $request->input('keterangan'));
            $keteranganAir = [
                'Tagihan HIPPAM periode ' . $validated['periode'],
                'Meter awal: ' . $meterAwal,
                'Meter akhir: ' . $meterAkhir,
                'Pemakaian: ' . $pemakaianAir . ' m3',
                'Tarif: Rp ' . number_format($tarifPerMeter, 0, ',', '.'),
                'Biaya tetap: Rp ' . number_format($biayaTetap, 0, ',', '.'),
                $denda > 0 ? 'Denda: Rp ' . number_format($denda, 0, ',', '.') : null,
                $keteranganTambahan !== '' ? $keteranganTambahan : null,
            ];

            $validated['keterangan'] = implode("\n", array_values(array_filter($keteranganAir)));
        } else {
            $manualValidated = $request->validate([
                'jumlah' => ['required', 'numeric', 'min:1'],
            ]);

            $validated['jumlah'] = (int) $manualValidated['jumlah'];
        }

        $validated['invoice'] = 'INV-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        $validated['status'] = 'pending';

        $created = Pembayaran::create($validated);

        AdminActivity::log('pembayaran', 'create', 'Membuat tagihan pembayaran baru.', [
            'pembayaran_id' => $created->id,
            'warga_id' => $created->warga_id,
            'jumlah' => $created->jumlah,
        ]);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil disimpan');
    }

    /**
     * Menampilkan form edit pembayaran
     */
    public function edit($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $wargas = Warga::orderBy('nama')->get();
        $jenisPembayarans = JenisPembayaran::orderBy('nama')->get();

        return view('pembayaran.edit', compact(
            'pembayaran',
            'wargas',
            'jenisPembayarans'
        ));
    }

    /**
     * Update data pembayaran
     */
    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        $validated = $request->validate([
            'warga_id' => ['required', 'exists:wargas,id'],
            'jenis_pembayaran_id' => ['required', 'exists:jenis_pembayarans,id'],
            'tanggal_bayar' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,paid'],
        ]);

        // Hardening: status harus diproses lewat alur pembayaran (online/tunai), bukan dari form edit.
        if (($validated['status'] ?? $pembayaran->status) !== $pembayaran->status) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Status tidak bisa diubah dari form edit. Gunakan proses Bayar Online / Bayar Tunai.');
        }

        $validated['status'] = $pembayaran->status;

        $periodeCheck = (string) ($request->input('periode') ?: $pembayaran->periode ?: now()->format('Y-m'));
        $duplicateExists = Pembayaran::query()
            ->where('warga_id', (int) $validated['warga_id'])
            ->where('jenis_pembayaran_id', (int) $validated['jenis_pembayaran_id'])
            ->where('periode', $periodeCheck)
            ->where('id', '!=', $pembayaran->id)
            ->exists();

        if ($duplicateExists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tagihan duplikat terdeteksi untuk warga, jenis pembayaran, dan periode yang sama.');
        }

        $jenisPembayaran = JenisPembayaran::findOrFail($validated['jenis_pembayaran_id']);

        if ($this->isAirJenis($jenisPembayaran)) {
            $airValidated = $request->validate([
                'periode' => ['nullable', 'string', 'max:20'],
                'meter_awal' => ['required', 'integer', 'min:0'],
                'meter_akhir' => ['required', 'integer', 'min:0', 'gte:meter_awal'],
                'denda' => ['nullable', 'integer', 'min:0'],
                'jatuh_tempo' => ['nullable', 'date'],
            ]);

            $meterAwal = (int) $airValidated['meter_awal'];
            $meterAkhir = (int) $airValidated['meter_akhir'];
            $pemakaianAir = max($meterAkhir - $meterAwal, 0);
            $tarifPerMeter = 1500;
            $biayaTetap = 5000;
            $dendaInput = (int) ($airValidated['denda'] ?? 0);

            $validated['periode'] = $airValidated['periode'] ?: now()->format('Y-m');
            $validated['meter_awal'] = $meterAwal;
            $validated['meter_akhir'] = $meterAkhir;
            $validated['pemakaian_air'] = $pemakaianAir;
            $validated['tarif_per_meter'] = $tarifPerMeter;
            $validated['biaya_tetap'] = $biayaTetap;
            $validated['jatuh_tempo'] = ! empty($airValidated['jatuh_tempo'])
                ? $airValidated['jatuh_tempo']
                : now()->addDays(10)->toDateString();
            $denda = $this->resolveDendaOtomatis($dendaInput, $validated['jatuh_tempo'], $validated['tanggal_bayar'] ?? null);
            $validated['denda'] = $denda;
            $validated['jumlah'] = ($pemakaianAir * $tarifPerMeter) + $biayaTetap + $denda;

            $keteranganTambahan = trim((string) $request->input('keterangan'));
            $keteranganAir = [
                'Tagihan HIPPAM periode ' . $validated['periode'],
                'Meter awal: ' . $meterAwal,
                'Meter akhir: ' . $meterAkhir,
                'Pemakaian: ' . $pemakaianAir . ' m3',
                'Tarif: Rp ' . number_format($tarifPerMeter, 0, ',', '.'),
                'Biaya tetap: Rp ' . number_format($biayaTetap, 0, ',', '.'),
                $denda > 0 ? 'Denda: Rp ' . number_format($denda, 0, ',', '.') : null,
                $keteranganTambahan !== '' ? $keteranganTambahan : null,
            ];

            $validated['keterangan'] = implode("\n", array_values(array_filter($keteranganAir)));
        } else {
            $manualValidated = $request->validate([
                'jumlah' => ['required', 'numeric', 'min:1'],
            ]);

            $validated['jumlah'] = (int) $manualValidated['jumlah'];
        }

        $pembayaran->update($validated);

        AdminActivity::log('pembayaran', 'update', 'Memperbarui data pembayaran.', [
            'pembayaran_id' => $pembayaran->id,
            'status' => $pembayaran->status,
            'jumlah' => $pembayaran->jumlah,
        ]);

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil diupdate');
    }

    /**
     * Hapus data pembayaran
     */
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        if ($pembayaran->status === 'paid') {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Tagihan yang sudah lunas tidak boleh dihapus.');
        }

        AdminActivity::log('pembayaran', 'delete', 'Menghapus data pembayaran.', [
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $pembayaran->warga_id,
        ]);

        $pembayaran->delete();

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran berhasil dihapus');
    }

    public function invoice($id)
    {
        $pembayaran = Pembayaran::with('warga','jenisPembayaran', 'paidByUser')->findOrFail($id);

        return view('pembayaran.invoice', compact('pembayaran'));
    }

    public function cashForm($id)
    {
        $pembayaran = Pembayaran::with(['warga', 'jenisPembayaran'])->findOrFail($id);

        if ($pembayaran->status === 'paid') {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Tagihan ini sudah lunas.');
        }

        if ((int) $pembayaran->jumlah <= 0) {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Tagihan ini masih draft. Isi meter dan nominal terlebih dahulu.');
        }

        return view('pembayaran.cash', compact('pembayaran'));
    }

    public function payCash(Request $request, $id)
    {
        $pembayaran = Pembayaran::with(['warga', 'jenisPembayaran'])->findOrFail($id);

        if ($pembayaran->status === 'paid') {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Tagihan ini sudah lunas.');
        }

        if ((int) $pembayaran->jumlah <= 0) {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Tagihan ini masih draft. Isi meter dan nominal terlebih dahulu.');
        }

        $validated = $request->validate([
            'cash_received_amount' => ['required', 'integer', 'min:0'],
            'catatan_tunai' => ['nullable', 'string', 'max:500'],
        ]);

        $totalTagihan = (int) $pembayaran->jumlah;
        $uangDiterima = (int) $validated['cash_received_amount'];

        if ($uangDiterima < $totalTagihan) {
            return redirect()->back()->with('error', 'Uang tunai kurang dari total tagihan.');
        }

        $kembalian = $uangDiterima - $totalTagihan;

        $catatanTunai = trim((string) ($validated['catatan_tunai'] ?? ''));
        $auditTunai = 'Pembayaran tunai | Diterima: Rp ' . number_format($uangDiterima, 0, ',', '.')
            . ' | Kembalian: Rp ' . number_format($kembalian, 0, ',', '.');

        $pembayaran->update([
            'status' => 'paid',
            'tanggal_bayar' => now()->toDateString(),
            'payment_method' => 'cash',
            'cash_received_amount' => $uangDiterima,
            'cash_change_amount' => $kembalian,
            'paid_by_user_id' => Auth::id(),
            'keterangan' => trim($pembayaran->keterangan . "\n" . $auditTunai . ($catatanTunai !== '' ? "\nCatatan: " . $catatanTunai : '')),
        ]);

        AdminActivity::log('pembayaran', 'pay_cash', 'Memproses pembayaran tunai.', [
            'pembayaran_id' => $pembayaran->id,
            'cash_received_amount' => $uangDiterima,
            'cash_change_amount' => $kembalian,
        ]);

        return redirect()->route('pembayaran.invoice', $pembayaran->id)
            ->with('success', 'Pembayaran tunai berhasil diproses. Kembalian: Rp ' . number_format($kembalian, 0, ',', '.'));
    }

    public function sendWhatsappReminder($id, WhatsAppService $whatsAppService)
    {
        $pembayaran = Pembayaran::with(['warga', 'jenisPembayaran'])->findOrFail($id);


        if (! $whatsAppService->enabled()) {
            return redirect()->back()->with('error', 'WhatsApp belum diaktifkan di konfigurasi.');
        }

        if ($pembayaran->status === 'paid') {
            return redirect()->back()->with('error', 'Tagihan ini sudah lunas.');
        }

        if ((int) $pembayaran->jumlah <= 0) {
            return redirect()->back()->with('error', 'Tagihan masih draft. Isi meter dan nominal terlebih dahulu.');
        }

        $noHp = (string) optional($pembayaran->warga)->no_hp;
        // Validasi nomor HP dinonaktifkan sementara
        // if (trim($noHp) === '') {
        //     return redirect()->back()->with('error', 'Nomor HP warga belum diisi.');
        // }

        $nama = (string) optional($pembayaran->warga)->nama;
        $periode = (string) ($pembayaran->periode ?? now()->format('Y-m'));
        $jatuhTempo = $pembayaran->jatuh_tempo
            ? Carbon::parse($pembayaran->jatuh_tempo)->translatedFormat('d M Y')
            : '-';

        $message = implode("\n", [
            'Halo ' . $nama . ',',
            'Ini pengingat tagihan Air periode ' . $periode . '.',
            'Total tagihan: Rp ' . number_format((int) $pembayaran->jumlah, 0, ',', '.'),
            'Jatuh tempo: ' . $jatuhTempo,
            'Invoice: ' . ($pembayaran->invoice ?? '-'),
            'Silakan login ke portal untuk melakukan pembayaran.',
        ]);

        if (! $whatsAppService->send($noHp, $message)) {
            WhatsAppReminderLog::create([
                'pembayaran_id' => $pembayaran->id,
                'warga_id' => $pembayaran->warga_id,
                'recipient' => $noHp,
                'status' => 'failed',
                'message' => $message,
                'error_message' => 'Provider mengembalikan gagal kirim.',
            ]);

            return redirect()->back()->with('error', 'Gagal mengirim reminder WhatsApp.');
        }

        $pembayaran->last_whatsapp_reminder_at = now()->toDateString();
        $pembayaran->save();

        WhatsAppReminderLog::create([
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $pembayaran->warga_id,
            'recipient' => $noHp,
            'status' => 'sent',
            'message' => $message,
            'sent_at' => now(),
        ]);

        AdminActivity::log('reminder', 'send_whatsapp_single', 'Mengirim reminder WhatsApp per tagihan.', [
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $pembayaran->warga_id,
        ]);

        return redirect()->back()->with('success', 'Reminder WhatsApp berhasil dikirim.');
    }

    public function sendWhatsappReminderPerWarga($wargaId, WhatsAppService $whatsAppService)
    {
        $warga = Warga::findOrFail($wargaId);


        if (! $whatsAppService->enabled()) {
            return redirect()->back()->with('error', 'WhatsApp belum diaktifkan di konfigurasi.');
        }

        $noHp = (string) $warga->no_hp;
        // Validasi nomor HP dinonaktifkan sementara
        // if (trim($noHp) === '') {
        //     return redirect()->back()->with('error', 'Nomor HP warga belum diisi.');
        // }

        $tagihans = Pembayaran::with('jenisPembayaran')
            ->where('warga_id', $warga->id)
            ->where('status', 'pending')
            ->where('jumlah', '>', 0)
            ->whereNotNull('jatuh_tempo')
            ->whereDate('jatuh_tempo', '<=', now())
            ->whereHas('jenisPembayaran', function ($query) {
                $query->whereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->orderBy('jatuh_tempo')
            ->get();

        if ($tagihans->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada tagihan overdue yang bisa dikirim untuk warga ini.');
        }

        $alreadySentToday = $tagihans->every(function (Pembayaran $item) {
            if (! $item->last_whatsapp_reminder_at) {
                return false;
            }

            return Carbon::parse($item->last_whatsapp_reminder_at)->isToday();
        });

        if ($alreadySentToday) {
            WhatsAppReminderLog::create([
                'warga_id' => $warga->id,
                'recipient' => $noHp,
                'status' => 'skipped',
                'message' => 'Reminder dilewati karena sudah terkirim hari ini.',
                'error_message' => 'Rate limit harian.',
            ]);

            return redirect()->back()->with('error', 'Reminder untuk warga ini sudah dikirim hari ini. Coba lagi besok.');
        }

        $totalTagihan = (int) $tagihans->sum('jumlah');
        $totalDenda = (int) $tagihans->sum('denda');
        $countTagihan = $tagihans->count();

        $detailLines = $tagihans->take(3)->map(function (Pembayaran $item) {
            $periode = (string) ($item->periode ?? '-');
            $jatuhTempo = $item->jatuh_tempo
                ? Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y')
                : '-';

            return '- ' . $periode . ' | Rp ' . number_format((int) $item->jumlah, 0, ',', '.') . ' | Tempo ' . $jatuhTempo;
        })->values()->all();

        if ($countTagihan > 3) {
            $detailLines[] = '- ... dan ' . ($countTagihan - 3) . ' tagihan lainnya';
        }

        $messageParts = [
            'Halo ' . $warga->nama . ',',
            'Anda memiliki ' . $countTagihan . ' tagihan Air yang perlu segera dibayar.',
            'Total tunggakan: Rp ' . number_format($totalTagihan, 0, ',', '.'),
            'Total denda: Rp ' . number_format($totalDenda, 0, ',', '.'),
            'Ringkasan:',
            ...$detailLines,
            'Silakan login ke portal untuk melunasi tagihan.',
        ];

        $message = implode("\n", $messageParts);

        if (! $whatsAppService->send($noHp, $message)) {
            WhatsAppReminderLog::create([
                'warga_id' => $warga->id,
                'recipient' => $noHp,
                'status' => 'failed',
                'message' => $message,
                'error_message' => 'Provider mengembalikan gagal kirim.',
            ]);

            return redirect()->back()->with('error', 'Gagal mengirim reminder WhatsApp.');
        }

        Pembayaran::query()
            ->whereIn('id', $tagihans->pluck('id')->all())
            ->update(['last_whatsapp_reminder_at' => now()->toDateString()]);

        WhatsAppReminderLog::create([
            'warga_id' => $warga->id,
            'recipient' => $noHp,
            'status' => 'sent',
            'message' => $message,
            'sent_at' => now(),
        ]);

        AdminActivity::log('reminder', 'send_whatsapp_bulk', 'Mengirim reminder WhatsApp gabungan per warga.', [
            'warga_id' => $warga->id,
            'tagihan_count' => $countTagihan,
        ]);

        return redirect()->back()->with('success', 'Reminder WhatsApp gabungan berhasil dikirim.');
    }

    public function sendWhatsappReminderAll(WhatsAppService $whatsAppService)
    {
        if (! $whatsAppService->enabled()) {
            return redirect()->back()->with('error', 'WhatsApp belum diaktifkan di konfigurasi. Pastikan WHATSAPP_ENABLED=true dan WHATSAPP_TOKEN sudah diset di Vercel.');
        }

        // Ambil semua warga yang punya tagihan pending (belum bayar) dengan jumlah > 0
        $wargaIds = Pembayaran::query()
            ->where('status', 'pending')
            ->where('jumlah', '>', 0)
            ->groupBy('warga_id')
            ->pluck('warga_id')
            ->all();

        if (empty($wargaIds)) {
            return redirect()->back()->with('error', 'Tidak ada warga dengan tagihan yang belum lunas.');
        }

        $sent    = 0;
        $skipped = 0;
        $failed  = 0;
        $failReasons = [];

        foreach ($wargaIds as $wargaId) {
            $tagihans = $allTagihans->get($wargaId);

            if (! $tagihans || $tagihans->isEmpty()) {
                continue;
            }

            $first  = $tagihans->first();
            $warga  = $first->warga;
            $noHp   = (string) ($warga?->no_hp ?? '');

            // Skip jika nomor HP kosong
            if (trim($noHp) === '') {
                $skipped++;
                WhatsAppReminderLog::create([
                    'warga_id'      => $wargaId,
                    'recipient'     => '',
                    'status'        => 'skipped',
                    'message'       => 'Nomor HP tidak ada.',
                    'error_message' => 'No phone number for warga ID ' . $wargaId,
                ]);
                continue;
            }

            // Skip jika sudah dikirim hari ini (kecuali ada parameter force)
            $alreadySentToday = $tagihans->every(function (Pembayaran $item) {
                return $item->last_whatsapp_reminder_at &&
                    Carbon::parse($item->last_whatsapp_reminder_at)->isToday();
            });

            if ($alreadySentToday && !request()->has('force')) {
                $skipped++;
                WhatsAppReminderLog::create([
                    'warga_id'      => $wargaId,
                    'recipient'     => $noHp,
                    'status'        => 'skipped',
                    'message'       => 'Reminder dilewati karena sudah terkirim hari ini.',
                    'error_message' => 'Rate limit harian.',
                ]);
                continue;
            }

            // Susun pesan
            $nama          = (string) ($warga?->nama ?? 'Warga');
            $countTagihan  = $tagihans->count();
            $totalTagihan  = (int) $tagihans->sum('jumlah');
            $totalDenda    = (int) $tagihans->sum('denda');

            $detailLines = $tagihans->take(3)->map(function (Pembayaran $item) {
                $periode    = (string) ($item->periode ?? '-');
                $jatuhTempo = $item->jatuh_tempo
                    ? Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y')
                    : '-';
                return '• ' . (optional($item->jenisPembayaran)->nama ?: '-') . ' (' . $periode . ') — Rp ' . number_format((int) $item->jumlah, 0, ',', '.');
            })->values()->all();

            $bodyLines   = [];
            $bodyLines[] = '🔔 PENGINGAT TAGIHAN — Desa';
            $bodyLines[] = 'Halo ' . $nama . ',';
            $bodyLines[] = 'Anda memiliki ' . $countTagihan . ' tagihan yang belum lunas.';
            $bodyLines[] = '';
            $bodyLines[] = '— Rincian —';
            foreach ($detailLines as $l) {
                $bodyLines[] = $l;
            }
            $bodyLines[] = '';
            $bodyLines[] = 'Total: Rp ' . number_format($totalTagihan, 0, ',', '.');
            if ($totalDenda > 0) {
                $bodyLines[] = 'Denda: Rp ' . number_format($totalDenda, 0, ',', '.');
            }
            $bodyLines[] = '';
            $bodyLines[] = 'Silakan login ke portal desa untuk melakukan pembayaran.';
            $bodyLines[] = 'Terima kasih.';

            $message = implode("\n", $bodyLines);

            // Kirim LANGSUNG (tidak melalui Job agar tidak tertunda di Vercel)
            $isSent  = $whatsAppService->send($noHp, $message);
            $lastRes = $whatsAppService->getLastResponse();

            if ($isSent) {
                Pembayaran::whereIn('id', $tagihans->pluck('id')->all())
                    ->update(['last_whatsapp_reminder_at' => now()->toDateString()]);

                WhatsAppReminderLog::create([
                    'pembayaran_id' => $first->id,
                    'warga_id'      => $wargaId,
                    'recipient'     => $noHp,
                    'status'        => 'sent',
                    'message'       => $message,
                    'sent_at'       => now(),
                ]);
                $sent++;
            } else {
                $errDetail = $lastRes ? json_encode($lastRes) : 'Provider failed';
                WhatsAppReminderLog::create([
                    'pembayaran_id' => $first->id,
                    'warga_id'      => $wargaId,
                    'recipient'     => $noHp,
                    'status'        => 'failed',
                    'message'       => $message,
                    'error_message' => $errDetail,
                ]);
                $failed++;
                $failReasons[] = $nama . ' (' . $noHp . '): ' . $errDetail;
            }
        }

        AdminActivity::log('reminder', 'send_whatsapp_mass', 'Kirim reminder WhatsApp massal.', [
            'sent'    => $sent,
            'skipped' => $skipped,
            'failed'  => $failed,
        ]);

        if ($failed > 0 && $sent === 0) {
            $detail = implode(' | ', array_slice($failReasons, 0, 2));
            return redirect()->back()->with('error', "Gagal mengirim ke {$failed} warga. Detail: {$detail}");
        }

        return redirect()->back()->with('success', "✅ Terkirim ke {$sent} warga, dilewati {$skipped}, gagal {$failed}.");
    }

    private function isAirJenis(JenisPembayaran $jenisPembayaran): bool
    {
        return str_contains(strtolower($jenisPembayaran->nama), 'air');
    }

    private function resolveDendaOtomatis(int $dendaInput, ?string $jatuhTempo, ?string $tanggalBayar = null): int
    {
    // Jika admin isi manual > 0, pakai nilai manual tersebut.
    if ($dendaInput > 0) {
        return $dendaInput;
    }

    if (! $jatuhTempo) {
        return 0;
    }

    // Jika tanggal bayar diisi dan lebih dari jatuh tempo, denda otomatis 2500
    if ($tanggalBayar && \Illuminate\Support\Carbon::parse($tanggalBayar)->isAfter(\Illuminate\Support\Carbon::parse($jatuhTempo))) {
        return 2500;
    }

    // Jika hari ini sudah lewat jatuh tempo, denda otomatis 2500
    return \Illuminate\Support\Carbon::parse($jatuhTempo)->isBefore(now()->startOfDay()) ? 2500 : 0;
    }

    private function generateTagihanOtomatis(string $periode): int
    {
        $wargas = Warga::query()->orderBy('nama')->get();

        if ($wargas->isEmpty()) {
            return 0;
        }

        $jenisPembayarans = JenisPembayaran::query()
            ->where(function ($query) {
                $query->where('nominal', '>', 0)
                    ->orWhereRaw('LOWER(nama) like ?', ['%air%']);
            })
            ->orderBy('nama')
            ->get();

        if ($jenisPembayarans->isEmpty()) {
            return 0;
        }

        $generated = 0;
        $tanggalTagihan = now()->toDateString();
        $jatuhTempoDefault = now()->addDays(10)->toDateString();

        foreach ($wargas as $warga) {
            foreach ($jenisPembayarans as $jenisPembayaran) {
                $exists = Pembayaran::query()
                    ->where('warga_id', $warga->id)
                    ->where('jenis_pembayaran_id', $jenisPembayaran->id)
                    ->where('periode', $periode)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $isAir = $this->isAirJenis($jenisPembayaran);

                $payload = [
                    'warga_id' => $warga->id,
                    'jenis_pembayaran_id' => $jenisPembayaran->id,
                    'tanggal_bayar' => $tanggalTagihan,
                    'periode' => $periode,
                    'status' => 'pending',
                    'invoice' => 'INV-AUTO-' . now()->format('YmdHis') . '-' . $warga->id . '-' . $jenisPembayaran->id . '-' . random_int(100, 999),
                ];

                if ($isAir) {
                    $previousAir = Pembayaran::query()
                        ->where('warga_id', $warga->id)
                        ->where('jenis_pembayaran_id', $jenisPembayaran->id)
                        ->whereNotNull('meter_akhir')
                        ->orderByDesc('id')
                        ->first();

                    $meterAwal = (int) ($previousAir->meter_akhir ?? 0);
                    $meterAkhir = $meterAwal;

                    $payload = array_merge($payload, [
                        'meter_awal' => $meterAwal,
                        'meter_akhir' => $meterAkhir,
                        'pemakaian_air' => 0,
                        'tarif_per_meter' => 1500,
                        'biaya_tetap' => 5000,
                        'denda' => 0,
                        'jatuh_tempo' => $jatuhTempoDefault,
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
                $generated++;
            }
        }

        return $generated;
    }
}