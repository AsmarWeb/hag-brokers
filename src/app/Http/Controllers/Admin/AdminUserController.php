<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PhoneNumber;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Suspend a user account and revoke existing API tokens and database sessions.
     */
    public function block(Request $request, User $user): RedirectResponse
    {
        $isAr = app()->getLocale() === 'ar';

        if ((string) $request->user()->getKey() === (string) $user->getKey()) {
            return back()->with('error', $isAr ? 'لا يمكنك حظر حسابك الحالي.' : 'You cannot block your own account.');
        }

        if ($user->hasRole(['admin', 'super-admin'])) {
            return back()->with('error', $isAr ? 'لا يمكن حظر حسابات المسؤولين من هذه الصفحة.' : 'Administrator accounts cannot be blocked here.');
        }

        $data = $request->validate([
            'blocked_reason' => ['nullable', 'string', 'max:500'],
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $data) {
            $target = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $target->forceFill([
                'blocked_at' => now(),
                'blocked_reason' => $data['blocked_reason'] ?? null,
            ])->save();

            $target->tokens()->delete();

            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $target->getKey())->delete();
            }
        });

        return back()->with('success', $isAr ? 'تم حظر المستخدم وإلغاء جلساته ورموزه.' : 'User blocked; active sessions and API tokens were revoked.');
    }

    /**
     * Restore access to a previously suspended account.
     */
    public function unblock(Request $request, User $user): RedirectResponse
    {
        $isAr = app()->getLocale() === 'ar';

        $user->forceFill([
            'blocked_at' => null,
            'blocked_reason' => null,
        ])->save();

        return back()->with('success', $isAr ? 'تم فك حظر المستخدم.' : 'User access restored.');
    }

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
        $query = User::query()->with(['roles', 'permissions', 'customer']);

        if ($request->input('status') === 'blocked') {
            $query->whereNotNull('blocked_at');
        } elseif ($request->input('status') === 'active') {
            $query->whereNull('blocked_at');
        }

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
            'totalUsers' => User::count(),
            'activeUsers' => User::whereNull('blocked_at')->count(),
            'blockedUsers' => User::whereNotNull('blocked_at')->count(),
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
        $normalized = ['email' => strtolower(trim((string) $request->input('email')))];
        if ($request->exists('phone')) {
            $normalized['phone'] = PhoneNumber::normalize($request->input('phone'), $request->input('phone_country_code'));
        }
        $request->merge($normalized);

        // Preserve existing assignments on partial updates. The edit form sends
        // explicit markers so an intentionally empty selection can clear them.
        $rolesProvided = $request->boolean('roles_present') || $request->exists('roles');
        $permissionsProvided = $request->boolean('permissions_present') || $request->exists('permissions');

        if ($request->boolean('roles_present') && ! $request->exists('roles')) {
            $request->merge(['roles' => []]);
        }
        if ($request->boolean('permissions_present') && ! $request->exists('permissions')) {
            $request->merge(['permissions' => []]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user), new \App\Rules\NonDisposableEmail()],
            'phone_country_code' => ['nullable', 'string', \Illuminate\Validation\Rule::in(array_keys(config('phone.countries', [])))],
            'phone' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/D'],
            'password' => ['nullable', 'string', 'min:8', 'max:128', 'confirmed'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'roles_present' => ['sometimes', 'boolean'],
            'permissions_present' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['required', 'integer', 'distinct', 'exists:roles,id'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['required', 'integer', 'distinct', 'exists:permissions,id'],
        ]);

        if ($rolesProvided && (string) $request->user()->getKey() === (string) $user->getKey()) {
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

        $emailChanged = $user->email !== $data['email'];
        $phoneChanged = array_key_exists('phone', $data) && $user->phone !== $data['phone'];

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $user, $emailChanged, $phoneChanged, $rolesProvided, $permissionsProvided) {
            $user->name = $data['name'];
            $user->email = $data['email'];
            if (array_key_exists('customer_id', $data)) {
                $user->customer_id = $data['customer_id'];
            }
            if (array_key_exists('phone', $data)) {
                $user->phone = $data['phone'];
            }

            if ($emailChanged) {
                $user->email_verified_at = null;
            }
            if ($phoneChanged) {
                $user->phone_verified_at = null;
                $user->phone_verification_required = true;
            }
            if ($emailChanged || $phoneChanged) {
                $user->remember_token = \Illuminate\Support\Str::random(60);
            }

            if (! empty($data['password'])) {
                $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
            }

            $user->save();
            if ($rolesProvided) {
                $user->syncRoles(array_map('intval', $data['roles'] ?? []));
            }
            if ($permissionsProvided) {
                $user->syncPermissions(array_map('intval', $data['permissions'] ?? []));
            }

            if ($emailChanged || $phoneChanged) {
                $user->tokens()->delete();

                \Illuminate\Support\Facades\DB::table('phone_verifications')
                    ->where('user_id', $user->getKey())
                    ->delete();
            }
        });

        if ($emailChanged) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
        if ($phoneChanged) {
            app(PhoneVerificationService::class)->sendSafely($user);
        }

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
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => PhoneNumber::normalize($request->input('phone'), $request->input('phone_country_code')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', new \App\Rules\NonDisposableEmail()],
            'phone_country_code' => ['nullable', 'string', \Illuminate\Validation\Rule::in(array_keys(config('phone.countries', [])))],
            'phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/D'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
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
                'phone' => $data['phone'],
                'phone_verification_required' => true,
                'customer_id' => $data['customer_id'] ?? null,
            ]);

            $user->syncRoles(array_map('intval', $data['roles'] ?? []));
            $user->syncPermissions(array_map('intval', $data['permissions'] ?? []));

            return $user;
        });

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);
        }
        app(PhoneVerificationService::class)->sendSafely($user);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم إنشاء المستخدم. يجب تأكيد البريد والهاتف قبل تسجيل الدخول.'
                : 'User created. Email and phone verification are required before sign-in.');
    }


    /**
     * Delete a user while protecting administrator access and linked records.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $message = app()->getLocale() === 'ar'
            ? 'الحذف المباشر من إدارة المستخدمين غير متاح. يجب تقديم طلب حذف الحساب والتحقق من البريد الإلكتروني أولاً.'
            : 'Direct deletion from user management is unavailable. A verified account deletion request is required.';

        return redirect()
            ->route('admin.users.show', $user)
            ->with('error', $message);
    }

}
