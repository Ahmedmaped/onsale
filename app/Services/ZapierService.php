<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZapierService
{
    private $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = env('ZAPIER_WEBHOOK_URL');
    }

    public function sendEvent($event, $data)
    {
        try {
            $response = Http::post($this->webhookUrl, [
                'event' => $event,
                'data' => $data
            ]);

            if (!$response->successful()) {
                \Log::error('Failed to send event to Zapier', [
                    'event' => $event,
                    'data' => $data,
                    'error' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error sending event to Zapier', [
                'event' => $event,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 