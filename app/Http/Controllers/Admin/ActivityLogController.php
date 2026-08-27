<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ExcelExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->string('start_date')) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->string('end_date'))->endOfDay() : now()->endOfDay();

        $logs = ActivityLog::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('description', 'like', '%'.$request->string('search').'%')
                        ->orWhere('action', 'like', '%'.$request->string('search').'%');
                });
            })
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'actionGroups' => $this->actionGroups(),
            'filters' => $request->only(['search', 'user_id', 'action', 'start_date', 'end_date']),
        ]);
    }

    public function export(Request $request): Response
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->string('start_date')) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->string('end_date'))->endOfDay() : now()->endOfDay();

        $logs = ActivityLog::with('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('description', 'like', '%'.$request->string('search').'%')
                        ->orWhere('action', 'like', '%'.$request->string('search').'%');
                });
            })
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $excel = new ExcelExport;
        $excel->addRow(['Tanggal', 'User', 'Aksi', 'Deskripsi', 'IP Address', 'Perangkat']);
        foreach ($logs as $log) {
            $excel->addRow([
                $log->created_at->format('d/m/Y H:i'),
                $log->user?->name ?? 'Sistem',
                $log->action,
                $log->description ?? '',
                $log->ip_address ?? '-',
                $log->user_agent ?? '-',
            ]);
        }

        return $excel->download('log-aktivitas-user-'.now()->format('Ymd').'.xlsx');
    }

    /**
     * @return array<string, array{label: string, items: array<int, string>}>
     */
    private function actionGroups(): array
    {
        return [
            'auth' => [
                'label' => 'Autentikasi',
                'items' => ['auth.login', 'auth.logout'],
            ],
            'order' => [
                'label' => 'Pesanan',
                'items' => ['order.checkout', 'order.created', 'order.updated', 'order.completed', 'order.cancelled', 'order.status_updated'],
            ],
            'sales' => [
                'label' => 'Penjualan',
                'items' => ['sales.updated', 'sales.deleted'],
            ],
            'user' => [
                'label' => 'User',
                'items' => ['user.created', 'user.updated', 'user.deleted'],
            ],
            'menu' => [
                'label' => 'Menu',
                'items' => ['menu_category.created', 'menu_category.updated', 'menu_category.deleted', 'menu_item.created', 'menu_item.updated', 'menu_item.deleted'],
            ],
            'table' => [
                'label' => 'Meja',
                'items' => ['table.created', 'table.updated', 'table.deleted'],
            ],
            'cost' => [
                'label' => 'Biaya Tambahan & Toko',
                'items' => ['additional_cost.created', 'additional_cost.updated', 'additional_cost.deleted', 'store_profile.created', 'store_profile.updated'],
            ],
            'stock' => [
                'label' => 'Stok',
                'items' => ['ingredient.created', 'ingredient.updated', 'ingredient.deleted', 'recipe.created', 'recipe.updated', 'recipe.deleted', 'stock_log.created', 'stock_log.production', 'stock_opname.created', 'stock_opname.posted', 'stock_opname.deleted'],
            ],
            'cash_flow' => [
                'label' => 'Arus Kas',
                'items' => ['cash_flow.created', 'cash_flow.updated', 'cash_flow.deleted', 'cash_flow.posted', 'cash_flow.unposted', 'cash_flow.posted_transactions'],
            ],
            'reservation' => [
                'label' => 'Reservasi',
                'items' => ['reservation.created'],
            ],
            'profile' => [
                'label' => 'Profil',
                'items' => ['profile.updated', 'profile.password_updated'],
            ],
        ];
    }
}
