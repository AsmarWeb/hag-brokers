<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\User;
use App\Services\DocumentPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Documents
    |--------------------------------------------------------------------------
    */

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
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
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

    /**
     * Create normal document form.
     */
    public function create(): View
    {
        $shipments = Shipment::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
            ])
            ->latest()
            ->get();

        return view('admin.documents.create', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Store normal uploaded document.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipment_id' => [
                'required',
                'integer',
                'exists:shipments,id',
            ],
            'type' => [
                'required',
                'string',
                'max:100',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'file' => [
                'required',
                'file',
                'max:25600',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $file = $request->file('file');

        $path = $file->store(
            'documents',
            'local'
        );

        Document::create([
            'shipment_id' => $validated['shipment_id'],
            'type' => $validated['type'],
            'name' => $validated['name'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'version' => 1,
            'status' => 'active',
            'uploaded_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Generator
    |--------------------------------------------------------------------------
    */

    public function pdfCreate(): View
    {
        $templates = DocumentTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
                'contact_person',
                'email',
                'phone',
            ]);

        $shipments = Shipment::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
                'vessel',
            ])
            ->latest()
            ->get([
                'id',
                'customer_id',
                'shipment_no',
                'booking_no',
                'origin_port_id',
                'destination_port_id',
                'vessel_id',
                'cargo_type',
                'container_type',
                'quantity',
                'weight',
                'weight_unit',
                'status',
                'preferred_date',
                'etd',
                'eta',
            ]);

        $quotations = Quotation::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
            ])
            ->latest()
            ->get([
                'id',
                'customer_id',
                'reference_no',
                'origin_port_id',
                'destination_port_id',
                'cargo_type',
                'container_type',
                'quantity',
                'weight',
                'preferred_date',
                'valid_until',
                'total_charge',
                'currency',
                'status',
            ]);

        return view('admin.documents.pdf-create', [
            'templates' => $templates,
            'customers' => $customers,
            'shipments' => $shipments,
            'quotations' => $quotations,
        ]);
    }

    public function previewPdf(
        Request $request,
        DocumentPdfService $pdfService
    ): Response {
        $validated = $this->validatePdfRequest($request);

        $template = DocumentTemplate::query()
            ->where('is_active', true)
            ->findOrFail($validated['template_id']);

        $data = $this->buildPdfData($validated);

        $pdf = $pdfService->generate(
            $template,
            $data
        );

        $pdf->setPaper(
            'a4',
            $validated['orientation']
        );

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]
        );
    }

    public function generatePdf(
        Request $request,
        DocumentPdfService $pdfService
    ): Response {
        $validated = $this->validatePdfRequest($request);

        $template = DocumentTemplate::query()
            ->where('is_active', true)
            ->findOrFail($validated['template_id']);

        $data = $this->buildPdfData($validated);

        $pdf = $pdfService->generate(
            $template,
            $data
        );

        $pdf->setPaper(
            'a4',
            $validated['orientation']
        );

        $fileName = $this->makePdfFileName(
            $validated['file_name']
                ?? $template->name
        );

        return response(
            $pdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit / Update / Delete
    |--------------------------------------------------------------------------
    */

    public function edit(Document $document): View
    {
        $document->load([
            'shipment',
            'uploadedBy',
        ]);

        return view('admin.documents.edit', [
            'document' => $document,
        ]);
    }

    public function update(
        Request $request,
        Document $document
    ): RedirectResponse {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                'max:100',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                'string',
                'max:50',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $document->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        if (
            $document->file_path &&
            Storage::disk('local')->exists($document->file_path)
        ) {
            Storage::disk('local')->delete(
                $document->file_path
            );
        }

        $document->delete();

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function validatePdfRequest(
        Request $request
    ): array {
        return $request->validate([
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
                'max:150',
            ],
            'orientation' => [
                'nullable',
                'in:portrait,landscape',
            ],
        ]) + [
            'orientation' => $request->input(
                'orientation',
                'portrait'
            ),
        ];
    }

    private function buildPdfData(
        array $validated
    ): array {
        $data = [
            'company' => [
                'name' => config(
                    'app.name',
                    'HAG Brokers'
                ),
                'email' => config(
                    'mail.from.address'
                ),
                'phone' => null,
                'logo' => null,
                'tax_number' => null,
            ],

            'generated_at' => now()->format(
                'Y-m-d H:i:s'
            ),

            'generated_date' => now()->format(
                'Y-m-d'
            ),

            'generated_time' => now()->format(
                'H:i'
            ),
        ];

        switch ($validated['entity_type']) {
            case 'customer':
                $customer = Customer::findOrFail(
                    $validated['entity_id']
                );

                $data['customer'] = [
                    'id' => $customer->id,
                    'company_name' => $customer->company_name,
                    'contact_person' => $customer->contact_person,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'country_code' => $customer->country_code,
                    'city' => $customer->city,
                ];

                break;

            case 'shipment':
                $shipment = Shipment::query()
                    ->with([
                        'customer',
                        'originPort',
                        'destinationPort',
                        'vessel',
                    ])
                    ->findOrFail(
                        $validated['entity_id']
                    );

                $data['customer'] = [
                    'id' => $shipment->customer?->id,
                    'company_name' => $shipment->customer?->company_name,
                    'contact_person' => $shipment->customer?->contact_person,
                    'email' => $shipment->customer?->email,
                    'phone' => $shipment->customer?->phone,
                ];

                $data['shipment'] = [
                    'id' => $shipment->id,
                    'shipment_no' => $shipment->shipment_no,
                    'booking_no' => $shipment->booking_no,
                    'cargo_type' => $shipment->cargo_type,
                    'container_type' => $shipment->container_type,
                    'quantity' => $shipment->quantity,
                    'weight' => $shipment->weight,
                    'weight_unit' => $shipment->weight_unit,
                    'status' => $shipment->status,
                    'preferred_date' => $shipment->preferred_date,
                    'etd' => $shipment->etd,
                    'eta' => $shipment->eta,
                    'origin_port' => $shipment->originPort?->name,
                    'destination_port' => $shipment->destinationPort?->name,
                    'vessel' => $shipment->vessel?->name,
                ];

                break;

            case 'quotation':
                $quotation = Quotation::query()
                    ->with([
                        'customer',
                        'originPort',
                        'destinationPort',
                    ])
                    ->findOrFail(
                        $validated['entity_id']
                    );

                $data['customer'] = [
                    'id' => $quotation->customer?->id,
                    'company_name' => $quotation->customer?->company_name,
                    'contact_person' => $quotation->customer?->contact_person,
                    'email' => $quotation->customer?->email,
                    'phone' => $quotation->customer?->phone,
                ];

                $data['quotation'] = [
                    'id' => $quotation->id,
                    'reference_no' => $quotation->reference_no,
                    'cargo_type' => $quotation->cargo_type,
                    'container_type' => $quotation->container_type,
                    'quantity' => $quotation->quantity,
                    'weight' => $quotation->weight,
                    'preferred_date' => $quotation->preferred_date,
                    'valid_until' => $quotation->valid_until,
                    'total_charge' => $quotation->total_charge,
                    'currency' => $quotation->currency,
                    'status' => $quotation->status,
                    'origin_port' => $quotation->originPort?->name,
                    'destination_port' => $quotation->destinationPort?->name,
                ];

                break;
        }

        return $data;
    }

    private function makePdfFileName(
        string $name
    ): string {
        $name = Str::of($name)
            ->replaceMatches(
                '/[^A-Za-z0-9\-_ ]+/',
                ''
            )
            ->trim()
            ->replace(' ', '-')
            ->value();

        return ($name !== '' ? $name : 'document') . '.pdf';
    }
}
