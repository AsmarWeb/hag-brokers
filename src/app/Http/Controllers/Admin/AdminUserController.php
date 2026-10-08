<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laratrust\Models\Permission;
use Laratrust\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display all users.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->with([
                'customer',
                'roles',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

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
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        return view('admin.users.create', [
            'customers' => $customers,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Display a specific user.
     */
    public function show(User $user): View
    {
        $user->load([
            'customer',
            'roles',
            'permissions',
        ]);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user): View
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $user->load([
            'roles',
            'permissions',
        ]);

        return view('admin.users.edit', [
            'user' => $user,
            'customers' => $customers,
            'roles' => $roles,
            'permissions' => $permissions,
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
                'exists:customers,id',
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

    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Prevent deleting your own account
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting a super-admin
        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Super-admin users cannot be deleted.');
        }

        $userName = $user->name ?: $user->email;

        // Remove Laratrust role/permission assignments
        $user->roles()->detach();

        if (method_exists($user, 'permissions')) {
            $user->permissions()->detach();
        }

        // Delete the user
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$userName} deleted successfully.");
    }
}
