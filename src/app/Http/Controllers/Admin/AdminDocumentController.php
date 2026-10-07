<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\DocumentVersion;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Services\DocumentPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDocumentController extends Controller
{
    /**
     * Document Center
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $type = $request->input('type');

        $status = $request->input('status');

        $documentableType = $request->input(
            'documentable_type'
        );

        $from = $request->input('from');

        $to = $request->input('to');

        /*
        |--------------------------------------------------------------------------
        | Documentable map
        |--------------------------------------------------------------------------
        */

        $documentableMap = [
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        ];

        /*
        |--------------------------------------------------------------------------
        | Documents query
        |--------------------------------------------------------------------------
        */

        $query = Document::query()
            ->with([
                'documentable',
                'uploadedBy',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {

                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )

                            ->orWhere(
                                'type',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'original_name',
                                'like',
                                "%{$search}%"
                            )

                            /*
                        |--------------------------------------------------------------------------
                        | Customer search
                        |--------------------------------------------------------------------------
                        */

                            ->orWhereHasMorph(
                                'documentable',
                                [Customer::class],
                                function ($q) use ($search) {

                                    $q->where(
                                        'company_name',
                                        'like',
                                        "%{$search}%"
                                    )

                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        )

                                        ->orWhere(
                                            'contact_person',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            )

                            /*
                        |--------------------------------------------------------------------------
                        | Shipment search
                        |--------------------------------------------------------------------------
                        */

                            ->orWhereHasMorph(
                                'documentable',
                                [Shipment::class],
                                function ($q) use ($search) {

                                    $q->where(
                                        'shipment_no',
                                        'like',
                                        "%{$search}%"
                                    )

                                        ->orWhere(
                                            'booking_no',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            )

                            /*
                        |--------------------------------------------------------------------------
                        | Quotation search
                        |--------------------------------------------------------------------------
                        */

                            ->orWhereHasMorph(
                                'documentable',
                                [Quotation::class],
                                function ($q) use ($search) {

                                    $q->where(
                                        'reference_no',
                                        'like',
                                        "%{$search}%"
                                    );
                                }
                            );
                    });
                }
            )

            ->when(
                $type,
                fn($q) => $q->where(
                    'type',
                    $type
                )
            )

            ->when(
                $status,
                fn($q) => $q->where(
                    'status',
                    $status
                )
            )

            ->when(
                isset($documentableMap[$documentableType]),
                fn($q) => $q->where(
                    'documentable_type',
                    $documentableMap[$documentableType]
                )
            )

            ->when(
                $from,
                fn($q) => $q->whereDate(
                    'created_at',
                    '>=',
                    $from
                )
            )

            ->when(
                $to,
                fn($q) => $q->whereDate(
                    'created_at',
                    '<=',
                    $to
                )
            )

            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $documents = $query
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalDocuments = Document::count();

        $activeDocuments = Document::where(
            'status',
            'active'
        )->count();

        $pdfDocuments = Document::where(
            'mime_type',
            'application/pdf'
        )->count();

        $recentDocuments = Document::where(
            'created_at',
            '>=',
            now()->subDays(30)
        )->count();

        $activeTemplates = DocumentTemplate::where(
            'is_active',
            true
        )->count();

        /*
        |--------------------------------------------------------------------------
        | Available document types
        |--------------------------------------------------------------------------
        */

        $types = Document::query()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'admin.documents.index',
            compact(
                'documents',
                'totalDocuments',
                'activeDocuments',
                'pdfDocuments',
                'recentDocuments',
                'activeTemplates',
                'types'
            )
        );
    }

    /**
     * Show upload document form.
     */
    public function create(): View
    {
        return view(
            'admin.documents.create',
            [
                'customers' => Customer::query()
                    ->orderBy('company_name')
                    ->get([
                        'id',
                        'company_name',
                        'contact_person',
                        'email',
                    ]),

                'shipments' => Shipment::query()
                    ->latest()
                    ->get([
                        'id',
                        'shipment_no',
                        'booking_no',
                        'customer_id',
                    ]),

                'quotations' => Quotation::query()
                    ->latest()
                    ->get([
                        'id',
                        'reference_no',
                        'customer_id',
                    ]),

                'types' => Document::query()
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->distinct()
                    ->orderBy('type')
                    ->pluck('type'),
            ]
        );
    }

    /**
     * Store uploaded document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                'in:active,draft,archived',
            ],

            'documentable_type' => [
                'required',
                'in:customer,shipment,quotation',
            ],

            'documentable_id' => [
                'required',
                'integer',
            ],

            'file' => [
                'required',
                'file',
                'max:20480',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Entity map
        |--------------------------------------------------------------------------
        */

        $map = [
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        ];

        $modelClass = $map[$validated['documentable_type']];

        $documentable = $modelClass::findOrFail(
            $validated['documentable_id']
        );

        /*
        |--------------------------------------------------------------------------
        | Store file
        |--------------------------------------------------------------------------
        */

        $file = $request->file('file');

        $path = $file->store(
            'documents/' .
                $validated['documentable_type'],
            'local'
        );

        $document = null;

        try {

            DB::transaction(
                function () use (
                    &$document,
                    $validated,
                    $documentable,
                    $file,
                    $path
                ) {

                    $document = Document::create([
                        'documentable_type' =>
                        $documentable->getMorphClass(),

                        'documentable_id' =>
                        $documentable->getKey(),

                        'type' =>
                        $validated['type'],

                        'name' =>
                        $validated['name'],

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
                        $validated['status']
                            ?? 'active',

                        'version' =>
                        1,

                        'uploaded_by' =>
                        auth()->id(),
                    ]);

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
                        auth()->id(),
                    ]);
                }
            );
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Remove uploaded file if database transaction fails
            |--------------------------------------------------------------------------
            */

            Storage::disk('local')->delete($path);

            throw $e;
        }

        return redirect()
            ->route('admin.documents.index')
            ->with(
                'success',
                'Document uploaded successfully.'
            );
    }

    /**
     * Edit document.
     */
    public function edit(
        Document $document
    ): View {
        $document->load([
            'documentable',
            'uploadedBy',
            'versions.uploadedBy',
        ]);

        return view(
            'admin.documents.edit',
            [
                'document' => $document,

                'customers' => Customer::query()
                    ->orderBy('company_name')
                    ->get([
                        'id',
                        'company_name',
                        'contact_person',
                        'email',
                    ]),

                'shipments' => Shipment::query()
                    ->latest()
                    ->get([
                        'id',
                        'shipment_no',
                        'booking_no',
                        'customer_id',
                    ]),

                'quotations' => Quotation::query()
                    ->latest()
                    ->get([
                        'id',
                        'reference_no',
                        'customer_id',
                    ]),
            ]
        );
    }

    /**
     * Update document.
     *
     * Replacing the file creates a new version.
     */
    public function update(
        Request $request,
        Document $document
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                'in:active,draft,archived',
            ],

            'documentable_type' => [
                'required',
                'in:customer,shipment,quotation',
            ],

            'documentable_id' => [
                'required',
                'integer',
            ],

            'file' => [
                'nullable',
                'file',
                'max:20480',
            ],
        ]);

        $map = [
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        ];

        $modelClass = $map[$validated['documentable_type']];

        $documentable = $modelClass::findOrFail(
            $validated['documentable_id']
        );

        $newPath = null;

        try {

            DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $document,
                    $documentable,
                    &$newPath
                ) {

                    $updateData = [
                        'name' =>
                        $validated['name'],

                        'type' =>
                        $validated['type'],

                        'status' =>
                        $validated['status']
                            ?? 'active',

                        'documentable_type' =>
                        $documentable->getMorphClass(),

                        'documentable_id' =>
                        $documentable->getKey(),
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | Replace file = new version
                    |--------------------------------------------------------------------------
                    */

                    if ($request->hasFile('file')) {

                        $file = $request->file('file');

                        $nextVersion =
                            ((int) $document->version) + 1;

                        $newPath = $file->store(
                            'documents/' .
                                $validated['documentable_type'],
                            'local'
                        );

                        $updateData['file_path'] =
                            $newPath;

                        $updateData['original_name'] =
                            $file->getClientOriginalName();

                        $updateData['disk'] =
                            'local';

                        $updateData['mime_type'] =
                            $file->getMimeType();

                        $updateData['file_size'] =
                            $file->getSize();

                        $updateData['version'] =
                            $nextVersion;

                        DocumentVersion::create([
                            'document_id' =>
                            $document->id,

                            'version' =>
                            $nextVersion,

                            'file_path' =>
                            $newPath,

                            'original_name' =>
                            $file->getClientOriginalName(),

                            'mime_type' =>
                            $file->getMimeType(),

                            'file_size' =>
                            $file->getSize(),

                            'uploaded_by' =>
                            auth()->id(),
                        ]);
                    }

                    $document->update(
                        $updateData
                    );
                }
            );
        } catch (\Throwable $e) {

            if ($newPath) {
                Storage::disk('local')
                    ->delete($newPath);
            }

            throw $e;
        }

        return redirect()
            ->route(
                'admin.documents.edit',
                $document
            )
            ->with(
                'success',
                'Document updated successfully.'
            );
    }

    /**
     * Show document versions.
     */
    public function versions(
        Document $document
    ): View {
        $document->load([
            'documentable',
            'uploadedBy',
            'versions.uploadedBy',
        ]);

        return view(
            'admin.documents.versions',
            compact('document')
        );
    }

    /**
     * Download current document.
     */
    public function download(
        Document $document
    ) {
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
     * Preview current document.
     */
    public function preview(
        Document $document
    ) {
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

    /**
     * Delete document and all versions.
     */
    public function destroy(
        Document $document
    ) {
        $disk = Storage::disk(
            $document->disk
        );

        /*
        |--------------------------------------------------------------------------
        | Delete current file
        |--------------------------------------------------------------------------
        */

        if (
            $disk->exists(
                $document->file_path
            )
        ) {
            $disk->delete(
                $document->file_path
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete version files
        |--------------------------------------------------------------------------
        */

        $document->load('versions');

        foreach ($document->versions as $version) {

            if (
                $disk->exists(
                    $version->file_path
                )
            ) {
                $disk->delete(
                    $version->file_path
                );
            }
        }

        $document->delete();

        return redirect()
            ->route(
                'admin.documents.index'
            )
            ->with(
                'success',
                'Document deleted successfully.'
            );
    }

    /**
     * Show Generate PDF page.
     */
    public function pdfCreate(
        Request $request
    ): View {

        $selectedTemplate = null;

        if ($request->filled('template')) {

            $selectedTemplate =
                DocumentTemplate::query()
                ->where(
                    'is_active',
                    true
                )
                ->find(
                    $request->integer(
                        'template'
                    )
                );
        }

        return view(
            'admin.documents.pdf-create',
            [
                'templates' =>
                DocumentTemplate::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy('name')
                    ->get(),

                'selectedTemplate' =>
                $selectedTemplate,

                'customers' =>
                Customer::query()
                    ->orderBy('company_name')
                    ->get([
                        'id',
                        'company_name',
                        'contact_person',
                        'email',
                    ]),

                'shipments' =>
                Shipment::query()
                    ->with([
                        'customer',
                        'originPort',
                        'destinationPort',
                        'vessel',
                    ])
                    ->latest()
                    ->get(),

                'quotations' =>
                Quotation::query()
                    ->with([
                        'customer',
                        'originPort',
                        'destinationPort',
                    ])
                    ->latest()
                    ->get(),
            ]
        );
    }

    /**
     * Preview PDF without saving it.
     */
    public function previewPdf(
        Request $request,
        DocumentPdfService $pdfService
    ) {
        $validated = $request->validate([
            'template_id' => [
                'required',
                'integer',
                'exists:document_templates,id',
            ],

            'document_type' => [
                'required',
                'string',
                'max:100',
            ],

            'entity_type' => [
                'required',
                'in:customer,shipment,quotation',
            ],

            'entity_id' => [
                'required',
                'integer',
            ],

            'orientation' => [
                'nullable',
                'in:portrait,landscape',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Template
        |--------------------------------------------------------------------------
        */

        $template = DocumentTemplate::query()
            ->where(
                'is_active',
                true
            )
            ->findOrFail(
                $validated['template_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = CompanySetting::firstOrCreate([]);

        /*
        |--------------------------------------------------------------------------
        | Entity
        |--------------------------------------------------------------------------
        */

        $entity = $this->resolveEntity(
            $validated['entity_type'],
            $validated['entity_id']
        );

        /*
        |--------------------------------------------------------------------------
        | Related records
        |--------------------------------------------------------------------------
        */

        $customer = null;

        $shipment = null;

        $quotation = null;

        if ($entity instanceof Customer) {

            $customer = $entity;
        }

        if ($entity instanceof Shipment) {

            $shipment = $entity;

            $customer = $shipment->customer;
        }

        if ($entity instanceof Quotation) {

            $quotation = $entity;

            $customer = $quotation->customer;
        }

        /*
        |--------------------------------------------------------------------------
        | Build data
        |--------------------------------------------------------------------------
        */

        $data = $this->buildPdfData(
            $company,
            $customer,
            $shipment,
            $quotation
        );

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = $pdfService->generate(
            $template,
            $data
        );

        $pdf->setPaper(
            'a4',
            $validated['orientation']
                ?? 'portrait'
        );

        return $pdf->stream(
            'preview-' .
                $template->slug .
                '.pdf'
        );
    }

    /**
     * Generate PDF and save it as a Document.
     */
    public function generatePdf(
        Request $request,
        DocumentPdfService $pdfService
    ) {
        $validated = $request->validate([
            'template_id' => [
                'required',
                'integer',
                'exists:document_templates,id',
            ],

            'document_type' => [
                'required',
                'string',
                'max:100',
            ],

            'entity_type' => [
                'required',
                'in:customer,shipment,quotation',
            ],

            'entity_id' => [
                'required',
                'integer',
            ],

            'file_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'orientation' => [
                'nullable',
                'in:portrait,landscape',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Template
        |--------------------------------------------------------------------------
        */

        $template = DocumentTemplate::query()
            ->where(
                'is_active',
                true
            )
            ->findOrFail(
                $validated['template_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Entity
        |--------------------------------------------------------------------------
        */

        $entity = $this->resolveEntity(
            $validated['entity_type'],
            $validated['entity_id']
        );

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = CompanySetting::firstOrCreate([]);

        /*
        |--------------------------------------------------------------------------
        | Related records
        |--------------------------------------------------------------------------
        */

        $customer = null;

        $shipment = null;

        $quotation = null;

        if ($entity instanceof Customer) {

            $customer = $entity;
        }

        if ($entity instanceof Shipment) {

            $shipment = $entity;

            $customer = $shipment->customer;
        }

        if ($entity instanceof Quotation) {

            $quotation = $entity;

            $customer = $quotation->customer;
        }

        /*
        |--------------------------------------------------------------------------
        | Build PDF data
        |--------------------------------------------------------------------------
        */

        $data = $this->buildPdfData(
            $company,
            $customer,
            $shipment,
            $quotation
        );

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = $pdfService->generate(
            $template,
            $data
        );

        $orientation =
            $validated['orientation']
            ?? 'portrait';

        $pdf->setPaper(
            'a4',
            $orientation
        );

        /*
        |--------------------------------------------------------------------------
        | Generate safe filename
        |--------------------------------------------------------------------------
        */

        $baseName =
            $validated['file_name']
            ?: $template->name .
            '-' .
            now()->format(
                'Ymd-His'
            );

        $fileName = Str::slug(
            $baseName
        );

        if (
            !str_ends_with(
                strtolower($fileName),
                '.pdf'
            )
        ) {
            $fileName .= '.pdf';
        }

        /*
        |--------------------------------------------------------------------------
        | Avoid accidental overwrite
        |--------------------------------------------------------------------------
        */

        $path =
            'documents/generated/' .
            $fileName;

        $disk = Storage::disk('local');

        if ($disk->exists($path)) {

            $fileName =
                pathinfo(
                    $fileName,
                    PATHINFO_FILENAME
                )
                . '-'
                . now()->format('His')
                . '.pdf';

            $path =
                'documents/generated/' .
                $fileName;
        }

        /*
        |--------------------------------------------------------------------------
        | Save PDF
        |--------------------------------------------------------------------------
        */

        $disk->put(
            $path,
            $pdf->output()
        );

        $fileSize = $disk->size(
            $path
        );

        $document = null;

        try {

            DB::transaction(
                function () use (
                    &$document,
                    $validated,
                    $entity,
                    $template,
                    $path,
                    $fileName,
                    $fileSize
                ) {

                    $document = Document::create([
                        'documentable_type' =>
                        $entity->getMorphClass(),

                        'documentable_id' =>
                        $entity->getKey(),

                        'type' =>
                        $validated['document_type'],

                        'name' =>
                        pathinfo(
                            $fileName,
                            PATHINFO_FILENAME
                        ),

                        'original_name' =>
                        $fileName,

                        'file_path' =>
                        $path,

                        'disk' =>
                        'local',

                        'mime_type' =>
                        'application/pdf',

                        'file_size' =>
                        $fileSize,

                        'status' =>
                        'active',

                        'version' =>
                        1,

                        'uploaded_by' =>
                        auth()->id(),

                        'metadata' => [
                            'source' =>
                            'pdf_generator',

                            'template_id' =>
                            $template->id,

                            'template_name' =>
                            $template->name,

                            'template_version' =>
                            $template->version,

                            'orientation' =>
                            $validated['orientation']
                                ?? 'portrait',

                            'generated_at' =>
                            now()->toIso8601String(),
                        ],
                    ]);

                    DocumentVersion::create([
                        'document_id' =>
                        $document->id,

                        'version' =>
                        1,

                        'file_path' =>
                        $path,

                        'original_name' =>
                        $fileName,

                        'mime_type' =>
                        'application/pdf',

                        'file_size' =>
                        $fileSize,

                        'uploaded_by' =>
                        auth()->id(),
                    ]);
                }
            );
        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Remove PDF if database transaction fails
            |--------------------------------------------------------------------------
            */

            $disk->delete($path);

            throw $e;
        }

        return redirect()
            ->route(
                'admin.documents.edit',
                $document
            )
            ->with(
                'success',
                'PDF generated and saved successfully.'
            );
    }

    /**
     * Download a specific document version.
     */
    public function downloadVersion(
        Document $document,
        DocumentVersion $version
    ) {
        abort_unless(
            $version->document_id === $document->id,
            404
        );

        $disk = Storage::disk(
            $document->disk
        );

        abort_unless(
            $disk->exists(
                $version->file_path
            ),
            404
        );

        return $disk->download(
            $version->file_path,
            $version->original_name
                ?? (
                    $document->name .
                    '-v' .
                    $version->version
                )
        );
    }

    /**
     * Preview a specific document version.
     */
    public function previewVersion(
        Document $document,
        DocumentVersion $version
    ) {
        abort_unless(
            $version->document_id === $document->id,
            404
        );

        $disk = Storage::disk(
            $document->disk
        );

        abort_unless(
            $disk->exists(
                $version->file_path
            ),
            404
        );

        return $disk->response(
            $version->file_path,
            $version->original_name
                ?? (
                    $document->name .
                    '-v' .
                    $version->version
                ),
            [
                'Content-Type' =>
                $version->mime_type
                    ?: 'application/octet-stream',

                'Content-Disposition' =>
                'inline',
            ]
        );
    }

    /**
     * Resolve a document entity.
     */
    private function resolveEntity(
        string $entityType,
        int $entityId
    ) {
        return match ($entityType) {

            'customer' =>
            Customer::findOrFail(
                $entityId
            ),

            'shipment' =>
            Shipment::with([
                'customer',
                'originPort',
                'destinationPort',
                'vessel',
            ])->findOrFail(
                $entityId
            ),

            'quotation' =>
            Quotation::with([
                'customer',
                'originPort',
                'destinationPort',
            ])->findOrFail(
                $entityId
            ),

            default => abort(422),
        };
    }

    /**
     * Build normalized data for PDF templates.
     */
    private function buildPdfData(
        CompanySetting $company,
        ?Customer $customer,
        ?Shipment $shipment,
        ?Quotation $quotation
    ): array {
        return [

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'company' => [
                'name' =>
                $company->company_name,

                'name_ar' =>
                $company->company_name_ar,

                'email' =>
                $company->email,

                'phone' =>
                $company->phone,

                'whatsapp' =>
                $company->whatsapp,

                'website' =>
                $company->website,

                'address' =>
                $company->address,

                'city' =>
                $company->city,

                'country' =>
                $company->country,

                'po_box' =>
                $company->po_box,

                'tax_number' =>
                $company->tax_number,

                'license_number' =>
                $company->license_number,

                'footer_text' =>
                $company->footer_text,

                'logo' =>
                $this->companyImage(
                    $company->logo_path
                ),

                'signature' =>
                $this->companyImage(
                    $company->signature_path
                ),

                'stamp' =>
                $this->companyImage(
                    $company->stamp_path
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            'customer' => [
                'id' =>
                $customer?->id,

                'company_name' =>
                $customer?->company_name,

                'contact_person' =>
                $customer?->contact_person,

                'email' =>
                $customer?->email,

                'phone' =>
                $customer?->phone,

                'city' =>
                $customer?->city,

                'country' =>
                $customer?->country,
            ],

            /*
            |--------------------------------------------------------------------------
            | Shipment
            |--------------------------------------------------------------------------
            */

            'shipment' => [
                'id' =>
                $shipment?->id,

                'shipment_no' =>
                $shipment?->shipment_no,

                'booking_no' =>
                $shipment?->booking_no,

                'cargo_type' =>
                $shipment?->cargo_type,

                'container_type' =>
                $shipment?->container_type,

                'quantity' =>
                $shipment?->quantity,

                'weight' =>
                $shipment?->weight,

                'status' =>
                $shipment?->status,

                'preferred_date' =>
                $shipment?->preferred_date
                    ?->format('Y-m-d'),

                'eta' =>
                $shipment?->eta
                    ?->format('Y-m-d'),

                'actual_arrival' =>
                $shipment?->actual_arrival
                    ?->format(
                        'Y-m-d H:i'
                    ),

                'origin_port' =>
                $shipment?->originPort?->name,

                'destination_port' =>
                $shipment?->destinationPort?->name,

                'vessel' =>
                $shipment?->vessel?->name,
            ],

            /*
            |--------------------------------------------------------------------------
            | Quotation
            |--------------------------------------------------------------------------
            */

            'quotation' => [
                'id' =>
                $quotation?->id,

                'reference_no' =>
                $quotation?->reference_no,

                'cargo_type' =>
                $quotation?->cargo_type,

                'container_type' =>
                $quotation?->container_type,

                'quantity' =>
                $quotation?->quantity,

                'weight' =>
                $quotation?->weight,

                'total_charge' =>
                $quotation?->total_charge,

                'status' =>
                $quotation?->status,

                'preferred_date' =>
                $quotation?->preferred_date
                    ?->format('Y-m-d'),

                'origin_port' =>
                $quotation?->originPort?->name,

                'destination_port' =>
                $quotation?->destinationPort?->name,
            ],
        ];
    }

    /**
     * Convert company image to a Data URI.
     *
     * Dompdf can render the image directly from
     * the generated Base64 Data URI.
     */
    private function companyImage(
        ?string $path
    ): ?string {
        if (!$path) {
            return null;
        }

        $disk = Storage::disk(
            'public'
        );

        if (!$disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType(
            $path
        );

        $contents = $disk->get(
            $path
        );

        return 'data:' .
            $mime .
            ';base64,' .
            base64_encode($contents);
    }
}
