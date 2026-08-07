<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(): JsonResponse
    {
        $reservations = Reservation::with('table')
            ->where('customer_id', auth()->id())
            ->orderBy('reservation_time', 'desc')
            ->get();

        return response()->json($reservations);
    }

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

    public function show(Reservation $reservation): JsonResponse
    {
        if ($reservation->customer_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($reservation->load('restaurantTable'));
    }
}
