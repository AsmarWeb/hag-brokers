<?php

namespace App\Http\Controllers;

use App\Models\CompanyBillingAccount;
use App\Models\CompanyBillingReceipt;
use App\Models\PlatformPlan;
use App\Services\Platform\CompanyBillingService;
use App\Services\Platform\CompanyWorkspaceService;
use App\Services\Platform\StripeTestGateway;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class CompanyBillingController extends Controller
{
    public function show(Request $request, CompanyBillingService $service, StripeTestGateway $gateway)
    {
        $w = $service->owner($request->user());
        $account = CompanyBillingAccount::where('company_workspace_id', $w->id)->first();

        if (config('platform_billing.free_launch', true) && ! ($gateway->live() && $account?->stripe_subscription_id)) {
            $free = PlatformPlan::where('code', 'free')->where('is_available', true)->firstOrFail();

            return response()->view('company.billing-free', ['free' => $free, 'effectivePlan' => app(CompanyWorkspaceService::class)->plan($w), 'upcoming' => PlatformPlan::whereIn('code', ['business', 'professional'])->orderBy('monthly_amount_minor')->get()])->header('Cache-Control', 'private, no-store');
        }

        return view('company.billing', ['account' => $account, 'ready' => $gateway->ready(), 'plans' => PlatformPlan::where('is_available', true)->where('monthly_amount_minor', '>', 0)->get(),
            'receipts' => $account ? CompanyBillingReceipt::where('company_billing_account_id', $account->id)->latest('id')->paginate(10) : null]);
    }

    public function checkout(Request $request, CompanyBillingService $service)
    {
        $d = $request->validate(['plan' => ['required', 'in:business,professional']]);
        try {
            $url = $service->checkout($request->user(), $d['plan'], $request->boolean('confirmed'));
        } catch (\RuntimeException $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }report($e);

            return back()->withErrors(['billing' => __('billing.api_failed')]);
        }

        return redirect()->away($url);
    }

    public function refresh(Request $request, CompanyBillingService $service)
    {
        $a = $service->account($service->owner($request->user()));
        try {
            $service->sync($a);
        } catch (\RuntimeException $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }report($e);

            return back()->withErrors(['billing' => __('billing.api_failed')]);
        }

        return redirect()->route('company.billing')->with('success', __('billing.synced'));
    }

    public function cancel(Request $request, CompanyBillingService $service)
    {
        $d = $request->validate(['cancel' => ['required', 'boolean']]);
        try {
            $service->cancellation($request->user(), (bool) $d['cancel']);
        } catch (\RuntimeException $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }report($e);

            return back()->withErrors(['billing' => __('billing.api_failed')]);
        }

        return redirect()->route('company.billing')->with('success', __('billing.synced'));
    }

    public function portal(Request $request, CompanyBillingService $service)
    {
        try {
            $url = $service->portal($request->user());
        } catch (\RuntimeException $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }report($e);

            return back()->withErrors(['billing' => __('billing.api_failed')]);
        }

        return redirect()->away($url);
    }
}
