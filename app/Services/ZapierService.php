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

            // تحويل التواريخ إلى تنسيق ISO
            // Convert dates to ISO format
            $data = $this->formatDates($data);

            Log::info('Sending event to Zapier', [
                'url' => $this->webhookUrl,
                'event' => $event,
                'data' => $data
            ]);

            $response = Http::post($this->webhookUrl, [
                'event' => $event,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
                'source' => 'onsaler'
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

    private function formatDates($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->formatDates($value);
                } elseif ($value instanceof \DateTime) {
                    $data[$key] = $value->toIso8601String();
                }
            }
        }
        return $data;
    }
} 