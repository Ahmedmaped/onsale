<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZapierService
{
    private $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = env('ZAPIER_WEBHOOK_URL');
        
        if (empty($this->webhookUrl)) {
            Log::error('Zapier webhook URL is not configured in .env file');
            throw new \RuntimeException('Zapier webhook URL is not configured in .env file');
        }
    }

    public function sendEvent($event, $data)
    {
        try {
            if (empty($this->webhookUrl)) {
                Log::error('Cannot send event: Webhook URL is empty');
                return false;
            }

            Log::info('Sending event to Zapier', [
                'url' => $this->webhookUrl,
                'event' => $event,
                'data' => $data
            ]);

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

            Log::info('Event sent successfully to Zapier', [
                'event' => $event,
                'response' => $response->body()
            ]);

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