<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function callback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', 'string', 'in:cash,qris,transfer,card'],
            'amount' => ['required', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string'],
        ]);

        $payment = Payment::create([
            'order_id' => $validated['order_id'],
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'status' => PaymentStatus::Paid,
            'reference' => $validated['reference'] ?? null,
        ]);

        return response()->json($payment, 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('order'));
    }
}
