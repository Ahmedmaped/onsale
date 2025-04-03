<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZapierService
{
    private $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.zapier.webhook_url');
        
        if (empty($this->webhookUrl)) {
            Log::error('Zapier webhook URL is not configured');
            throw new \RuntimeException('Zapier webhook URL is not configured');
        }
    }

    public function sendEvent($event, $data)
    {
        try {
            if (empty($this->webhookUrl)) {
                Log::error('Cannot send event: Webhook URL is empty');
                return false;
            }

            $response = Http::post($this->webhookUrl, [
                'event' => $event,
                'data' => $data
            ]);

            if (!$response->successful()) {
                Log::error('Failed to send event to Zapier', [
                    'event' => $event,
                    'data' => $data,
                    'error' => $response->body()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending event to Zapier', [
                'event' => $event,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 