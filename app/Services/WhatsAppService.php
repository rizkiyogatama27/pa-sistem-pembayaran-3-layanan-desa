<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected ?array $lastResponse = null;
    public function enabled(): bool
    {
        return (bool) config('services.whatsapp.enabled') && filled(config('services.whatsapp.token'));
    }

    public function send(string $recipient, string $message): bool
    {
        if (! $this->enabled() || trim($recipient) === '' || trim($message) === '') {
            return false;
        }

        $provider = (string) config('services.whatsapp.provider', 'fonnte');

        // Normalize the recipient to international format once
        $target = $this->normalizeNumber($recipient);

        // Try Twilio if configured or explicitly selected
        $twilioSid = (string) config('services.twilio.account_sid');
        $twilioToken = (string) config('services.twilio.auth_token');
        $twilioFrom = (string) config('services.twilio.whatsapp_from');

        if ($provider === 'twilio' || ($twilioSid !== '' && $twilioToken !== '' && $twilioFrom !== '')) {
            try {
                $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $twilioSid . '/Messages.json';

                // Ensure the From value has the required 'whatsapp:' prefix for Twilio
                $from = $twilioFrom;
                if ($from !== '' && ! str_starts_with($from, 'whatsapp:')) {
                    $from = 'whatsapp:' . $from;
                }

                $response = Http::withBasicAuth($twilioSid, $twilioToken)
                    ->asForm()
                    ->post($url, [
                        'From' => $from,
                        'To' => 'whatsapp:+' . $target,
                        'Body' => $message,
                    ]);

                $this->lastResponse = [
                    'http_status' => $response->status(),
                    'body' => null,
                ];

                try {
                    $this->lastResponse['body'] = $response->json();
                } catch (\Exception $e) {
                    $this->lastResponse['body'] = $response->body();
                }

                if ($response->successful()) {
                    return true;
                }
            } catch (\Exception $e) {
                $this->lastResponse = ['http_status' => 0, 'body' => $e->getMessage()];
            }
        }

        if ($provider === 'facebook_cloud' || $provider === 'whatsapp_cloud') {
            // Use WhatsApp Cloud API (Meta)
            $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
            $endpoint = rtrim((string) config('services.whatsapp.endpoint', 'https://graph.facebook.com/v17.0'), '/');

            if ($phoneNumberId === '') {
                // misconfiguration
                return false;
            }

            $url = $endpoint . '/' . $phoneNumberId . '/messages';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . (string) config('services.whatsapp.token'),
                'Content-Type' => 'application/json',
            ])->post($url, [
                'messaging_product' => 'whatsapp',
                'to' => $target,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

            $this->lastResponse = [
                'http_status' => $response->status(),
                'body' => null,
            ];

            try {
                $this->lastResponse['body'] = $response->json();
            } catch (\Exception $e) {
                $this->lastResponse['body'] = $response->body();
            }

            if (! $response->successful()) {
                return false;
            }

            $body = $this->lastResponse['body'];
            if (is_array($body) && isset($body['messages'])) {
                return true;
            }

            return true;
        }

        // Fallback / legacy: try device-based providers (like fonnte)
        $targets = array_values(array_unique(array_filter([
            $target,
            $this->stripCountryCode($recipient),
        ], fn ($value) => trim((string) $value) !== '')));

        foreach ($targets as $t) {
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => (string) config('services.whatsapp.token'),
                ])
                ->post((string) config('services.whatsapp.endpoint'), [
                    'target' => $t,
                    'message' => $message,
                    'countryCode' => (string) config('services.whatsapp.country_code', '62'),
                ]);

            $this->lastResponse = [
                'http_status' => $response->status(),
                'body' => null,
            ];

            try {
                $body = $response->json();
                $this->lastResponse['body'] = $body;
            } catch (\Exception $e) {
                $this->lastResponse['body'] = $response->body();
            }

            if (! $response->successful()) {
                // keep lastResponse and try next format
                continue;
            }

            if (! is_array($this->lastResponse['body']) || $this->lastResponse['body'] === []) {
                return true;
            }

            $status = $this->lastResponse['body']['status'] ?? null;
            $success = $this->lastResponse['body']['success'] ?? null;
            $result = $this->lastResponse['body']['result'] ?? null;

            if ($status === false || $status === 'false' || $success === false || $success === 'false' || $result === false || $result === 'false') {
                // provider explicitly failed
                continue;
            }

            if ($status === true || $status === 'true' || $success === true || $success === 'true' || $result === true || $result === 'true') {
                return true;
            }

            return true;
        }

        // If legacy/device-based provider failed for all formats, try WhatsApp Cloud API
        $cloudPhoneId = (string) config('services.whatsapp.phone_number_id');
        $cloudToken = (string) config('services.whatsapp.token');

        if ($cloudPhoneId !== '' && $cloudToken !== '') {
            // attempt cloud send as a fallback
            $endpoint = rtrim((string) config('services.whatsapp.endpoint', 'https://graph.facebook.com/v17.0'), '/');
            $url = $endpoint . '/' . $cloudPhoneId . '/messages';

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $cloudToken,
                    'Content-Type' => 'application/json',
                ])->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $target,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

                $this->lastResponse = [
                    'http_status' => $response->status(),
                    'body' => null,
                ];

                try {
                    $this->lastResponse['body'] = $response->json();
                } catch (\Exception $e) {
                    $this->lastResponse['body'] = $response->body();
                }

                if ($response->successful()) {
                    return true;
                }
            } catch (\Exception $e) {
                // ignore and fallthrough to false
                $this->lastResponse = ['http_status' => 0, 'body' => $e->getMessage()];
            }
        }

        return false;
    }

    public function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?: '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        return '62' . ltrim($digits, '0');
    }

    public function stripCountryCode(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?: '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }

    /**
     * Return the last provider response (http_status + body) or null if none.
     *
     * @return array|null
     */
    public function getLastResponse(): ?array
    {
        return $this->lastResponse;
    }
}