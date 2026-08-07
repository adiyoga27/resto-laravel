<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['orderItems.menuItem', 'payments'])
            ->where('customer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_type' => ['required', 'string', 'in:delivery,pickup'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
            'delivery_address' => ['required_if:order_type,delivery', 'string', 'max:500'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::create([
            'customer_id' => auth()->id(),
            'channel' => OrderChannel::Mobile,
            'order_type' => OrderType::from($validated['order_type']),
            'order_status' => OrderStatus::Baru,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'notes' => $validated['notes'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
        ]);

        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $itemSubtotal = $menuItem->price * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'price' => $menuItem->price,
                'subtotal' => $itemSubtotal,
                'notes' => $item['notes'] ?? null,
            ]);

            $subtotal += $itemSubtotal;
        }

        $tax = $subtotal * 0.11;
        $total = $subtotal + $tax;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return response()->json($order->load('orderItems.menuItem'), 201);
    }

    public function show(Order $order): JsonResponse
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($order->load(['orderItems.menuItem', 'payments', 'restaurantTable']));
    }
}
