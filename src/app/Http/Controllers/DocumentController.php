<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Make sure the shipment belongs to the authenticated customer.
     */
    private function own(Request $request, Shipment $shipment): void
    {
        abort_unless(
            $shipment->customer_id === $request->user()->customer_id,
            403
        );
    }

    /**
     * Display all documents for a shipment.
     */
    public function index(
        Request $request,
        Shipment $shipment
    ): View {
        $this->own($request, $shipment);

        $documents = $shipment
            ->documents()
            ->latest()
            ->get();

        return view('shipments.documents.index', [
            'shipment' => $shipment,
            'documents' => $documents,
        ]);
    }

    /**
     * Upload a new document for a shipment.
     */
    public function store(
        Request $request,
        Shipment $shipment
    ): RedirectResponse {
        $this->own($request, $shipment);

        $data = $request->validate([
            'type' => [
                'required',
                'string',
                'max:100',
            ],

            'file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        $file = $request->file('file');

        $path = $file->store(
            'shipments/' . $shipment->id,
            'local'
        );

        $document = Document::create([
            'shipment_id' => $shipment->id,
            'type' => $data['type'],
            'name' => $file->getClientOriginalName(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route(
                'shipments.documents.index',
                $shipment
            )
            ->with(
                'success',
                'Document uploaded successfully.'
            );
    }
}
