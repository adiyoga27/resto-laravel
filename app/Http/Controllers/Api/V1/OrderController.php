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
    /**
     * List orders
     *
     * @group Orders
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"order_number":"ORD-20260101-ABC123","order_type":"delivery","order_status":"baru","subtotal":50000,"discount":0,"tax":5500,"total":55500,"customer_name":"John","customer_phone":"08123456789","order_items":[{"id":1,"menu_item_id":1,"quantity":2,"price":25000,"subtotal":50000,"notes":null,"menu_item":{"id":1,"name":"Nasi Goreng","price":25000}}],"payments":[]}]
     */
    public function index(): JsonResponse
    {
        $orders = Order::with(['orderItems.menuItem', 'payments'])
            ->where('customer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Create order
     *
     * @group Orders
     *
     * @authenticated
     *
     * @bodyParam order_type string required Order type (delivery/pickup). Example: delivery
     * @bodyParam items array required Array of order items.
     * @bodyParam items.*.menu_item_id integer required Menu item ID. Example: 1
     * @bodyParam items.*.quantity integer required Quantity. Example: 2
     * @bodyParam items.*.notes string Optional notes for this item.
     * @bodyParam customer_name string required Customer name. Example: John Doe
     * @bodyParam customer_phone string required Customer phone number. Example: 08123456789
     * @bodyParam delivery_address string required if order_type=delivery. Example: Jl. Merdeka No. 10
     * @bodyParam discount numeric Discount amount. Example: 5000
     * @bodyParam notes string Optional order notes.
     *
     * @response 201 {"id":1,"order_number":"ORD-20260101-ABC123","order_type":"delivery","order_status":"baru","subtotal":50000,"discount":5000,"tax":4950,"total":49950,"customer_name":"John Doe","customer_phone":"08123456789","order_items":[{"id":1,"menu_item_id":1,"quantity":2,"price":25000,"subtotal":50000,"menu_item":{"id":1,"name":"Nasi Goreng","price":25000}}]}
     */
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
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $discount = (float) ($validated['discount'] ?? 0);

        $order = Order::create([
            'customer_id' => auth()->id(),
            'channel' => OrderChannel::Mobile,
            'order_type' => OrderType::from($validated['order_type']),
            'order_status' => OrderStatus::Baru,
            'subtotal' => 0,
            'discount' => $discount,
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

        $afterDiscount = max(0, $subtotal - $discount);
        $tax = $afterDiscount * 0.11;
        $total = $afterDiscount + $tax;

        $order->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        return response()->json($order->load('orderItems.menuItem'), 201);
    }

    /**
     * Show order detail
     *
     * @group Orders
     *
     * @authenticated
     *
     * @urlParam order integer required Order ID.
     *
     * @response 200 {"id":1,"order_number":"ORD-20260101-ABC123","order_type":"delivery","order_status":"baru","subtotal":50000,"discount":0,"tax":5500,"total":55500,"order_items":[{"id":1,"menu_item_id":1,"quantity":2,"price":25000,"subtotal":50000,"menu_item":{"id":1,"name":"Nasi Goreng","price":25000}}],"payments":[]}
     */
    public function show(Order $order): JsonResponse
    {
        if ($order->customer_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($order->load(['orderItems.menuItem', 'payments', 'restaurantTable']));
    }
}
