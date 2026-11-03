<?php

namespace App\Http\Controllers;

use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\OpportunityAssistant;
use App\Services\Platform\OpportunityCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

final class OpportunityAssistantController extends Controller
{
    public function __invoke(Request $request, CompanyMarketplaceService $market, OpportunityAssistant $assistant)
    {
        $workspace = $market->actor($request->user(), true);
        $data = $request->validate([
            'mode' => ['required', 'in:guide,ai'], 'consent' => ['exclude_unless:mode,ai', 'required', 'accepted'],
            'language' => ['required', 'in:ar,en'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', $request->input('mode') === 'ai' ? 'max:6000' : 'max:20000'],
            'opportunity_type' => ['required', Rule::in(OpportunityCatalog::TYPES)],
            'category' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'],
        ] + OpportunityCatalog::detailRules($request->input('category')));
        app()->setLocale($data['language']);
        if ($data['mode'] === 'guide') {
            return response()->json($assistant->guide($data));
        }
        if (! $assistant->ready()) {
            return response()->json(['message' => __('trade.ai_unavailable')], 503);
        }
        $day = now('Asia/Dubai')->format('Y-m-d');
        foreach (['company:'.$workspace->id => 'company_daily_limit', 'platform' => 'platform_daily_limit'] as $scope => $limit) {
            if (RateLimiter::hit('opportunity-ai:'.$scope.':'.$day, 86400) > max(0, (int) config('opportunity_assistant.'.$limit))) {
                return response()->json(['message' => __('trade.ai_limit')], 429);
            }
        }
        try {
            return response()->json($assistant->suggest($data, $data['language']));
        } catch (\RuntimeException) {
            return response()->json(['message' => __('trade.ai_unavailable')], 503);
        }
    }
}
