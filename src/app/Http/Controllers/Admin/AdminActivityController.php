<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminActivityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Activity::with([
            'customer',
            'lead',
            'user',
        ]);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $activities = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.activities.index', compact('activities'));
    }

    public function create(): View
    {
        return view('admin.activities.create', [
            'customers' => Customer::where('is_active', true)
                ->orderBy('company_name')
                ->get(),

            'leads' => Lead::latest()
                ->get(),

            'users' => User::orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        Activity::create($validated);

        return redirect()
            ->route('admin.activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function show(Activity $activity): View
    {
        $activity->load([
            'customer',
            'lead',
            'user',
        ]);

        return view('admin.activities.show', compact('activity'));
    }

    public function edit(Activity $activity): View
    {
        return view('admin.activities.edit', [
            'activity' => $activity,
            'customers' => Customer::where('is_active', true)
                ->orderBy('company_name')
                ->get(),
            'leads' => Lead::latest()->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $activity->update($validated);

        return redirect()
            ->route('admin.activities.show', $activity)
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()
            ->route('admin.activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
}
