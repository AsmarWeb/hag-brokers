<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\Task;
use App\Models\Vessel;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        /*
        |--------------------------------------------------------------------------
        | Main Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            // CRM
            'customers' => Customer::count(),
            'leads' => Lead::count(),
            'opportunities' => Opportunity::count(),
            'tasks' => Task::count(),
            'activities' => Activity::count(),

            // Sales
            'total_quotations' => Quotation::count(),
            'pending_quotations' => Quotation::where(
                'status',
                'pending'
            )->count(),

            // Shipping Operations
            'total_shipments' => Shipment::count(),

            'in_transit' => Shipment::where(
                'status',
                'in_transit'
            )->count(),

            'delivered' => Shipment::where(
                'status',
                'delivered'
            )->count(),

            'pending_shipments' => Shipment::where(
                'status',
                'pending'
            )->count(),

            // Network
            'carriers' => Carrier::count(),
            'vessels' => Vessel::count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Lead Pipeline
        |--------------------------------------------------------------------------
        */

        $leadStats = [
            'new' => Lead::where('status', 'new')->count(),

            'contacted' => Lead::where(
                'status',
                'contacted'
            )->count(),

            'qualified' => Lead::where(
                'status',
                'qualified'
            )->count(),

            'proposal' => Lead::where(
                'status',
                'proposal'
            )->count(),

            'won' => Lead::where(
                'status',
                'won'
            )->count(),

            'lost' => Lead::where(
                'status',
                'lost'
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Opportunity Pipeline
        |--------------------------------------------------------------------------
        */

        $opportunityStats = [
            'new' => Opportunity::where(
                'stage',
                'new'
            )->count(),

            'qualified' => Opportunity::where(
                'stage',
                'qualified'
            )->count(),

            'quotation' => Opportunity::where(
                'stage',
                'quotation'
            )->count(),

            'negotiation' => Opportunity::where(
                'stage',
                'negotiation'
            )->count(),

            'won' => Opportunity::where(
                'stage',
                'won'
            )->count(),

            'lost' => Opportunity::where(
                'stage',
                'lost'
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Shipment Pipeline
        |--------------------------------------------------------------------------
        */

        $shipmentStats = [
            'pending' => Shipment::where(
                'status',
                'pending'
            )->count(),

            'booked' => Shipment::where(
                'status',
                'booked'
            )->count(),

            'confirmed' => Shipment::where(
                'status',
                'confirmed'
            )->count(),

            'in_transit' => Shipment::where(
                'status',
                'in_transit'
            )->count(),

            'arrived' => Shipment::where(
                'status',
                'arrived'
            )->count(),

            'delivered' => Shipment::where(
                'status',
                'delivered'
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Recent Leads
        |--------------------------------------------------------------------------
        */

        $recentLeads = Lead::with([
            'assignedTo',
        ])
            ->latest()
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Opportunities
        |--------------------------------------------------------------------------
        */

        $recentOpportunities = Opportunity::with([
            'customer',
            'contact',
            'assignedTo',
            'originPort',
            'destinationPort',
        ])
            ->latest()
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Upcoming Tasks
        |--------------------------------------------------------------------------
        */

        $upcomingTasks = Task::with([
            'customer',
            'lead',
            'assignedTo',
        ])
            ->whereIn('status', [
                'pending',
                'in_progress',
            ])
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Activities
        |--------------------------------------------------------------------------
        */

        $recentActivities = Activity::with([
            'customer',
            'lead',
            'user',
        ])
            ->latest()
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Shipments
        |--------------------------------------------------------------------------
        */

        $recentShipments = Shipment::with([
            'customer',
            'originPort',
            'destinationPort',
            'carrier',
            'vessel',
        ])
            ->latest()
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Recent Quotations
        |--------------------------------------------------------------------------
        */

        $recentQuotations = Quotation::with([
            'customer',
            'originPort',
            'destinationPort',
        ])
            ->latest()
            ->limit(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('admin.dashboard', [
            'stats' => $stats,

            'leadStats' => $leadStats,

            'opportunityStats' => $opportunityStats,

            'shipmentStats' => $shipmentStats,

            'recentLeads' => $recentLeads,

            'recentOpportunities' => $recentOpportunities,

            'upcomingTasks' => $upcomingTasks,

            'recentActivities' => $recentActivities,

            'recentShipments' => $recentShipments,

            'recentQuotations' => $recentQuotations,
        ]);
    }
}
