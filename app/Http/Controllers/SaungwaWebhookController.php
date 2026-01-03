<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SaungwaWebhookController extends Controller
{
    /**
     * Handle incoming webhook from SaungWA
     */
    public function handle(Request $request)
    {
        // Log incoming webhook for debugging
        Log::channel('saungwa')->info('SaungWA Webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        $data = $request->all();

        // Handle different webhook types
        if (isset($data['message_status'])) {
            return $this->handleDeliveryStatus($data);
        }

        if (isset($data['message']) && isset($data['from'])) {
            return $this->handleIncomingMessage($data);
        }

        return response()->json(['status' => 'received']);
    }

    /**
     * Handle delivery status updates
     */
    protected function handleDeliveryStatus(array $data): \Illuminate\Http\JsonResponse
    {
        Log::channel('saungwa')->info('Delivery status update', $data);

        // You can update your PesanWa model status here if needed
        // Example: Update message status to 'delivered' when callback received

        return response()->json(['status' => 'delivery_status_received']);
    }

    /**
     * Handle incoming messages
     */
    protected function handleIncomingMessage(array $data): \Illuminate\Http\JsonResponse
    {
        Log::channel('saungwa')->info('Incoming message', $data);

        // Process incoming message here
        // Example: Save to database, trigger auto-reply, etc.

        return response()->json(['status' => 'message_received']);
    }
}
