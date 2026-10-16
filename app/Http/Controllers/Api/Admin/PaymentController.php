<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $payments = Payment::query()
            ->with(['user:id,name,email', 'course:id,title,slug', 'subscription:id,plan,billing_cycle,status'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('user', function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'payments' => collect($payments->items())->map(fn (Payment $payment): array => [
                'id' => $payment->id,
                'user' => [
                    'id' => $payment->user?->id,
                    'name' => $payment->user?->name,
                    'email' => $payment->user?->email,
                ],
                'course' => $payment->course ? [
                    'id' => $payment->course->id,
                    'title' => $payment->course->title,
                    'slug' => $payment->course->slug,
                ] : null,
                'subscription' => $payment->subscription ? [
                    'id' => $payment->subscription->id,
                    'plan' => $payment->subscription->plan,
                    'billingCycle' => $payment->subscription->billing_cycle,
                    'status' => $payment->subscription->status,
                ] : null,
                'reference' => $payment->reference,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'paymentMethod' => $payment->payment_method,
                'paymentId' => $payment->payment_id,
                'notes' => $payment->notes,
                'createdAt' => $payment->created_at?->toIso8601String(),
            ])->all(),
            'summary' => [
                'total' => Payment::query()->count(),
                'pending' => Payment::query()->where('status', 'pending')->count(),
                'success' => Payment::query()->where('status', 'success')->count(),
                'failed' => Payment::query()->where('status', 'failed')->count(),
                'refunded' => Payment::query()->where('status', 'refunded')->count(),
                'revenue' => (float) Payment::query()->where('status', 'success')->sum('amount'),
            ],
            'meta' => [
                'page' => $payments->currentPage(),
                'perPage' => $payments->perPage(),
                'total' => $payments->total(),
                'lastPage' => $payments->lastPage(),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function approve(Payment $payment): JsonResponse
    {
        if ($payment->status !== 'pending') {
            return response()->json(['message' => 'Hanya pembayaran berstatus pending yang dapat diverifikasi.'], 422);
        }

        $this->subscriptions->approve($payment);

        return response()->json(['message' => 'Pembayaran terverifikasi dan langganan telah diaktifkan.']);
    }

    public function reject(Payment $payment): JsonResponse
    {
        if ($payment->status !== 'pending') {
            return response()->json(['message' => 'Hanya pembayaran berstatus pending yang dapat ditolak.'], 422);
        }

        $this->subscriptions->reject($payment);

        return response()->json(['message' => 'Pembayaran ditolak dan pesanan dibatalkan.']);
    }
}
