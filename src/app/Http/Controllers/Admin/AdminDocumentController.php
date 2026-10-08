<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $documents = Document::query()
            ->with([
                'shipment',
                'uploadedBy',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {

                    // Search document fields
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")

                        // Search shipment fields
                        ->orWhereHas('shipment', function ($shipment) use ($search) {
                            $shipment
                                ->where('shipment_no', 'like', "%{$search}%")
                                ->orWhere('booking_no', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Document Types
        |--------------------------------------------------------------------------
        |
        | The admin documents view expects $types.
        | We use the existing "type" column from the documents table.
        |
        */
        $types = Document::query()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('admin.documents.index', [
            'documents' => $documents,
            'types' => $types,
        ]);
    }
}
