<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Show current user profile
     *
     * @group Profile
     *
     * @authenticated
     *
     * @response 200 {"id":1,"name":"John Doe","email":"john@example.com","phone":"08123456789","role":"customer"}
     */
    public function show(): JsonResponse
    {
        return response()->json(auth()->user());
    }
}
