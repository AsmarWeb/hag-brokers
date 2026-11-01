<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a user's profile and assigned access.
     */
    public function show(User $user): View
    {
        $user->load(['roles', 'permissions', 'customer']);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Display all users.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Update user's customer assignment.
     */
    public function updateCustomer(
        Request $request,
        User $user
    ): RedirectResponse {
        $data = $request->validate([
            'customer_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $user->update([
            'customer_id' => $data['customer_id'] ?? null,
        ]);

        return back()->with(
            'success',
            'User updated successfully.'
        );
    }

    /**
     * Display the user editing form.
     */
    public function edit(User $user): View
    {
        $user->load(['roles', 'permissions', 'customer']);

        return view('admin.users.edit', [
            'user' => $user,
            'customers' => \App\Models\Customer::query()->orderBy('id')->get(),
            'roles' => \App\Models\Role::query()->orderBy('name')->get(),
            'permissions' => \App\Models\Permission::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Save account details and access assignments atomically.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['required', 'integer', 'distinct', 'exists:roles,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', 'integer', 'distinct', 'exists:permissions,id'],
        ]);

        // Prevent the signed-in administrator from removing their own admin access.
        if ((string) $request->user()->getKey() === (string) $user->getKey()) {
            $retainsAdminAccess = \App\Models\Role::query()
                ->whereIn('id', $data['roles'] ?? [])
                ->whereIn('name', ['admin', 'super-admin'])
                ->exists();

            if (! $retainsAdminAccess) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'roles' => app()->getLocale() === 'ar'
                        ? 'يجب الاحتفاظ بدور إداري واحد على الأقل لحسابك.'
                        : 'Keep at least one administrator role on your own account.',
                ]);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $user) {
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->customer_id = $data['customer_id'] ?? null;

            if (! empty($data['password'])) {
                $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
            }

            $user->save();

            $user->syncRoles(array_map('intval', $data['roles'] ?? []));
            $user->syncPermissions(array_map('intval', $data['permissions'] ?? []));
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم تحديث المستخدم بنجاح.'
                : 'User updated successfully.');
    }

}
