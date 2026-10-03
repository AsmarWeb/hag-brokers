<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laratrust\Models\Role;
use Laratrust\Models\Permission;

class AdminUserController extends Controller
{
    /**
     * Display all users.
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['roles', 'customer']);

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
     * Show create user form.
     */
    public function create(): View
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->orderBy('company_name')
            ->get();

        return view('admin.users.create', [
            'roles' => $roles,
            'permissions' => $permissions,
            'customers' => $customers,
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'roles' => [
                'nullable',
                'array',
            ],

            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'customer_id' => $data['customer_id'] ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign Roles
        |--------------------------------------------------------------------------
        */

        if (!empty($data['roles'])) {
            $roles = Role::whereIn('id', $data['roles'])->get();

            $user->syncRoles($roles);
        }

        /*
        |--------------------------------------------------------------------------
        | Assign Direct Permissions
        |--------------------------------------------------------------------------
        */

        if (!empty($data['permissions'])) {
            $permissions = Permission::whereIn(
                'id',
                $data['permissions']
            )->get();

            $user->syncPermissions($permissions);
        }

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User created successfully.'
            );
    }

    /**
     * Display a specific user.
     */
    public function show(User $user): View
    {
        $user->load([
            'roles',
            'permissions',
            'customer',
        ]);

        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user): View
    {
        $user->load([
            'roles',
            'permissions',
        ]);

        $roles = Role::query()
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->orderBy('company_name')
            ->get();

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'customers' => $customers,
        ]);
    }

    /**
     * Update user.
     */
    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'customer_id' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            'roles' => [
                'nullable',
                'array',
            ],

            'roles.*' => [
                'integer',
                'exists:roles,id',
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->customer_id = $data['customer_id'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Update Password
        |--------------------------------------------------------------------------
        */

        if (!empty($data['password'])) {
            $user->password = Hash::make(
                $data['password']
            );
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Sync Roles
        |--------------------------------------------------------------------------
        */

        $roles = Role::whereIn(
            'id',
            $data['roles'] ?? []
        )->get();

        $user->syncRoles($roles);

        /*
        |--------------------------------------------------------------------------
        | Sync Direct Permissions
        |--------------------------------------------------------------------------
        */

        $permissions = Permission::whereIn(
            'id',
            $data['permissions'] ?? []
        )->get();

        $user->syncPermissions($permissions);

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User updated successfully.'
            );
    }

    /**
     * Delete user.
     */
    public function destroy(User $user): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent deleting yourself
        |--------------------------------------------------------------------------
        */

        if (auth()->id() === $user->id) {
            return back()->with(
                'error',
                'You cannot delete your own account.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove roles and permissions
        |--------------------------------------------------------------------------
        */

        $user->syncRoles([]);
        $user->syncPermissions([]);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User deleted successfully.'
            );
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
            'User customer assignment updated successfully.'
        );
    }
}
