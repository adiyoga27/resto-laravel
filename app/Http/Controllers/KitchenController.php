<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['orderItems.menuItem', 'restaurantTable'])
            ->whereIn('order_status', ['baru', 'diproses', 'siap'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kitchen.index', compact('orders'));
    }

    public function getOrders(): JsonResponse
    {
        $orders = Order::with(['orderItems.menuItem', 'restaurantTable'])
            ->whereIn('order_status', ['baru', 'diproses', 'siap'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function updateStatus(Order $order): JsonResponse
    {
        $validStatuses = ['baru', 'diproses', 'siap', 'selesai'];
        $nextStatus = request('status');

        if (! in_array($nextStatus, $validStatuses)) {
            return response()->json(['error' => 'Status tidak valid.'], 422);
        }

        $order->update(['order_status' => OrderStatus::from($nextStatus)]);

        if ($nextStatus === 'selesai' && $order->restaurant_table_id) {
            $hasActiveOrders = Order::where('restaurant_table_id', $order->restaurant_table_id)
                ->whereIn('order_status', ['baru', 'diproses', 'siap'])
                ->exists();

            if (! $hasActiveOrders) {
                RestaurantTable::where('id', $order->restaurant_table_id)
                    ->update(['status' => TableStatus::Kosong]);
            }
        }

        return response()->json(['success' => true, 'order' => $order->load(['orderItems.menuItem', 'restaurantTable'])]);
    }
}
