<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $roles = collect(UserRole::cases())->map(fn ($role) => [
            'value' => $role->value,
            'label' => ucfirst($role->value),
        ]);

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);

        ActivityLogger::log('user.created', 'Membuat user "'.$user->name.'"', $user);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user): Response
    {
        $roles = collect(UserRole::cases())->map(fn ($role) => [
            'value' => $role->value,
            'label' => ucfirst($role->value),
        ]);

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $changes = $this->changesBetween($user->getAttributes(), $validated, ['password']);

        $user->update($validated);

        ActivityLogger::log('user.updated', 'Mengupdate user "'.$user->name.'"', $user, $changes);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;
        $user->delete();

        ActivityLogger::log('user.deleted', 'Menghapus user "'.$name.'"');

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    /**
     * @param  string[]  $ignore
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function changesBetween(array $before, array $after, array $ignore = []): array
    {
        $changes = [];

        foreach ($after as $key => $value) {
            if (in_array($key, $ignore, true)) {
                continue;
            }

            $old = $before[$key] ?? null;

            if ((string) $old === (string) $value) {
                continue;
            }

            $changes[$key] = ['from' => $old, 'to' => $value];
        }

        return $changes;
    }
}
