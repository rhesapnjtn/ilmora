<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->with('subscription')
            ->latest('id')
            ->get()
            ->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'reference' => $payment->reference,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'paymentMethod' => $payment->payment_method,
                'notes' => $payment->notes,
                'subscriptionId' => $payment->subscription_id,
                'plan' => $payment->subscription?->plan,
                'billingCycle' => $payment->subscription?->billing_cycle,
                'createdAt' => $payment->created_at?->toIso8601String(),
            ])
            ->all();

        return response()->json(['payments' => $payments]);
    }
}
