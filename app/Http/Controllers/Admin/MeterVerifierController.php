<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MeterReading;
use App\Models\Pembayaran;
use App\Models\AdminActivityLog;
use App\Jobs\ScheduleMeterAuditJob;
use App\Services\AutoGenerateTagihanService;
use Illuminate\Support\Facades\Auth;

class MeterVerifierController extends Controller
{
    public function index()
    {
        $readings = MeterReading::whereIn('status', ['pending_verification', 'pending'])->orderByDesc('created_at')->with('warga', 'pembayaran')->paginate(25);
        return view('admin.meter_verifier.index', compact('readings'));
    }

    public function approve(Request $request, $id)
    {
        $reading = MeterReading::findOrFail($id);
        $pembayaran = Pembayaran::find($reading->pembayaran_id);

        if ($request->filled('koreksi_meter_awal')) {
            $reading->meter_awal = $request->input('koreksi_meter_awal');
        }
        if ($request->filled('koreksi_meter_akhir')) {
            $reading->meter_akhir = $request->input('koreksi_meter_akhir');
        } elseif ($request->filled('koreksi_meter')) {
            $reading->meter_akhir = $request->input('koreksi_meter');
        }

        $reading->status = 'verified';
        $reading->verified_by = Auth::id();
        $reading->save();

        if ($pembayaran) {
            $service = app(AutoGenerateTagihanService::class);
            $calc = $service->calculateWaterBill((int)$reading->meter_awal, (int)$reading->meter_akhir, (int)$pembayaran->tarif_per_meter, (int)$pembayaran->biaya_tetap, 0);
            $pembayaran->meter_awal = $reading->meter_awal;
            $pembayaran->meter_akhir = $reading->meter_akhir;
            $pembayaran->pemakaian_air = $calc['usage'];
            $pembayaran->jumlah = $calc['amount'];
            $pembayaran->keterangan = ($pembayaran->keterangan ?? '') . ' | Verified by admin ' . Auth::id();
            $pembayaran->save();
        }

        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'meter_reading',
            'action' => 'approve',
            'description' => 'Approved MeterReading #' . $reading->id,
            'metadata' => ['reading_id' => $reading->id],
        ]);

        return redirect()->back()->with('status', 'Reading approved');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        $reading = MeterReading::findOrFail($id);
        $reading->status = 'rejected';
        $reading->rejection_reason = $request->input('reason');
        $reading->verified_by = Auth::id();
        $reading->save();

        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'meter_reading',
            'action' => 'reject',
            'description' => 'Rejected MeterReading #' . $reading->id . ': ' . $reading->rejection_reason,
            'metadata' => ['reading_id' => $reading->id, 'reason' => $reading->rejection_reason],
        ]);

        return redirect()->back()->with('status', 'Reading rejected');
    }

    public function scheduleAudit(Request $request, $id)
    {
        $reading = MeterReading::findOrFail($id);
        ScheduleMeterAuditJob::dispatch($reading->id);
        AdminActivityLog::create([
            'user_id' => Auth::id(),
            'module' => 'meter_reading',
            'action' => 'schedule_audit',
            'description' => 'Scheduled audit for MeterReading #' . $reading->id,
            'metadata' => ['reading_id' => $reading->id],
        ]);

        return redirect()->back()->with('status', 'Audit scheduled');
    }
}
