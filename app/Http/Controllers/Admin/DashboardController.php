<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('order_status', 'selesai')->sum('total');
        $activeOrders = Order::whereIn('order_status', ['baru', 'diproses'])->count();
        $totalCustomers = User::where('role', 'customer')->count();

        $monthlySales = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('order_status', 'selesai')
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $revenueData = array_fill(0, 12, 0);
        $orderData = array_fill(0, 12, 0);

        foreach ($monthlySales as $sale) {
            $revenueData[$sale->month - 1] = (float) $sale->revenue;
            $orderData[$sale->month - 1] = (int) $sale->orders;
        }

        return view('admin.dashboard', compact(
            'totalOrders', 'totalRevenue', 'activeOrders', 'totalCustomers',
            'months', 'revenueData', 'orderData'
        ));
    }
}
