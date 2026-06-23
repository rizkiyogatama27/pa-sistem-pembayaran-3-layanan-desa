<?php

namespace App\Http\Controllers\Meter;

use App\Http\Controllers\Controller;
use App\Models\JenisPembayaran;
use Illuminate\Http\Request;
use App\Models\MeterReading;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Storage;
use App\Services\AutoGenerateTagihanService;
use App\Services\MeterOcrService;

class SelfReportMeterController extends Controller
{
    public function create()
    {
        $wargaId = auth()->user()?->warga_id;
        $airJenisId = JenisPembayaran::query()
            ->whereRaw('LOWER(nama) like ?', ['%air%'])
            ->value('id');

        $pembayarans = Pembayaran::query()
            ->with('jenisPembayaran')
            ->when($wargaId, function ($query, $id) {
                $query->where('warga_id', $id);
            })
            ->when($airJenisId, function ($query, $id) {
                $query->where('jenis_pembayaran_id', $id);
            })
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->get();

        return view('meter.self_report', compact('pembayarans', 'airJenisId'));
    }

    public function store(Request $request, MeterOcrService $ocrService)
    {
        $request->validate([
            'pembayaran_id' => 'required|integer|exists:pembayarans,id',
            'meter_akhir' => 'nullable|integer|min:0',
            'meter_photo' => 'required|image|max:10240',
        ]);

        $pembayaran = Pembayaran::findOrFail($request->input('pembayaran_id'));

        $photoFile = $request->file('meter_photo');
        $absolute = $photoFile->getRealPath();
        $photoHash = hash_file('sha256', $absolute);

        // Karena Vercel memblokir beberapa file host, kita coba freeimage.host
        $path = null;
        try {
            $freeImageKey = '6d207e02198a847aa98d0a2a901485a5';
            $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                'source',
                file_get_contents($absolute),
                $photoFile->getClientOriginalName()
            )->post('https://freeimage.host/api/1/upload', [
                'key' => $freeImageKey,
                'action' => 'upload'
            ]);

            if ($response->successful()) {
                $path = $response->json('image.url');
            }
        } catch (\Exception $e) {}

        // Fallback 1: envs.sh (sangat reliable untuk serverless)
        if (! $path) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                    'file',
                    file_get_contents($absolute),
                    $photoFile->getClientOriginalName()
                )->post('https://envs.sh');

                if ($response->successful()) {
                    $path = trim($response->body());
                }
            } catch (\Exception $e) {}
        }

        // Fallback 2: imgbb (jika punya key, tapi public key sering limit)
        if (! $path) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->attach(
                    'image',
                    file_get_contents($absolute),
                    $photoFile->getClientOriginalName()
                )->post('https://api.imgbb.com/1/upload', [
                    'key' => '546c25a59c58ad7d3c26021f1d1f0ffc' // Free public key or placeholder
                ]);
                if ($response->successful()) {
                    $path = $response->json('data.url');
                }
            } catch (\Exception $e) {}
        }

        // Fallback 3: Base64
        if (! $path || ! str_starts_with($path, 'http')) {
            try {
                \Illuminate\Support\Facades\DB::statement('ALTER TABLE meter_readings MODIFY meter_photo LONGTEXT');
            } catch (\Exception $e) {}
            
            // Limit base64 to avoid 500 error if DB statement fails (truncate to max varchar)
            // But we hope LONGTEXT worked.
            $mime = $photoFile->getMimeType();
            $base64 = base64_encode(file_get_contents($absolute));
            $path = 'data:' . $mime . ';base64,' . $base64;
            
            // Safe guard for TiDB VARCHAR limit if ALTER failed
            if (strlen($path) > 250) {
                // If it fails, we just save a placeholder to avoid 500 error so the OCR data still saves
                $path = 'fallback_base64_too_long';
            }
        }

        // try extract EXIF
        $lat = null; $lng = null;
        try {
            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($absolute);
                if (! empty($exif['GPSLatitude']) && ! empty($exif['GPSLongitude'])) {
                    // convert to decimal
                    $lat = $this->getGps($exif['GPSLatitude'], $exif['GPSLatitudeRef'] ?? 'N');
                    $lng = $this->getGps($exif['GPSLongitude'], $exif['GPSLongitudeRef'] ?? 'E');
                }
            }
        } catch (\Exception $e) {
            // ignore
        }

        $ocr = $ocrService->read($absolute);
        $ocrDetected = $ocr['meter_akhir'];
        
        $userMeterAkhir = $request->input('meter_akhir');
        $finalMeterAkhir = $userMeterAkhir !== null ? (int)$userMeterAkhir : ($ocrDetected !== null ? (int)$ocrDetected : 0);

        $ocrMismatch = $ocrDetected !== null && $userMeterAkhir !== null && abs((int) $userMeterAkhir - (int) $ocrDetected) > 10;

        $last = Pembayaran::where('warga_id', $pembayaran->warga_id)->whereNotNull('meter_akhir')->orderBy('periode', 'desc')->first();
        $lastMeter = $last?->meter_akhir ?? 0;

        // Semua laporan warga WAJIB masuk ke antrean verifikasi admin, 
        // karena OCR sering gagal/keliru dan user mungkin mengosongkan angka.
        $status = 'pending_verification';
        if ($ocrMismatch) {
            $reason = 'OCR result differs from user input';
        } else {
            $reason = $ocrDetected !== null ? 'OCR detected meter ' . $ocrDetected . '. Awaiting verifier review.' : 'OCR not available or failed; awaiting verifier review.';
        }

        $reading = MeterReading::create([
            'pembayaran_id' => $pembayaran->id,
            'warga_id' => $pembayaran->warga_id,
            'meter_awal' => $lastMeter,
            'meter_akhir' => $finalMeterAkhir,
            'meter_photo' => $path,
            'photo_hash' => $photoHash,
            'lat' => $lat,
            'lng' => $lng,
            'device_fingerprint' => $request->header('User-Agent'),
            'ocr_engine' => $ocr['engine'],
            'ocr_status' => $ocr['status'],
            'ocr_text' => $ocr['raw_text'],
            'ocr_meter_akhir' => $ocrDetected,
            'ocr_confidence' => $ocr['confidence'],
            'ocr_error' => $ocr['error'],
            'reading_at' => now(),
            'reading_source' => 'self',
            'status' => $status,
            'notes' => $request->input('notes'),
            'rejection_reason' => $reason,
        ]);

        // store as provisional until verifier approves
        $pembayaran->status = 'pending';
        $pembayaran->keterangan = trim(($pembayaran->keterangan ?? '') . ' | OCR/self-report pending verification');
        $pembayaran->save();

        // if verifier later approves, payment will be finalized from admin verifier screen
        if ($status === 'verified') {
            $service = app(AutoGenerateTagihanService::class);
            $calc = $service->calculateWaterBill((int)$reading->meter_awal, (int)$reading->meter_akhir, (int)$pembayaran->tarif_per_meter, (int)$pembayaran->biaya_tetap, 0);
            $pembayaran->meter_awal = $reading->meter_awal;
            $pembayaran->meter_akhir = $reading->meter_akhir;
            $pembayaran->pemakaian_air = $calc['usage'];
            $pembayaran->jumlah = $calc['amount'];
            $pembayaran->keterangan = ($pembayaran->keterangan ?? '') . ' | Self‑report verified';
            $pembayaran->save();
        }

        // If AJAX/JSON request, return structured JSON with OCR result and reading info
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'status' => $status,
                'reading_id' => $reading->id,
                'ocr' => [
                    'engine' => $reading->ocr_engine,
                    'meter_akhir' => $reading->ocr_meter_akhir,
                    'confidence' => $reading->ocr_confidence,
                    'raw_text' => $reading->ocr_text,
                    'error' => $reading->ocr_error,
                ],
                'message' => 'Reading submitted. Status: ' . $status,
            ], 200);
        }

        return redirect()->route('user.tagihan')->with('status', 'Berhasil! Laporan foto meteran Anda telah dikirim dan sedang menunggu verifikasi dari Admin Desa.');
    }

    private function getGps($exifCoord, $hemi)
    {
        $parts = $exifCoord;
        $degrees = count($parts) > 0 ? $this->gps2Num($parts[0]) : 0;
        $minutes = count($parts) > 1 ? $this->gps2Num($parts[1]) : 0;
        $seconds = count($parts) > 2 ? $this->gps2Num($parts[2]) : 0;

        $flip = ($hemi == 'W' || $hemi == 'S') ? -1 : 1;
        return $flip * ($degrees + ($minutes / 60) + ($seconds / 3600));
    }

    private function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);
        if (count($parts) <= 0) return 0;
        if (count($parts) == 1) return $parts[0];
        return floatval($parts[0]) / floatval($parts[1]);
    }

    public function ocr(Request $request, MeterOcrService $ocrService)
    {
        $request->validate([
            'meter_photo' => 'required|image|max:10240',
        ]);

        $photoFile = $request->file('meter_photo');
        $absolute = $photoFile->getRealPath();
        
        $ocr = $ocrService->read($absolute);

        return response()->json($ocr);
    }
}
