<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class ReceiptController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        if ($order->created_by && $order->created_by !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $order->load(['orderItems.menuItem', 'restaurantTable', 'payments', 'createdBy']);

        return response()->json([
            'store' => config('pos.store'),
            'order' => [
                'number' => $order->order_number,
                'date' => $order->created_at->format('d-m-Y H:i'),
                'type' => $order->order_type->label(),
                'status' => $order->order_status->label(),
                'cashier' => $order->createdBy?->name,
                'table' => $order->restaurantTable?->table_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'tax' => (float) $order->tax,
                'total' => (float) $order->total,
                'notes' => $order->notes,
                'items' => $order->orderItems->map(fn ($item) => [
                    'name' => $item->menuItem?->name ?? 'Item terhapus',
                    'qty' => $item->quantity,
                    'price' => (float) $item->price,
                    'subtotal' => (float) $item->subtotal,
                    'notes' => $item->notes,
                ]),
                'payments' => $order->payments->map(fn ($payment) => [
                    'method' => $payment->method->label(),
                    'amount' => (float) $payment->amount,
                    'reference' => $payment->reference,
                ]),
            ],
        ]);
    }
}
