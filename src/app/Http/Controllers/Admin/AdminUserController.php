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


    /**
     * Display the user creation form.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'customers' => \App\Models\Customer::query()->orderBy('id')->get(),
            'roles' => \App\Models\Role::query()->orderBy('name')->get(),
            'permissions' => \App\Models\Permission::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Create an account and its access assignments atomically.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['required', 'integer', 'distinct', 'exists:roles,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', 'integer', 'distinct', 'exists:permissions,id'],
        ]);

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
                'customer_id' => $data['customer_id'] ?? null,
            ]);

            $user->syncRoles(array_map('intval', $data['roles'] ?? []));
            $user->syncPermissions(array_map('intval', $data['permissions'] ?? []));

            return $user;
        });

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم إنشاء المستخدم بنجاح.'
                : 'User created successfully.');
    }


    /**
     * Delete a user while protecting administrator access and linked records.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $isAr = app()->getLocale() === 'ar';

        if ((string) $request->user()->getKey() === (string) $user->getKey()) {
            return redirect()->route('admin.users.index')->with('error', $isAr
                ? 'لا يمكنك حذف حسابك الحالي.'
                : 'You cannot delete your own account.');
        }

        try {
            $error = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $isAr) {
                $target = User::query()
                    ->whereKey($user->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $adminRoles = ['admin', 'super-admin'];

                if ($target->roles()->whereIn('name', $adminRoles)->exists()) {
                    $otherAdminExists = User::query()
                        ->where($target->getKeyName(), '!=', $target->getKey())
                        ->whereHas('roles', function ($query) use ($adminRoles) {
                            $query->whereIn('name', $adminRoles);
                        })
                        ->lockForUpdate()
                        ->first();

                    if (! $otherAdminExists) {
                        return $isAr
                            ? 'لا يمكن حذف آخر مسؤول في المنصة.'
                            : 'The last administrator cannot be deleted.';
                    }
                }

                // Check references before deletion, including cascading references.
                $schema = $target->getConnection()->getSchemaBuilder();

                foreach ($schema->getTableListing() as $table) {
                    foreach ($schema->getForeignKeys($table) as $foreignKey) {
                        if ($foreignKey['foreign_table'] !== $target->getTable()) {
                            continue;
                        }

                        $query = $target->getConnection()->table($table);
                        $matches = true;

                        foreach ($foreignKey['columns'] as $index => $column) {
                            $value = $target->getAttribute(
                                $foreignKey['foreign_columns'][$index]
                            );

                            if ($value === null) {
                                $matches = false;
                                break;
                            }

                            $query->where($column, $value);
                        }

                        if ($matches && $query->exists()) {
                            return $isAr
                                ? 'لا يمكن حذف المستخدم لوجود سجلات مرتبطة به. راجع ارتباطاته أولاً.'
                                : 'This user has linked records. Review those records before deleting the account.';
                        }
                    }
                }

                $target->syncRoles([]);
                $target->syncPermissions([]);
                $target->delete();

                return null;
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            $constraintError = in_array(
                (string) $exception->getCode(),
                ['23000', '23503'],
                true
            ) || in_array(
                (int) ($exception->errorInfo[1] ?? 0),
                [19, 787, 1451],
                true
            );

            if (! $constraintError) {
                throw $exception;
            }

            return redirect()->route('admin.users.index')->with('error', $isAr
                ? 'تعذر الحذف بسبب ارتباطات المستخدم بالبيانات.'
                : 'The user could not be deleted because of related data.');
        }

        return redirect()->route('admin.users.index')->with(
            $error === null ? 'success' : 'error',
            $error ?? ($isAr
                ? 'تم حذف المستخدم بنجاح.'
                : 'User deleted successfully.')
        );
    }

}
