<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Submit payment callback
     *
     * @group Payments
     *
     * @authenticated
     *
     * @bodyParam order_id integer required Order ID. Example: 1
     * @bodyParam method string required Payment method (cash/qris/transfer/card). Example: cash
     * @bodyParam amount numeric required Payment amount. Example: 22200
     * @bodyParam cash_received numeric Uang yang diterima untuk pembayaran tunai. Dikosongkan jika bukan tunai. Example: 50000
     * @bodyParam change numeric Kembalian yang diberikan (cash_received - amount). Dikosongkan jika bukan tunai atau kembalian 0. Example: 27800
     * @bodyParam reference string Optional payment reference.
     *
     * @response 201 {"id":1,"order_id":1,"method":"cash","amount":22200,"cash_received":50000,"change":27800,"status":"paid","reference":null}
     */
    public function callback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'method' => ['required', 'string', 'in:cash,qris,transfer,card'],
            'amount' => ['required', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'change' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string'],
        ]);

        $payment = Payment::create([
            'order_id' => $validated['order_id'],
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'cash_received' => $validated['cash_received'] ?? null,
            'change' => $validated['change'] ?? null,
            'status' => PaymentStatus::Paid,
            'reference' => $validated['reference'] ?? null,
        ]);

        return response()->json($payment, 201);
    }

    /**
     * Show payment detail
     *
     * @group Payments
     *
     * @authenticated
     *
     * @urlParam payment integer required Payment ID.
     *
     * @response 200 {"id":1,"order_id":1,"method":"cash","amount":22200,"cash_received":50000,"change":27800,"status":"paid","reference":null,"order":{"id":1,"order_number":"ORD-20260101-ABC123"}}
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json($payment->load('order'));
    }
}
