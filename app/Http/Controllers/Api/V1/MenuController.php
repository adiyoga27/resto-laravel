<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * List all menu categories with items
     *
     * @group Menu
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"name":"Makanan","menu_items":[{"id":1,"name":"Nasi Goreng","price":25000,"image":null,"description":"Nasi goreng spesial","is_active":true}]}]
     */
    public function index(): JsonResponse
    {
        $categories = MenuCategory::with(['menuItems' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json($categories);
    }

    /**
     * Show menu item detail
     *
     * @group Menu
     *
     * @authenticated
     *
     * @urlParam menuItem integer required Menu item ID.
     *
     * @response 200 {"id":1,"name":"Nasi Goreng","price":25000,"image":null,"description":"Nasi goreng spesial","category":{"id":1,"name":"Makanan"}}
     */
    public function show(MenuItem $menuItem): JsonResponse
    {
        $menuItem->load('category');

        return response()->json($menuItem);
    }
}
