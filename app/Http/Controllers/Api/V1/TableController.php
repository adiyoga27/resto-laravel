<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\RestaurantTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    /**
     * List tables
     *
     * @group Tables
     *
     * @authenticated
     *
     * @queryParam status string Filter by status (kosong, terisi, direservasi). Example: kosong
     *
     * @response 200 [{"id":1,"table_number":"T01","capacity":4,"status":"kosong"}]
     */
    public function index(Request $request): JsonResponse
    {
        $tables = RestaurantTable::query()
            ->when($request->has('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('table_number')
            ->get();

        return response()->json($tables);
    }

    /**
     * Create table
     *
     * @group Tables
     *
     * @authenticated
     *
     * @bodyParam table_number string required Table number/name. Example: T05
     * @bodyParam capacity integer required Seat capacity (min 1). Example: 4
     *
     * @response 201 {"id":5,"table_number":"T05","capacity":4,"status":"kosong"}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:restaurant_tables'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $validated['status'] = TableStatus::Kosong;
        $table = RestaurantTable::create($validated);

        return response()->json($table, 201);
    }

    /**
     * Show table detail
     *
     * @group Tables
     *
     * @authenticated
     *
     * @urlParam table integer required Table ID.
     *
     * @response 200 {"id":1,"table_number":"T01","capacity":4,"status":"kosong"}
     */
    public function show(RestaurantTable $table): JsonResponse
    {
        return response()->json($table);
    }

    /**
     * Update table
     *
     * @group Tables
     *
     * @authenticated
     *
     * @urlParam table integer required Table ID.
     *
     * @bodyParam table_number string Table number/name. Example: T05
     * @bodyParam capacity integer Seat capacity (min 1). Example: 6
     * @bodyParam status string Table status (kosong, terisi, direservasi). Example: terisi
     *
     * @response 200 {"id":1,"table_number":"T05","capacity":6,"status":"terisi"}
     */
    public function update(Request $request, RestaurantTable $table): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => ['sometimes', 'string', 'max:50', Rule::unique('restaurant_tables')->ignore($table->id)],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', Rule::enum(TableStatus::class)],
        ]);

        $table->update($validated);

        return response()->json($table);
    }

    /**
     * Delete table
     *
     * @group Tables
     *
     * @authenticated
     *
     * @urlParam table integer required Table ID.
     *
     * @response 200 {"message":"Table deleted successfully."}
     */
    public function destroy(RestaurantTable $table): JsonResponse
    {
        $table->delete();

        return response()->json(['message' => 'Table deleted successfully.']);
    }
}
