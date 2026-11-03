<?php

namespace App\Http\Controllers;

use App\Models\CompanyMailDelivery;
use App\Models\CompanyMailPreference;
use App\Models\CompanyPlatformAudit;
use App\Models\CompanyWorkspace;
use App\Services\Platform\CompanyMailService;
use App\Services\Platform\CompanyMarketplaceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyMailController extends Controller
{
    public function edit(Request $request, CompanyMarketplaceService $market, CompanyMailService $mail)
    {
        $w = $market->actor($request->user(), true);
        $preference = CompanyMailPreference::where('workspace_id', $w->id)->first() ?? new CompanyMailPreference(['enabled' => false, 'offers' => true, 'messages' => true, 'locale' => app()->getLocale()]);
        $counts = CompanyMailDelivery::where('workspace_id', $w->id)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return response()->view('company.mail.settings', compact('preference', 'counts') + ['ready' => $mail->ready()])->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, CompanyMarketplaceService $market)
    {
        $market->actor($request->user(), true);
        $data = $request->validate(['enabled' => ['required', 'boolean'], 'offers' => ['required', 'boolean'], 'messages' => ['required', 'boolean'], 'locale' => ['required', 'in:ar,en'], 'workspace_id' => ['prohibited']]);
        DB::transaction(function () use ($request, $market, $data) {
            $w = $market->actor($request->user(), true);
            CompanyWorkspace::whereKey($w->id)->lockForUpdate()->firstOrFail();
            $market->actor($request->user(), true);
            $p = CompanyMailPreference::where('workspace_id', $w->id)->lockForUpdate()->first() ?? new CompanyMailPreference(['workspace_id' => $w->id]);
            if ($data['enabled'] && ! $p->enabled) {
                $p->enabled_at = now();
            }
            $p->fill($data);
            $p->save();
            CompanyPlatformAudit::create(['company_workspace_id' => $w->id, 'actor_id' => $request->user()->id, 'action' => 'company.mail_preferences', 'details' => $data, 'created_at' => now()]);
        }, 3);

        return redirect()->route('company.mail.edit')->with('success',__('company_mail.saved'));
    }
}
