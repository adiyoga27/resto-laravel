<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = MenuCategory::with(['menuItems' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json($categories);
    }

    public function show(MenuItem $menuItem): JsonResponse
    {
        $menuItem->load('category');

        return response()->json($menuItem);
    }
}
