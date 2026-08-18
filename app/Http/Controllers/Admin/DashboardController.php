<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Response;
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

        $dailyData = $this->getDailyData();
        $weeklyData = $this->getWeeklyData();
        $monthlyData = $this->getMonthlyData();

        return view('admin.dashboard', compact(
            'totalOrders', 'totalRevenue', 'activeOrders', 'totalCustomers',
            'dailyData', 'weeklyData', 'monthlyData'
        ));
    }

    private function getDailyData(): array
    {
        $sales = Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('order_status', 'selesai')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $labels = [];
        $revenue = [];
        $orders = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->translatedFormat('d M');
            $sale = $sales->firstWhere('date', $date);
            $revenue[] = $sale ? (float) $sale->revenue : 0;
            $orders[] = $sale ? (int) $sale->orders : 0;
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'orders' => $orders];
    }

    private function getWeeklyData(): array
    {
        $sales = Order::select(
            DB::raw('YEARWEEK(created_at, 1) as week'),
            DB::raw('MIN(DATE(created_at)) as week_start'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('order_status', 'selesai')
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy(DB::raw('YEARWEEK(created_at, 1)'))
            ->orderBy('week')
            ->get();

        $labels = [];
        $revenue = [];
        $orders = [];

        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
            $labels[] = $start->translatedFormat('d M').' - '.$end->translatedFormat('d M');
            $sale = $sales->firstWhere('week_start', $start->format('Y-m-d'));
            $revenue[] = $sale ? (float) $sale->revenue : 0;
            $orders[] = $sale ? (int) $sale->orders : 0;
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'orders' => $orders];
    }

    private function getMonthlyData(): array
    {
        $sales = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('order_status', 'selesai')
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $revenue = array_fill(0, 12, 0);
        $orders = array_fill(0, 12, 0);

        foreach ($sales as $sale) {
            $revenue[$sale->month - 1] = (float) $sale->revenue;
            $orders[$sale->month - 1] = (int) $sale->orders;
        }

        return ['labels' => $monthNames, 'revenue' => $revenue, 'orders' => $orders];
    }

    public function export(): Response
    {
        $daily = $this->getDailyData();
        $weekly = $this->getWeeklyData();
        $monthly = $this->getMonthlyData();

        $excel = new ExcelExport;

        $excel->addRow(['GRAFIK OMZET HARIAN (30 Hari Terakhir)']);
        $excel->addRow([]);
        $excel->addRow(['Tanggal', 'Omzet (Rp)', 'Jumlah Order']);
        for ($i = 0; $i < count($daily['labels']); $i++) {
            $excel->addRow([$daily['labels'][$i], number_format($daily['revenue'][$i], 0, ',', '.'), (string) $daily['orders'][$i]]);
        }

        $excel->addRow([]);
        $excel->addRow([]);
        $excel->addRow(['GRAFIK OMZET MINGGUAN (12 Minggu Terakhir)']);
        $excel->addRow([]);
        $excel->addRow(['Minggu', 'Omzet (Rp)', 'Jumlah Order']);
        for ($i = 0; $i < count($weekly['labels']); $i++) {
            $excel->addRow([$weekly['labels'][$i], number_format($weekly['revenue'][$i], 0, ',', '.'), (string) $weekly['orders'][$i]]);
        }

        $excel->addRow([]);
        $excel->addRow([]);
        $excel->addRow(['GRAFIK OMZET BULANAN ('.now()->year.')']);
        $excel->addRow([]);
        $excel->addRow(['Bulan', 'Omzet (Rp)', 'Jumlah Order']);
        for ($i = 0; $i < count($monthly['labels']); $i++) {
            $excel->addRow([$monthly['labels'][$i], number_format($monthly['revenue'][$i], 0, ',', '.'), (string) $monthly['orders'][$i]]);
        }

        return $excel->download('dashboard-omzet-'.now()->format('Ymd').'.xlsx');
    }
}
