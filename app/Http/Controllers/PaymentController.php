<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Payment;

class PaymentController extends Controller
{
    // API 1: Menerima request tagihan baru dari Ticketing Service
    public function store(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        // Generate Virtual Account (Mocking)
        $vaNumber = '9900' . rand(10000000, 99999999);

        $payment = Payment::create([
            'booking_id' => $request->booking_id,
            'va_number' => $vaNumber,
            'amount' => $request->amount,
            'status' => 'PENDING',
        ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => [
                'payment_id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'va_number' => $payment->va_number,
                'amount' => $payment->amount,
                'status' => $payment->status,
            ]
        ], 201);
    }

    // API 2: Mensimulasikan user telah mentransfer uang
public function simulate(Request $request)
    {
        $request->validate([
            'va_number' => 'required|string',
            'status' => 'nullable|in:PAID,FAILED'
        ]);

        $payment = Payment::where('va_number', $request->va_number)->first();

        if (!$payment) {
            return response()->json(['message' => 'Virtual Account not found'], 404);
        }

        if ($payment->status !== 'PENDING') {
            return response()->json(['message' => 'Payment is already processed'], 400);
        }

        $targetStatus = $request->input('status', 'PAID');
        $payment->update(['status' => $targetStatus]);

        $ticketingWebhookUrl = env('TICKETING_SERVICE_URL') . '/api/webhooks/payment-notification';

        try {
            $response = Http::post($ticketingWebhookUrl, [
                'booking_id' => $payment->booking_id,
                'payment_status' => $payment->status,
                'va_number' => $payment->va_number,
                'paid_at' => $targetStatus === 'PAID' ? now()->toDateTimeString() : null,
            ]);

            return response()->json([
                'message' => 'Payment simulation successful and Callback sent',
                'callback_response_status' => $response->status()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment simulation successful, but failed to send Callback',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}