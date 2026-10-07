<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Make sure the shipment belongs to the authenticated customer.
     */
    private function own(
        Request $request,
        Shipment $shipment
    ): void {
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
            ->with('uploadedBy')
            ->latest()
            ->get();

        return view(
            'shipments.documents.index',
            [
                'shipment' => $shipment,
                'documents' => $documents,
            ]
        );
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
            'documents/shipment',
            'local'
        );

        $document = null;

        try {
            DB::transaction(function () use (
                &$document,
                $request,
                $data,
                $file,
                $path,
                $shipment
            ) {
                /*
                |--------------------------------------------------------------------------
                | Create main document
                |--------------------------------------------------------------------------
                */

                $document = Document::create([
                    'documentable_type' =>
                    $shipment->getMorphClass(),

                    'documentable_id' =>
                    $shipment->id,

                    'type' =>
                    $data['type'],

                    'name' =>
                    $file->getClientOriginalName(),

                    'original_name' =>
                    $file->getClientOriginalName(),

                    'file_path' =>
                    $path,

                    'disk' =>
                    'local',

                    'mime_type' =>
                    $file->getMimeType(),

                    'file_size' =>
                    $file->getSize(),

                    'status' =>
                    'active',

                    'version' =>
                    1,

                    'uploaded_by' =>
                    $request->user()->id,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Create first version
                |--------------------------------------------------------------------------
                */

                DocumentVersion::create([
                    'document_id' =>
                    $document->id,

                    'version' =>
                    1,

                    'file_path' =>
                    $path,

                    'original_name' =>
                    $file->getClientOriginalName(),

                    'mime_type' =>
                    $file->getMimeType(),

                    'file_size' =>
                    $file->getSize(),

                    'uploaded_by' =>
                    $request->user()->id,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            throw $e;
        }

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

    /**
     * Download a shipment document.
     */
    public function download(
        Request $request,
        Shipment $shipment,
        Document $document
    ) {
        $this->own($request, $shipment);

        abort_unless(
            $document->documentable_type === $shipment->getMorphClass()
                && $document->documentable_id === $shipment->id,
            404
        );

        $disk = Storage::disk(
            $document->disk
        );

        abort_unless(
            $disk->exists(
                $document->file_path
            ),
            404
        );

        return $disk->download(
            $document->file_path,
            $document->original_name
                ?? $document->name
        );
    }

    /**
     * Preview a shipment document.
     */
    public function preview(
        Request $request,
        Shipment $shipment,
        Document $document
    ) {
        $this->own($request, $shipment);

        abort_unless(
            $document->documentable_type === $shipment->getMorphClass()
                && $document->documentable_id === $shipment->id,
            404
        );

        $disk = Storage::disk(
            $document->disk
        );

        abort_unless(
            $disk->exists(
                $document->file_path
            ),
            404
        );

        return $disk->response(
            $document->file_path,
            $document->original_name
                ?? $document->name,
            [
                'Content-Type' =>
                $document->mime_type
                    ?: 'application/octet-stream',

                'Content-Disposition' =>
                'inline',
            ]
        );
    }
}
