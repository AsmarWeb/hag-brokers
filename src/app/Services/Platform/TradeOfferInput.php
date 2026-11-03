<?php

namespace App\Services\Platform;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class TradeOfferInput
{
    public function submission(Request $request): array
    {
        $data = $request->validate([
            'request_key' => ['required', 'uuid'], 'quantity' => ['required', 'regex:/^\d+(?:\.\d{1,3})?$/D', 'decimal:0,3', 'gt:0', 'max:999999999.999'],
            'unit' => ['required', Rule::in(OpportunityCatalog::UNITS)], 'unit_price' => ['required', 'regex:/^\d+(?:\.\d{1,2})?$/D', 'decimal:0,2', 'gt:0', 'max:999999999.99'],
            'currency' => ['required', Rule::in(OpportunityCatalog::CURRENCIES)], 'tax_basis' => ['required', 'in:exclusive,inclusive,not_specified'],
            'delivery_days' => ['required', 'integer', 'between:0,3650'], 'valid_until' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString(), 'before_or_equal:'.now('Asia/Dubai')->addDays(90)->toDateString()],
            'payment_terms' => ['required', 'string', 'min:10', 'max:1000'], 'notes' => ['nullable', 'string', 'max:3000'],
            'attachments' => ['nullable', 'array', 'max:3'], 'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'confirmed' => ['required', 'accepted'],
        ]);
        $uploads = $request->file('attachments', []);
        if (collect($uploads)->sum(fn ($file) => $file->getSize()) > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['attachments' => __('rfq.files_help')]);
        }
        unset($data['attachments'],$data['confirmed']);

        return [$data, $uploads];
    }
}
