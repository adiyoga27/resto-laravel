<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AdditionalCost;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdditionalCostController extends Controller
{
    public function index(): Response
    {
        $costs = AdditionalCost::with('users:id,name,email')->ordered()->paginate(20);

        return Inertia::render('Admin/AdditionalCosts/Index', [
            'costs' => $costs,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AdditionalCosts/Create', [
            'users' => $this->assignableUsers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $cost = AdditionalCost::create([
            'name' => $validated['name'],
            'rate' => $validated['rate'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        $cost->users()->sync($validated['user_ids'] ?? []);

        ActivityLogger::log('additional_cost.created', 'Membuat biaya tambahan "'.$cost->name.'"', $cost);

        return redirect()->route('admin.additional-costs.index')->with('success', 'Biaya tambahan berhasil dibuat.');
    }

    public function edit(AdditionalCost $additionalCost): Response
    {
        return Inertia::render('Admin/AdditionalCosts/Edit', [
            'cost' => $additionalCost,
            'userIds' => $additionalCost->users()->pluck('users.id'),
            'users' => $this->assignableUsers(),
        ]);
    }

    public function update(Request $request, AdditionalCost $additionalCost): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $additionalCost->update([
            'name' => $validated['name'],
            'rate' => $validated['rate'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);
        $additionalCost->users()->sync($validated['user_ids'] ?? []);

        ActivityLogger::log('additional_cost.updated', 'Mengupdate biaya tambahan "'.$additionalCost->name.'"', $additionalCost);

        return redirect()->route('admin.additional-costs.index')->with('success', 'Biaya tambahan berhasil diupdate.');
    }

    public function destroy(AdditionalCost $additionalCost): RedirectResponse
    {
        $name = $additionalCost->name;
        $additionalCost->delete();

        ActivityLogger::log('additional_cost.deleted', 'Menghapus biaya tambahan "'.$name.'"');

        return redirect()->route('admin.additional-costs.index')->with('success', 'Biaya tambahan berhasil dihapus.');
    }

    /**
     * Users that can be charged an additional cost when they create orders.
     *
     * @return Collection<int, User>
     */
    private function assignableUsers(): Collection
    {
        return User::query()
            ->where('role', '!=', UserRole::Dapur)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }
}
