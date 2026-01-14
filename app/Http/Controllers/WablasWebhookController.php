<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WablasWebhookController extends Controller
{
    /**
     * Handle incoming webhook from Wablas
     * 
     * Wablas webhook payload format:
     * - For delivery status: { "id": "...", "phone": "...", "message": "...", "status": "sent|delivered|read|failed" }
     * - For incoming message: { "id": "...", "phone": "...", "message": "...", "pushName": "...", "isGroup": false }
     */
    public function handle(Request $request)
    {
        // Log incoming webhook for debugging
        Log::info('Wablas Webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        $data = $request->all();

        // Handle delivery status updates
        if (isset($data['status'])) {
            return $this->handleDeliveryStatus($data);
        }

        // Handle incoming messages
        if (isset($data['message']) && isset($data['phone'])) {
            return $this->handleIncomingMessage($data);
        }

        return response()->json(['status' => 'received']);
    }

    /**
     * Handle delivery status updates from Wablas
     * 
     * Status values: sent, delivered, read, failed
     */
    protected function handleDeliveryStatus(array $data): \Illuminate\Http\JsonResponse
    {
        Log::info('Wablas delivery status update', $data);

        // You can update your PesanWa model status here if needed
        // Example: Update message status based on Wablas callback
        // $messageId = $data['id'] ?? null;
        // $status = $data['status'] ?? null;

        return response()->json(['status' => 'delivery_status_received']);
    }

    /**
     * Handle incoming messages from Wablas
     */
    protected function handleIncomingMessage(array $data): \Illuminate\Http\JsonResponse
    {
        Log::info('Wablas incoming message', [
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'pushName' => $data['pushName'] ?? null,
            'isGroup' => $data['isGroup'] ?? false,
        ]);

        // Process incoming message here
        // Example: Save to database, trigger auto-reply, etc.

        return response()->json(['status' => 'message_received']);
    }
}
