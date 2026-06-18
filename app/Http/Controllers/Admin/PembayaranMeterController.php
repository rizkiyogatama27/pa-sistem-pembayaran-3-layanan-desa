<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use App\Models\Pembayaran;
use App\Services\AutoGenerateTagihanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembayaranMeterController extends Controller
{
    public function store(Request $request, $id, AutoGenerateTagihanService $service)
    {
        $user = Auth::user();
        if (! in_array($user->role, ['admin', 'petugas'])) {
            abort(403);
        }

        $pembayaran = Pembayaran::findOrFail($id);

        $jenis = strtolower((string) optional($pembayaran->jenisPembayaran)->nama);
        if (! str_contains($jenis, 'air')) {
            return back()->with('error', 'Hanya tagihan layanan air yang dapat diupdate meternya.');
        }

        $validated = $request->validate([
            'meter_akhir' => 'required|integer|min:0',
            'reading_at' => 'nullable|date',
            'photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        // determine previous meter_awal
        $previous = Pembayaran::query()
            ->where('warga_id', $pembayaran->warga_id)
            ->whereNotNull('meter_akhir')
            ->where('jenis_pembayaran_id', $pembayaran->jenis_pembayaran_id)
            ->orderByDesc('id')
            ->first();

        $meterAwal = $previous->meter_akhir ?? (int) ($pembayaran->meter_awal ?? 0);
        $meterAkhir = (int) $validated['meter_akhir'];

        // store photo if provided
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('meter_photos', 'public');
        }

        // create reading record
        $reading = MeterReading::create([
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $pembayaran->warga_id,
            'meter_awal' => $meterAwal,
            'meter_akhir' => $meterAkhir,
            'meter_photo' => $photoPath,
            'reading_at' => $validated['reading_at'] ?? now(),
            'reading_source' => 'petugas',
            'verified_by' => $user->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        // calculate bill
        $tarif = (int) ($pembayaran->tarif_per_meter ?? 1500);
        $fixed = (int) ($pembayaran->biaya_tetap ?? 5000);
        $minimum = 0;

        $calc = $service->calculateWaterBill($meterAwal, $meterAkhir, $tarif, $fixed, $minimum);

        // update pembayaran
        $pembayaran->meter_awal = $meterAwal;
        $pembayaran->meter_akhir = $meterAkhir;
        $pembayaran->jumlah = $calc['amount'];
        $pembayaran->keterangan = 'Tagihan air periode ' . ($pembayaran->periode ?? now()->format('Y-m')) . '. Pemakaian: ' . $calc['usage'];
        $pembayaran->status = 'pending';
        $pembayaran->save();

        return back()->with('success', 'Meter reading disimpan dan tagihan diperbarui.');
    }
}
