<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return DB::transaction(function () use ($transactionId, $status, $request): JsonResponse {
            $payment = Payment::query()
                ->where('payment_id', $transactionId)
                ->orWhere('reference', $transactionId)
                ->lockForUpdate()
                ->first();

            if ($payment === null) {
                return response()->json(['message' => 'Payment not found.'], 404);
            }

            // Idempotency: already processed successfully.
            if ($payment->status === 'success') {
                return response()->json(['message' => 'Already processed.']);
            }

            // Optional amount verification when the provider sends it.
            $providedAmount = $request->input('amount') ?? $request->input('gross_amount');
            if ($providedAmount !== null && (string) $providedAmount !== '' && (float) $providedAmount !== (float) $payment->amount) {
                return response()->json(['message' => 'Amount mismatch.'], 422);
            }

            $newStatus = match (true) {
                in_array($status, ['success', 'paid', 'settlement', 'capture'], true) => 'success',
                in_array($status, ['failed', 'deny', 'cancel', 'expire', 'expired'], true) => 'failed',
                default => $payment->status,
            };

            if ($newStatus === $payment->status) {
                return response()->json(['message' => 'Webhook processed.']);
            }

            $payment->update(['status' => $newStatus]);

            if ($newStatus === 'success' && $payment->subscription_id !== null) {
                $subscription = Subscription::query()->lockForUpdate()->find($payment->subscription_id);

                if ($subscription !== null && $subscription->status !== 'active') {
                    $subscription->update([
                        'status' => 'active',
                        'starts_at' => $subscription->starts_at ?? now()->toDateString(),
                        'expires_at' => $subscription->expires_at ?? now()->addMonth()->toDateString(),
                    ]);
                }
            }

            return response()->json(['message' => 'Webhook processed.']);
        });
    }
}