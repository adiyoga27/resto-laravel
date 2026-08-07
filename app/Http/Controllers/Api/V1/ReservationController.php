<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * List reservations
     *
     * @group Reservations
     *
     * @authenticated
     *
     * @response 200 [{"id":1,"restaurant_table_id":1,"reservation_time":"2026-01-15 19:00:00","guest_count":4,"status":"pending","restaurant_table":{"id":1,"table_number":"T01","capacity":4}}]
     */
    public function index(): JsonResponse
    {
        $reservations = Reservation::with('table')
            ->where('customer_id', auth()->id())
            ->orderBy('reservation_time', 'desc')
            ->get();

        return response()->json($reservations);
    }

    /**
     * Create reservation
     *
     * @group Reservations
     *
     * @authenticated
     *
     * @bodyParam restaurant_table_id integer required Table ID. Example: 1
     * @bodyParam reservation_time string required Reservation datetime. Example: 2026-01-15 19:00:00
     * @bodyParam guest_count integer required Number of guests. Example: 4
     * @bodyParam notes string Optional notes.
     *
     * @response 201 {"id":1,"restaurant_table_id":1,"reservation_time":"2026-01-15 19:00:00","guest_count":4,"status":"pending","restaurant_table":{"id":1,"table_number":"T01"}}
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_table_id' => ['required', 'exists:restaurant_tables,id'],
            'reservation_time' => ['required', 'date', 'after:now'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['customer_id'] = auth()->id();
        $validated['status'] = ReservationStatus::Pending;

        $reservation = Reservation::create($validated);

        return response()->json($reservation->load('restaurantTable'), 201);
    }

    /**
     * Show reservation detail
     *
     * @group Reservations
     *
     * @authenticated
     *
     * @urlParam reservation integer required Reservation ID.
     *
     * @response 200 {"id":1,"restaurant_table_id":1,"reservation_time":"2026-01-15 19:00:00","guest_count":4,"status":"pending","restaurant_table":{"id":1,"table_number":"T01","capacity":4}}
     */
    public function show(Reservation $reservation): JsonResponse
    {
        if ($reservation->customer_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($reservation->load('restaurantTable'));
    }
}
