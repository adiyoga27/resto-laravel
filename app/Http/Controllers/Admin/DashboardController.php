<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardController extends Controller
{
    public function index(): InertiaResponse
    {
        $totalOrders = Order::count();
        $totalRevenue = Order::where('order_status', 'selesai')->sum('total');
        $activeOrders = Order::whereIn('order_status', ['baru', 'diproses'])->count();
        $totalCustomers = User::where('role', 'customer')->count();

        $dailyData = $this->getDailyData();
        $weeklyData = $this->getWeeklyData();
        $monthlyData = $this->getMonthlyData();

        return Inertia::render('Dashboard', [
            'totalOrders' => $totalOrders,
            'totalRevenue' => (float) $totalRevenue,
            'activeOrders' => $activeOrders,
            'totalCustomers' => $totalCustomers,
            'dailyData' => $dailyData,
            'weeklyData' => $weeklyData,
            'monthlyData' => $monthlyData,
        ]);
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
        $sales = Order::select('created_at', 'total')
            ->where('order_status', 'selesai')
            ->where('created_at', '>=', now()->subWeeks(12))
            ->get();

        $weeks = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weeks[$start->format('Y-W')] = ['start' => $start, 'revenue' => 0, 'orders' => 0];
        }

        foreach ($sales as $sale) {
            $key = $sale->created_at->startOfWeek(Carbon::MONDAY)->format('Y-W');
            if (isset($weeks[$key])) {
                $weeks[$key]['revenue'] += (float) $sale->total;
                $weeks[$key]['orders'] += 1;
            }
        }

        $labels = [];
        $revenue = [];
        $orders = [];

        foreach ($weeks as $week) {
            $end = $week['start']->copy()->endOfWeek(Carbon::SUNDAY);
            $labels[] = $week['start']->translatedFormat('d M').' - '.$end->translatedFormat('d M');
            $revenue[] = $week['revenue'];
            $orders[] = $week['orders'];
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'orders' => $orders];
    }

    private function getMonthlyData(): array
    {
        $sales = Order::select('created_at', 'total')
            ->where('order_status', 'selesai')
            ->whereYear('created_at', now()->year)
            ->get();

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $revenue = array_fill(0, 12, 0);
        $orders = array_fill(0, 12, 0);

        foreach ($sales as $sale) {
            $month = (int) $sale->created_at->format('n') - 1;
            $revenue[$month] += (float) $sale->total;
            $orders[$month] += 1;
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
