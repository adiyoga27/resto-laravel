<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
}
