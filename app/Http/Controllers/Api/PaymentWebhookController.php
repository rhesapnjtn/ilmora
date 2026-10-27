<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.payment.webhook_secret');

        if (is_string($secret) && $secret !== '') {
            $signature = (string) $request->header('X-Webhook-Signature', '');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, $signature)) {
                return response()->json(['message' => 'Invalid signature.'], 401);
            }
        }

        $transactionId = $request->input('transaction_id')
            ?? $request->input('payment_id')
            ?? $request->input('reference');

        $status = strtolower((string) ($request->input('status') ?? $request->input('transaction_status') ?? ''));

        if ($transactionId === null) {
            return response()->json(['message' => 'Missing transaction id.'], 422);
        }

        $payment = Payment::query()
            ->where('payment_id', $transactionId)
            ->orWhere('reference', $transactionId)
            ->first();

        if ($payment === null) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if ($payment->status === 'success') {
            return response()->json(['message' => 'Already processed.']);
        }

        $newStatus = match (true) {
            in_array($status, ['success', 'paid', 'settlement', 'capture'], true) => 'success',
            in_array($status, ['failed', 'deny', 'cancel', 'expire', 'expired'], true) => 'failed',
            default => $payment->status,
        };

        $payment->update(['status' => $newStatus]);

        if ($newStatus === 'success' && $payment->subscription_id !== null) {
            $subscription = Subscription::query()->find($payment->subscription_id);

            if ($subscription !== null && $subscription->status !== 'active') {
                $subscription->update([
                    'status' => 'active',
                    'starts_at' => $subscription->starts_at ?? now()->toDateString(),
                    'expires_at' => $subscription->expires_at ?? now()->addMonth()->toDateString(),
                ]);
            }
        }

        return response()->json(['message' => 'Webhook processed.']);
    }
}
