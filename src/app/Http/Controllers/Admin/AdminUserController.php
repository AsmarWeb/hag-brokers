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
}
