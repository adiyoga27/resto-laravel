<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = OrderStatusLog::with(['order', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->whereHas('order', function ($query) use ($request) {
                    $query->where('order_number', 'like', '%'.$request->string('search').'%');
                });
            })
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('to_status'), fn ($query) => $query->where('to_status', $request->string('to_status')))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        return Inertia::render('Admin/OrderLogs/Index', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['search', 'user_id', 'to_status']),
        ]);
    }
}
