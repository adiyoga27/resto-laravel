<?php

namespace App\Http\Controllers;

use App\Enums\OrderChannel;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TableStatus;
use App\Models\AdditionalCost;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(): Response
    {
        $menuItems = MenuItem::with('category')
            ->where('is_active', true)
            ->orderBy('menu_category_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy(function ($item) {
                return $item->category?->name ?? 'Tanpa Kategori';
            });

        $tables = RestaurantTable::orderByRaw("CASE status WHEN 'kosong' THEN 0 WHEN 'terisi' THEN 1 WHEN 'direservasi' THEN 2 ELSE 3 END")
            ->orderBy('table_number')
            ->get();

        $activeOrders = Order::with(['orderItems.menuItem', 'restaurantTable', 'payments', 'createdBy'])
            ->whereIn('order_status', ['baru', 'diproses', 'siap'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Pos/Index', [
            'menuItems' => $menuItems,
            'tables' => $tables,
            'additionalCosts' => AdditionalCost::active()->ordered()->get(),
            'activeOrders' => $activeOrders,
            'printer' => config('pos.printer'),
            'successOrder' => session('success_order_id') ? [
                'id' => session('success_order_id'),
                'number' => session('success_order_number'),
                'total' => (float) session('success_order_total'),
                'items' => session('success_order_items'),
            ] : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('items'))) {
            $request->merge(['items' => json_decode($request->input('items'), true) ?? []]);
        }

        $validated = $request->validate([
            'order_type' => ['required', 'string', 'in:dine-in,delivery,pickup'],
            'restaurant_table_id' => ['nullable', 'required_if:order_type,dine-in', 'exists:restaurant_tables,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,card'],
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'additional_cost_id' => ['nullable', 'integer', 'exists:additional_costs,id'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['order_type'] === 'dine-in') {
            $table = RestaurantTable::findOrFail($validated['restaurant_table_id']);
            if ($table->status === TableStatus::Kosong) {
                $table->update(['status' => TableStatus::Terisi]);
            }
        }

        $discount = (float) ($validated['discount'] ?? 0);
        $additionalCost = $validated['additional_cost_id'] ?? null ? AdditionalCost::find($validated['additional_cost_id']) : null;
        $additionalCostRate = $additionalCost?->rate !== null ? (float) $additionalCost->rate : 0;

        $order = Order::create([
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'restaurant_table_id' => $validated['restaurant_table_id'] ?? null,
            'created_by' => auth()->id(),
            'channel' => OrderChannel::Pos,
            'order_type' => OrderType::from($validated['order_type']),
            'order_status' => OrderStatus::Baru,
            'subtotal' => 0,
            'discount' => $discount,
            'additional_cost_id' => $additionalCost?->id,
            'additional_cost_name' => $additionalCost?->name,
            'additional_cost_rate' => $additionalCostRate > 0 ? $additionalCostRate : null,
            'additional_cost' => 0,
            'tax' => 0,
            'total' => 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $unitPrice = $menuItem->priceForOrderType($validated['order_type']);
            $itemSubtotal = $unitPrice * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'notes' => $item['notes'] ?? null,
            ]);

            $subtotal += $itemSubtotal;
        }

        $afterDiscount = max(0, $subtotal - $discount);
        $additionalCostAmount = $additionalCostRate > 0 ? round($afterDiscount * ($additionalCostRate / 100), 2) : 0;
        $total = $afterDiscount + $additionalCostAmount;

        $order->update([
            'subtotal' => $subtotal,
            'additional_cost_id' => $additionalCost?->id,
            'additional_cost_name' => $additionalCost?->name,
            'additional_cost_rate' => $additionalCostRate > 0 ? $additionalCostRate : null,
            'additional_cost' => $additionalCostAmount,
            'tax' => 0,
            'total' => $total,
        ]);

        $reference = $validated['payment_reference'] ?? null;

        $cashReceived = $validated['cash_received'] ?? null;

        Payment::create([
            'order_id' => $order->id,
            'method' => PaymentMethod::from($validated['payment_method']),
            'amount' => $validated['payment_amount'],
            'cash_received' => $cashReceived,
            'change' => ($cashReceived !== null) ? max(0, round($cashReceived - $total, 2)) : null,
            'status' => PaymentStatus::Paid,
            'reference' => $reference,
        ]);

        ActivityLogger::log('order.checkout', 'Checkout order #'.$order->order_number, $order, [
            'order_type' => $order->order_type->value,
            'total' => (float) $order->total,
            'payment_method' => PaymentMethod::from($validated['payment_method'])->value,
            'items' => $order->orderItems->sum('quantity'),
        ]);

        return redirect()->route('pos.index')->with([
            'success_order_id' => $order->id,
            'success_order_number' => $order->order_number,
            'success_order_total' => $order->total,
            'success_order_items' => $order->orderItems->count(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if (! in_array($order->order_status->value, ['baru', 'diproses', 'siap'])) {
            abort(403);
        }

        if (is_string($request->input('items'))) {
            $request->merge(['items' => json_decode($request->input('items'), true) ?? []]);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cash,qris,transfer,card'],
            'payment_amount' => ['required', 'numeric', 'min:0'],
            'cash_received' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'additional_cost_id' => ['nullable', 'integer', 'exists:additional_costs,id'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $discount = (float) ($validated['discount'] ?? 0);
        $additionalCost = $validated['additional_cost_id'] ?? null ? AdditionalCost::find($validated['additional_cost_id']) : null;
        $additionalCostRate = $additionalCost?->rate !== null ? (float) $additionalCost->rate : 0;

        $order->orderItems()->delete();

        $subtotal = 0;

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $unitPrice = $menuItem->priceForOrderType($validated['order_type']);
            $itemSubtotal = $unitPrice * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $item['quantity'],
                'price' => $unitPrice,
                'subtotal' => $itemSubtotal,
                'notes' => $item['notes'] ?? null,
            ]);

            $subtotal += $itemSubtotal;
        }

        $afterDiscount = max(0, $subtotal - $discount);
        $additionalCostAmount = $additionalCostRate > 0 ? round($afterDiscount * ($additionalCostRate / 100), 2) : 0;
        $total = $afterDiscount + $additionalCostAmount;

        $order->update([
            'customer_name' => $validated['customer_name'] ?? $order->customer_name,
            'customer_phone' => $validated['customer_phone'] ?? $order->customer_phone,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'additional_cost_id' => $additionalCost?->id,
            'additional_cost_name' => $additionalCost?->name,
            'additional_cost_rate' => $additionalCostRate > 0 ? $additionalCostRate : null,
            'additional_cost' => $additionalCostAmount,
            'tax' => 0,
            'total' => $total,
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        $reference = $validated['payment_reference'] ?? null;

        $cashReceived = $validated['cash_received'] ?? null;

        Payment::create([
            'order_id' => $order->id,
            'method' => PaymentMethod::from($validated['payment_method']),
            'amount' => $validated['payment_amount'],
            'cash_received' => $cashReceived,
            'change' => ($cashReceived !== null) ? max(0, round($cashReceived - $total, 2)) : null,
            'status' => PaymentStatus::Paid,
            'reference' => $reference,
        ]);

        ActivityLogger::log('order.updated', 'Mengupdate order #'.$order->order_number, $order, [
            'order_type' => $order->order_type->value,
            'total' => (float) $order->total,
            'payment_method' => PaymentMethod::from($validated['payment_method'])->value,
            'items' => $order->orderItems->sum('quantity'),
        ]);

        return redirect()->route('pos.index')->with([
            'success_order_id' => $order->id,
            'success_order_number' => $order->order_number,
            'success_order_total' => $order->total,
            'success_order_items' => $order->orderItems->count(),
        ]);
    }

    public function getActiveOrders(): JsonResponse
    {
        $orders = Order::with(['orderItems.menuItem', 'restaurantTable', 'payments', 'createdBy'])
            ->whereIn('order_status', ['baru', 'diproses', 'siap'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function completeOrder(Order $order): RedirectResponse
    {
        if ($order->order_status !== OrderStatus::Selesai) {
            $order->update(['order_status' => OrderStatus::Selesai]);
            $order->mutateStock();
        }

        ActivityLogger::log('order.completed', 'Menyelesaikan order #'.$order->order_number, $order);

        if ($order->restaurant_table_id) {
            $this->freeTableIfNoActiveOrders($order->restaurant_table_id);
        }

        return redirect()->route('pos.index')->with('success', 'Order #'.$order->order_number.' selesai.');
    }

    public function cancelOrder(Order $order): RedirectResponse
    {
        $order->update(['order_status' => OrderStatus::Dibatalkan]);

        ActivityLogger::log('order.cancelled', 'Membatalkan order #'.$order->order_number, $order);

        if ($order->restaurant_table_id) {
            $this->freeTableIfNoActiveOrders($order->restaurant_table_id);
        }

        return redirect()->route('pos.index')->with('success', 'Order #'.$order->order_number.' dibatalkan.');
    }

    private function freeTableIfNoActiveOrders(int $tableId): void
    {
        $hasActiveOrders = Order::where('restaurant_table_id', $tableId)
            ->whereIn('order_status', ['baru', 'diproses', 'siap'])
            ->exists();

        if (! $hasActiveOrders) {
            RestaurantTable::where('id', $tableId)->update(['status' => TableStatus::Kosong]);
        }
    }
}
