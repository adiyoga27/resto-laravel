<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AdditionalCost;
use Illuminate\Http\JsonResponse;

class AdditionalCostController extends Controller
{
    /**
     * List available additional costs
     *
     * Get the list of active additional costs (e.g. service charge 5%) that can be
     * applied to an order via `additional_cost_id`.
     *
     * @group Additional Costs
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"name":"Service Charge","rate":5,"sort_order":1}]
     */
    public function index(): JsonResponse
    {
        $costs = AdditionalCost::active()->ordered()->get();

        return response()->json($costs);
    }
}
