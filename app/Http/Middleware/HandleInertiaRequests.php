<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role->value,
                    'role_label' => match ($request->user()->role) {
                        UserRole::Admin => 'Admin',
                        UserRole::Kasir => 'Kasir',
                        UserRole::Dapur => 'Dapur',
                        UserRole::Customer => 'Customer',
                    },
                    'is_admin' => $request->user()->isAdmin(),
                    'is_kasir' => $request->user()->isKasir(),
                    'is_dapur' => $request->user()->isDapur(),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'appName' => config('app.name', 'RestoApp'),
        ];
    }
}
