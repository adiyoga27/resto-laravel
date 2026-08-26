<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/StoreProfiles/Form', [
            'profile' => StoreProfile::first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $this->upsert($validated);

        return redirect()->route('admin.store-profiles.index')->with('success', 'Data toko berhasil disimpan.');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $this->upsert($validated);

        return redirect()->route('admin.store-profiles.index')->with('success', 'Data toko berhasil diupdate.');
    }

    /**
     * @return array{store_name: string, store_address: string, store_phone: string, is_active: bool}
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_address' => ['required', 'string', 'max:1000'],
            'store_phone' => ['required', 'string', 'max:30'],
        ]);

        return array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsert(array $data): void
    {
        $profile = StoreProfile::first();

        if ($profile) {
            $profile->update($data);

            return;
        }

        StoreProfile::create($data);
    }
}
