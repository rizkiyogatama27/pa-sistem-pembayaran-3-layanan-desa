<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PaymentGuideController extends Controller
{
    /**
     * Return JSON with the URL of the payment guide image.
     * If the image does not exist, the response contains null and a friendly message.
     */
    public function show()
    {
        try {
            $relativePath = 'images/tata-cara-pembayaran.png';
            // Check if the file exists in the public disk
            if (Storage::disk('public')->exists($relativePath)) {
                $url = asset($relativePath);
                return response()->json(['image_url' => $url]);
            }
            // File missing – return null so the front‑end can show fallback text
            return response()->json(['image_url' => null, 'message' => 'Gambar tidak ditemukan.']);
        } catch (\Throwable $e) {
            Log::error('PaymentGuideController error: ' . $e->getMessage());
            return response()->json(['image_url' => null, 'error' => 'Terjadi kesalahan server.'], 500);
        }
    }
}
