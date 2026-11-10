<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Freight\SailingCatalog;
use App\Services\Freight\SailingImport;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class AdminSailingImportController extends Controller
{
    public function show()
    {
        return view('admin.freight-sailings.import');
    }

    public function template()
    {
        return response(implode(',', SailingImport::HEADERS)."\r\n", 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="freight-sailings.csv"',
        ]);
    }

    public function preview(Request $request, SailingImport $import, SailingCatalog $catalog)
    {
        $request->session()->forget('sailing_import');
        $request->validate(['schedule' => ['required', 'file', 'extensions:csv', 'max:128']]);

        $rows = $import->read($request->file('schedule')->getRealPath());
        $plan = $import->plan($rows, $catalog);
        $token = (string) Str::uuid();
        $request->session()->put('sailing_import', [
            'token' => $token,
            'user' => $request->user()->id,
            'expires' => now()->addMinutes(15)->timestamp,
            'rows' => $rows,
            'revision' => $import->revision($plan),
        ]);

        return response()->view('admin.freight-sailings.import', compact('plan', 'token'))
            ->header('Cache-Control', 'no-store');
    }

    public function publish(Request $request, SailingImport $import, SailingCatalog $catalog)
    {
        $data = $request->validate(['token' => ['required', 'uuid']]);
        $preview = $request->session()->get('sailing_import');
        if (! $preview || $preview['user'] !== $request->user()->id || $preview['expires'] <= now()->timestamp
            || ! hash_equals($preview['token'], $data['token'])) {
            $this->stale();
        }

        try {
            DB::transaction(function () use ($import, $catalog, $preview) {
                $plan = $import->plan($preview['rows'], $catalog, true);
                if (! hash_equals($preview['revision'], $import->revision($plan))) {
                    $this->stale();
                }
                $import->apply($plan);
            }, 3);
        } catch (UniqueConstraintViolationException) {
            $this->stale();
        }

        $request->session()->forget('sailing_import');

        return redirect()->route('admin.freight-sailings.index')->with('success', __('sailing_import.published'));
    }

    private function stale(): never
    {
        throw ValidationException::withMessages(['schedule' => __('sailing_import.stale')])
            ->redirectTo(route('admin.freight-sailings.import'));
    }
}
