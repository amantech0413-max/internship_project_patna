<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    /**
     * Send a text message via official WhatsApp Cloud API (Meta).
     * Does NOT create groups or add members — message sending only.
     */
    public function sendTextMessage(string $mobile, string $body): array
    {
        if (! config('whatsapp.enabled')) {
            Log::warning('WhatsApp Cloud API disabled. Message not sent.', ['mobile' => $mobile]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => 'WhatsApp API is disabled. Set WHATSAPP_ENABLED=true in .env',
            ];
        }

        $phoneNumberId = config('whatsapp.phone_number_id');
        $token = config('whatsapp.access_token');

        if (! $phoneNumberId || ! $token) {
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'WhatsApp API credentials missing.',
            ];
        }

        $to = $this->formatPhoneNumber($mobile);
        $url = sprintf(
            '%s/%s/%s/messages',
            rtrim(config('whatsapp.base_url'), '/'),
            config('whatsapp.api_version'),
            $phoneNumberId
        );

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($url, [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $body,
                ],
            ]);

        if ($response->successful()) {
            $messageId = $response->json('messages.0.id');

            Log::info('WhatsApp message sent', ['mobile' => $to, 'message_id' => $messageId]);

            return [
                'success' => true,
                'message_id' => $messageId,
                'error' => null,
            ];
        }

        $error = $response->json('error.message') ?? $response->body();

        Log::error('WhatsApp API error', ['mobile' => $to, 'error' => $error]);

        return [
            'success' => false,
            'message_id' => null,
            'error' => is_string($error) ? $error : json_encode($error),
        ];
    }

    public function formatPhoneNumber(string $mobile): string
    {
        $digits = preg_replace('/\D/', '', $mobile);
        $country = config('whatsapp.default_country_code', '91');

        if (str_starts_with($digits, $country)) {
            return $digits;
        }

        if (strlen($digits) === 10) {
            return $country . $digits;
        }

        return $digits;
    }
}
