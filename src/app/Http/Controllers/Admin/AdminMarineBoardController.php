<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarineBoardInquiry;
use App\Models\MarineCargoListing;
use App\Models\MarineVesselListing;
use App\Rules\NoPublicContactDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminMarineBoardController extends Controller
{
    public function index(): View
    {
        return view('admin.maritime-board.index', [
            'cargoes' => MarineCargoListing::with(['loadingPort', 'dischargingPort', 'reviewer'])->latest()->paginate(15, ['*'], 'cargo_page'),
            'vessels' => MarineVesselListing::with(['currentPort', 'reviewer'])->latest()->paginate(15, ['*'], 'vessel_page'),
            'inquiries' => MarineBoardInquiry::with(['cargoListing', 'vesselListing'])->latest()->paginate(15, ['*'], 'inquiry_page'),
        ]);
    }

    public function review(Request $request, string $kind, int $listing): RedirectResponse
    {
        $rules = [
            'status' => ['required', 'in:published,rejected,closed'],
            'review_notes' => ['nullable', 'string', 'max:5000'],
        ];

        if ($kind === 'cargo' && $request->input('status') === 'published') {
            $rules['public_description'] = ['required', 'string', 'min:20', 'max:1000', new NoPublicContactDetails()];
        } elseif ($kind === 'cargo') {
            $rules['public_description'] = ['nullable', 'string', 'min:20', 'max:1000', new NoPublicContactDetails()];
        }

        $data = $request->validate($rules);

        if ($kind === 'cargo') {
            $record = MarineCargoListing::findOrFail($listing);
        } elseif ($kind === 'vessel') {
            $record = MarineVesselListing::findOrFail($listing);
        } else {
            abort(404);
        }

        if ($kind === 'cargo') {
            $record->public_description = $data['public_description'] ?? $record->public_description;
        }

        $record->forceFill([
            'status' => $data['status'],
            'review_notes' => $data['review_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'published_at' => $data['status'] === 'published' ? ($record->published_at ?? now()) : null,
            'closed_at' => $data['status'] === 'closed' ? now() : null,
        ])->save();

        return back()->with('status', __('maritime_board.review_saved'));
    }

    public function updateInquiry(Request $request, MarineBoardInquiry $inquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:new,contacted,closed']]);
        $inquiry->update(['status' => $data['status']]);

        return back()->with('status', __('maritime_board.inquiry_updated'));
    }
}
