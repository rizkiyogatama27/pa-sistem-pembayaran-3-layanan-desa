<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MeterOcrService
{
    public function read(string $absolutePath): array
    {
        if (! is_file($absolutePath)) {
            return $this->emptyResult('file_not_found', 'Image file not found');
        }

        $tesseract = $this->tryTesseract($absolutePath);
        if ($tesseract['status'] === 'ok') {
            return $tesseract;
        }

        $ocrSpace = $this->tryOcrSpace($absolutePath);
        if ($ocrSpace['status'] !== 'not_configured') {
            // Return ocrSpace even if failed, so we can see the real error (e.g. timeout, no digits)
            return $ocrSpace;
        }

        return $this->emptyResult('unavailable', 'No OCR engine available on this server');
    }

    private function tryTesseract(string $absolutePath): array
    {
        $binary = $this->findBinary(['tesseract', 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe', 'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe']);
        if (! $binary) {
            return $this->emptyResult('not_installed', 'Tesseract executable was not found');
        }

        $escapedBinary = escapeshellarg($binary);
        $escapedFile = escapeshellarg($absolutePath);
        $command = $escapedBinary . ' ' . $escapedFile . ' stdout --psm 6 -l eng';

        $output = [];
        $exitCode = 0;
        @exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0 || empty($output)) {
            return $this->emptyResult('failed', 'Tesseract returned no text');
        }

        $rawText = trim(implode("\n", $output));
        $meterValue = $this->extractMeterValue($rawText);

        if ($meterValue === null) {
            return $this->emptyResult('no_digits', 'Tesseract did not detect any meter digits', $rawText);
        }

        return [
            'status' => 'ok',
            'engine' => 'tesseract',
            'raw_text' => $rawText,
            'meter_akhir' => $meterValue,
            'confidence' => null,
            'error' => null,
        ];
    }

    private function tryOcrSpace(string $absolutePath): array
    {
        $apiKey = trim((string) env('OCR_SPACE_API_KEY', ''));
        if ($apiKey === '') {
            return $this->emptyResult('not_configured', 'OCR.space API key is not configured');
        }

        try {
            $response = Http::timeout(30)
                ->attach('file', file_get_contents($absolutePath), 'meter.jpg')
                ->post('https://api.ocr.space/parse/image', [
                    'apikey' => $apiKey,
                    'language' => 'eng',
                    'scale' => 'true',
                    'isTable' => 'false',
                ]);

            if (! $response->successful()) {
                return $this->emptyResult('failed', 'OCR.space request failed: ' . $response->status());
            }

            $data = $response->json();
            $parsed = $data['ParsedResults'][0] ?? null;
            $rawText = trim((string) ($parsed['ParsedText'] ?? ''));
            $meterValue = $this->extractMeterValue($rawText);
            $confidence = isset($parsed['TextOverlay']['Lines'][0]['Words'][0]['WordText']) ? null : null;

            if ($rawText === '' || $meterValue === null) {
                return $this->emptyResult('no_digits', 'OCR.space did not detect meter digits', $rawText);
            }

            return [
                'status' => 'ok',
                'engine' => 'ocr_space',
                'raw_text' => $rawText,
                'meter_akhir' => $meterValue,
                'confidence' => $confidence,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return $this->emptyResult('failed', $e->getMessage());
        }
    }

    private function extractMeterValue(string $text): ?int
    {
        // Prioritaskan angka 3 sampai 6 digit (biasanya angka meteran sesungguhnya)
        // Hindari 7-8 digit karena itu biasanya nomor seri meteran atau tanggal.
        if (preg_match('/\b(\d{3,6})\b/', $text, $matches)) {
            return (int) $matches[1];
        }

        $digits = preg_replace('/\D+/', '', $text);
        if ($digits !== '' && strlen($digits) >= 3) {
            // Jika lebih dari 6 digit, kemungkinan tergabung dengan teks lain/nomor seri.
            // Ambil maksimal 5 digit pertama sebagai tebakan terbaik.
            if (strlen($digits) > 6) {
                return (int) substr($digits, 0, 5);
            }
            return (int) $digits;
        }

        return null;
    }

    private function findBinary(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($candidate === 'tesseract') {
                $path = trim((string) shell_exec('where tesseract 2>NUL'));
                if ($path !== '') {
                    return strtok($path, "\r\n");
                }
                continue;
            }

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function emptyResult(string $status, string $error, string $rawText = ''): array
    {
        return [
            'status' => $status,
            'engine' => null,
            'raw_text' => $rawText,
            'meter_akhir' => null,
            'confidence' => null,
            'error' => $error,
        ];
    }
}
