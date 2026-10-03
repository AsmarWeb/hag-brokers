<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        |
        | بعض المستخدمين قد لا يكون لديهم customer_id بعد.
        | لذلك نسمح بأن تكون القيمة null بدون التسبب في خطأ.
        |
        */

        $customerId = $user->customer_id;

        /*
        |--------------------------------------------------------------------------
        | Shipments
        |--------------------------------------------------------------------------
        */

        $shipmentQuery = Shipment::query();

        if ($customerId) {
            $shipmentQuery->where('customer_id', $customerId);
        } else {
            // لا توجد شحنات مرتبطة بهذا المستخدم
            $shipmentQuery->whereRaw('1 = 0');
        }

        $stats = [
            'total_shipments' => (clone $shipmentQuery)->count(),

            'in_transit' => (clone $shipmentQuery)
                ->where('status', 'in_transit')
                ->count(),

            'pending' => (clone $shipmentQuery)
                ->where('status', 'pending')
                ->count(),

            'delivered' => (clone $shipmentQuery)
                ->where('status', 'delivered')
                ->count(),
        ];

        $shipments = (clone $shipmentQuery)
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Public Opportunities
        |--------------------------------------------------------------------------
        |
        | هذه هي الفرص التي تظهر للمستخدم في Dashboard.
        |
        */

        $opportunities = Opportunity::query()
            ->with([
                'media',
                'originPort',
                'destinationPort',
            ])
            ->where('visibility', 'public')
            ->where('verification_status', 'verified')
            ->whereNotNull('published_at')
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('published_at')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | My Opportunities
        |--------------------------------------------------------------------------
        |
        | فرص المستخدم نفسه.
        |
        */

        $myOpportunities = collect();

        if ($customerId) {
            $myOpportunities = Opportunity::query()
                ->with('media')
                ->where('customer_id', $customerId)
                ->latest()
                ->limit(6)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Quotations
        |--------------------------------------------------------------------------
        |
        | نتحقق أولاً من وجود Model.
        |
        */

        $quotations = collect();

        if (class_exists(Quotation::class) && $customerId) {
            try {
                $quotations = Quotation::query()
                    ->where('customer_id', $customerId)
                    ->latest()
                    ->limit(5)
                    ->get();
            } catch (\Throwable $e) {
                /*
                 * إذا كان جدول quotations أو customer_id
                 * غير موجود بالشكل المتوقع، لا نوقف Dashboard.
                 */
                $quotations = collect();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        |
        | تم تعطيل قراءة Laravel notifications مؤقتاً لأن جدول
        | notifications الحالي لا يستخدم البنية القياسية:
        |
        | notifiable_type
        | notifiable_id
        |
        | يمكننا ربطه لاحقاً بعد معرفة تصميم جدول notifications.
        |
        */

        $notifications = collect();

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboard', [
            'stats' => $stats,

            'shipments' => $shipments,

            'opportunities' => $opportunities,

            'myOpportunities' => $myOpportunities,

            'quotations' => $quotations,

            'notifications' => $notifications,
        ]);
    }
}
