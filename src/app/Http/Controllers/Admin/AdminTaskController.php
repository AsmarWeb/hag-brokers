<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTaskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Task::with([
            'customer',
            'lead',
            'assignedTo',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return view('admin.tasks.create', [
            'customers' => Customer::where('is_active', true)
                ->orderBy('company_name')
                ->get(),
            'leads' => Lead::latest()->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'completed_at' => ['nullable', 'date'],
        ]);

        Task::create($validated);

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task): View
    {
        $task->load([
            'customer',
            'lead',
            'assignedTo',
        ]);

        return view('admin.tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        return view('admin.tasks.edit', [
            'task' => $task,
            'customers' => Customer::where('is_active', true)
                ->orderBy('company_name')
                ->get(),
            'leads' => Lead::latest()->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'completed_at' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
