<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Services\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        $period = $request->get('period', 'daily');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $query = Order::whereBetween('created_at', [$startDate, $endDate]);

        if (auth()->user()->isKasir()) {
            $query->forKasir(auth()->id());
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        $totalRevenue = $query->where('order_status', '!=', 'dibatalkan')->sum('total');
        $totalOrders = $query->count();

        return view('reports.sales', compact('orders', 'totalRevenue', 'totalOrders', 'period', 'startDate', 'endDate'));
    }

    public function show(Order $order): View
    {
        $order->load(['orderItems.menuItem.category', 'payments', 'restaurantTable', 'createdBy', 'customer']);

        return view('reports.sales-detail', [
            'order' => $order,
        ]);
    }

    public function updateOrder(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => ['required', 'string', 'in:baru,diproses,siap,selesai,dibatalkan'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
            'discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $wasNotCompleted = $order->order_status !== OrderStatus::Selesai;

        $order->update([
            'order_status' => OrderStatus::from($validated['order_status']),
            'customer_name' => $validated['customer_name'] ?? $order->customer_name,
            'customer_phone' => $validated['customer_phone'] ?? $order->customer_phone,
            'discount' => $validated['discount'] ?? $order->discount,
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        if ($wasNotCompleted && $validated['order_status'] === 'selesai') {
            $order->mutateStock();
        }

        $order->recalculateTotals();

        if ($order->restaurant_table_id && in_array($validated['order_status'], ['selesai', 'dibatalkan'])) {
            $this->freeTableIfNoActiveOrders($order->restaurant_table_id, $order->id);
        }

        return redirect()->route('reports.sales', request()->only(['start_date', 'end_date']))
            ->with('success', 'Order #'.$order->order_number.' berhasil diperbarui.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $tableId = $order->restaurant_table_id;

        $order->orderItems()->delete();
        $order->payments()->delete();
        $order->delete();

        if ($tableId) {
            $this->freeTableIfNoActiveOrders($tableId, $order->id);
        }

        return redirect()->route('reports.sales', request()->only(['start_date', 'end_date']))
            ->with('success', 'Order #'.$order->order_number.' berhasil dihapus.');
    }

    private function freeTableIfNoActiveOrders(int $tableId, int $excludeOrderId): void
    {
        $hasActiveOrders = Order::where('restaurant_table_id', $tableId)
            ->where('id', '!=', $excludeOrderId)
            ->whereIn('order_status', ['baru', 'diproses', 'siap'])
            ->exists();

        if (! $hasActiveOrders) {
            RestaurantTable::where('id', $tableId)->update(['status' => TableStatus::Kosong]);
        }
    }

    public function popularMenu(Request $request): View
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $popularItems = MenuItem::withCount(['orderItems as total_orders' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->where('order_status', '!=', 'dibatalkan');
            });
        }])->orderBy('total_orders', 'desc')->paginate(20);

        return view('reports.popular-menu', compact('popularItems', 'startDate', 'endDate'));
    }

    public function tables(Request $request): View
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $tableUsage = RestaurantTable::withCount(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->orderBy('table_number')->get();

        return view('reports.tables', compact('tableUsage', 'startDate', 'endDate'));
    }

    public function exportSales(Request $request): Response
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $excel = new ExcelExport;
        $excel->addRow(['Order #', 'Tanggal', 'Tipe', 'Channel', 'Status', 'Total']);
        foreach ($orders as $order) {
            $excel->addRow([
                $order->order_number,
                $order->created_at->format('d/m/Y H:i'),
                $order->order_type->label(),
                $order->channel->value,
                $order->order_status->label(),
                number_format($order->total, 0, ',', '.'),
            ]);
        }

        return $excel->download('laporan-penjualan-'.now()->format('Ymd').'.xlsx');
    }

    public function exportPopularMenu(Request $request): Response
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $items = MenuItem::withCount(['orderItems as total_orders' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                    ->where('order_status', '!=', 'dibatalkan');
            });
        }])->orderBy('total_orders', 'desc')->get();

        $excel = new ExcelExport;
        $excel->addRow(['Menu', 'Kategori', 'Total Order']);
        foreach ($items as $item) {
            $excel->addRow([
                $item->name,
                $item->category?->name ?? '-',
                (string) $item->total_orders,
            ]);
        }

        return $excel->download('laporan-menu-terlaris-'.now()->format('Ymd').'.xlsx');
    }

    public function exportTables(Request $request): Response
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : now()->startOfMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date'))->endOfDay() : now()->endOfDay();

        $tableUsage = RestaurantTable::withCount(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->orderBy('table_number')->get();

        $excel = new ExcelExport;
        $excel->addRow(['Meja', 'Kapasitas', 'Status', 'Total Order']);
        foreach ($tableUsage as $table) {
            $excel->addRow([
                $table->table_number,
                (string) $table->capacity,
                $table->status->label(),
                (string) $table->orders_count,
            ]);
        }

        return $excel->download('laporan-meja-'.now()->format('Ymd').'.xlsx');
    }
}
