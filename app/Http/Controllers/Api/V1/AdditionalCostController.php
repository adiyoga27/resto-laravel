<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdditionalCostController extends Controller
{
    /**
     * List available additional costs
     *
     * Get the active additional costs (e.g. service charge 5%) assigned to the
     * authenticated user. The first one is applied automatically to the user's orders.
     *
     * @group Additional Costs
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"name":"Service Charge","rate":5,"sort_order":1}]
     */
    public function index(Request $request): JsonResponse
    {
        $costs = $request->user()->additionalCosts()->active()->ordered()->get();

        return response()->json($costs);
    }
}
