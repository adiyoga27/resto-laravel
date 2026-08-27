<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdditionalCost;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdditionalCostController extends Controller
{
    public function index(): Response
    {
        $costs = AdditionalCost::ordered()->paginate(20);

        return Inertia::render('Admin/AdditionalCosts/Index', [
            'costs' => $costs,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AdditionalCosts/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $cost = AdditionalCost::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]));

        ActivityLogger::log('additional_cost.created', 'Membuat biaya tambahan "'.$cost->name.'"', $cost);

        return redirect()->route('admin.additional-costs.index')->with('success', 'Biaya tambahan berhasil dibuat.');
    }

    public function edit(AdditionalCost $additionalCost): Response
    {
        return Inertia::render('Admin/AdditionalCosts/Edit', [
            'cost' => $additionalCost,
        ]);
    }

    public function update(Request $request, AdditionalCost $additionalCost): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $additionalCost->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]));

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
}
