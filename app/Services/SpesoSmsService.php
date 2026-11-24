<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** SMS gateway client for the Speso business API (same call as the slce_portal project). */
class SpesoSmsService
{
    private string $apiKey;

    private string $senderId;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.speso_sms.api_key');
        $this->senderId = (string) config('services.speso_sms.sender_id');
        $this->baseUrl = rtrim((string) (config('services.speso_sms.base_url') ?: 'https://business.speso.co/api/v1'), '/');
    }

    /** Normalise a Ghanaian number to the local 0-prefixed form. */
    public function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($phone, '233')) {
            $phone = '0'.substr($phone, 3);
        }

        return $phone;
    }

    /** @return array{success: bool, code: string, message: string} */
    public function sendSms(string $phone, string $message): array
    {
        $phone = $this->normalizePhone($phone);

        if ($this->apiKey === '') {
            return ['success' => false, 'code' => 'config', 'message' => 'Speso API key is not configured. Set SPESO_API_KEY in .env.'];
        }

        try {
            $response = Http::withToken($this->apiKey)->acceptJson()->timeout(15)
                ->post($this->baseUrl.'/messages/send', [
                    'sender_id' => $this->senderId,
                    'phone' => $phone,
                    'message' => $message,
                ]);

            $body = $response->json() ?? [];
            $success = $response->successful() && ($body['success'] ?? true) !== false;

            if (! $success) {
                Log::error('Speso SMS failed', ['status' => $response->status(), 'body' => $response->body()]);
            }

            return [
                'success' => $success,
                'code' => (string) $response->status(),
                'message' => (string) ($body['message'] ?? $body['error'] ?? ($success ? 'Message submitted successfully' : 'SMS sending failed')),
            ];
        } catch (\Throwable $e) {
            Log::error('Speso SMS exception', ['error' => $e->getMessage()]);

            return ['success' => false, 'code' => 'error', 'message' => $e->getMessage()];
        }
    }
}
