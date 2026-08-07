<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SyncStatus;
use App\Http\Controllers\Controller;
use App\Models\MobileSyncLog;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Sync offline actions
     *
     * @group Sync
     *
     * @authenticated
     *
     * @bodyParam actions array required Array of actions to sync.
     * @bodyParam actions.*.idempotency_key string required Unique key per action.
     * @bodyParam actions.*.action_type string required Type: order or reservation.
     * @bodyParam actions.*.payload array required Action data.
     * @bodyParam device_id string required Device identifier.
     *
     * @response 200 {"results":[{"idempotency_key":"abc123","status":"synced","resource_id":1}]}
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'actions' => ['required', 'array'],
            'actions.*.idempotency_key' => ['required', 'string'],
            'actions.*.action_type' => ['required', 'string', 'in:order,reservation'],
            'actions.*.payload' => ['required', 'array'],
            'device_id' => ['required', 'string'],
        ]);

        $results = [];

        foreach ($validated['actions'] as $action) {
            $existing = MobileSyncLog::where('idempotency_key', $action['idempotency_key'])->first();

            if ($existing && $existing->status === SyncStatus::Synced) {
                $results[] = ['idempotency_key' => $action['idempotency_key'], 'status' => 'already_synced'];

                continue;
            }

            try {
                if ($action['action_type'] === 'order') {
                    $order = Order::create(array_merge($action['payload'], [
                        'customer_id' => auth()->id(),
                    ]));
                    $resourceId = $order->id;
                } else {
                    $reservation = Reservation::create(array_merge($action['payload'], [
                        'customer_id' => auth()->id(),
                    ]));
                    $resourceId = $reservation->id;
                }

                MobileSyncLog::updateOrCreate(
                    ['idempotency_key' => $action['idempotency_key']],
                    [
                        'user_id' => auth()->id(),
                        'device_id' => $validated['device_id'],
                        'action_type' => $action['action_type'],
                        'payload' => $action['payload'],
                        'status' => SyncStatus::Synced,
                        'synced_at' => now(),
                    ]
                );

                $results[] = [
                    'idempotency_key' => $action['idempotency_key'],
                    'status' => 'synced',
                    'resource_id' => $resourceId,
                ];
            } catch (\Exception $e) {
                MobileSyncLog::updateOrCreate(
                    ['idempotency_key' => $action['idempotency_key']],
                    [
                        'user_id' => auth()->id(),
                        'device_id' => $validated['device_id'],
                        'action_type' => $action['action_type'],
                        'payload' => $action['payload'],
                        'status' => SyncStatus::Failed,
                        'error_message' => $e->getMessage(),
                    ]
                );

                $results[] = [
                    'idempotency_key' => $action['idempotency_key'],
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
