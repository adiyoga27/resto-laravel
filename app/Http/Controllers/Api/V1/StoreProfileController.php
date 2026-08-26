<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StoreProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreProfileController extends Controller
{
    /**
     * Show store profile
     *
     * Get the single store profile (store name, address, phone). Returns `404`
     * when no profile has been created yet. Only accounts with the `admin` role
     * can access this endpoint.
     *
     * @group Store Profiles
     *
     * @authenticated
     *
     * @response 200 {"id":1,"store_name":"Resto Nusantara","store_address":"Jl. Merdeka No. 123, Jakarta","store_phone":"021-555-0123","is_active":true}
     * @response 404 {"message":"Store profile not found."}
     */
    public function index(): JsonResponse
    {
        $profile = StoreProfile::first();

        if (! $profile) {
            return response()->json(['message' => 'Store profile not found.'], 404);
        }

        return response()->json($profile);
    }

    /**
     * Create store profile
     *
     * Create the single store profile. Returns `409 Conflict` if a profile has
     * already been created (use PUT to update it instead).
     *
     * @group Store Profiles
     *
     * @authenticated
     *
     * @bodyParam store_name string required Store name. Example: Resto Nusantara
     * @bodyParam store_address string required Store address. Example: Jl. Merdeka No. 123, Jakarta
     * @bodyParam store_phone string required Store phone number. Example: 021-555-0123
     * @bodyParam is_active boolean Set profile as active. Default: true
     *
     * @response 201 {"id":1,"store_name":"Resto Nusantara","store_address":"Jl. Merdeka No. 123, Jakarta","store_phone":"021-555-0123","is_active":true}
     * @response 409 {"message":"Store profile already exists."}
     */
    public function store(Request $request): JsonResponse
    {
        if (StoreProfile::exists()) {
            return response()->json(['message' => 'Store profile already exists.'], 409);
        }

        $validated = $this->validatedData($request);

        $profile = StoreProfile::create($validated);

        return response()->json($profile, 201);
    }

    /**
     * Update store profile
     *
     * Update the single store profile. Creates the profile if it does not exist yet.
     *
     * @group Store Profiles
     *
     * @authenticated
     *
     * @bodyParam store_name string Store name. Example: Resto Nusantara 2
     * @bodyParam store_address string Store address. Example: Jl. Sudirman No. 90, Jakarta
     * @bodyParam store_phone string Store phone number. Example: 021-555-0456
     * @bodyParam is_active boolean Set profile as active. Example: true
     *
     * @response 200 {"id":1,"store_name":"Resto Nusantara 2","store_address":"Jl. Sudirman No. 90, Jakarta","store_phone":"021-555-0456","is_active":true}
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request, partial: true);

        $profile = StoreProfile::first();

        if (! $profile) {
            $profile = StoreProfile::create($validated);

            return response()->json($profile, 201);
        }

        $profile->update($validated);

        return response()->json($profile);
    }

    /**
     * @return array{store_name?: string, store_address?: string, store_phone?: string, is_active?: bool}
     */
    private function validatedData(Request $request, bool $partial = false): array
    {
        $validated = $request->validate([
            'store_name' => [$partial ? 'sometimes' : 'required', 'string', 'max:255'],
            'store_address' => [$partial ? 'sometimes' : 'required', 'string', 'max:1000'],
            'store_phone' => [$partial ? 'sometimes' : 'required', 'string', 'max:30'],
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        } elseif (! $partial) {
            $validated['is_active'] = true;
        }

        return $validated;
    }
}
