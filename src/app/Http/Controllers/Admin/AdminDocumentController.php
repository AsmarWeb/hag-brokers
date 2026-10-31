<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Services\DocumentPdfService;
use App\Models\Customer;
use App\Models\Shipment;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                            $shipment->where('shipment_no', 'like', "%{$search}%")
                                ->orWhere('booking_no', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.documents.index', [
            'documents' => $documents,
        ]);
    }

    public function create(): View
    {
        return view('admin.documents.create', [
            'customers' => Customer::query()
                ->select('id', 'company_name', 'contact_person')
                ->orderBy('company_name')
                ->get(),

            'shipments' => Shipment::query()
                ->select('id', 'shipment_no', 'booking_no')
                ->orderByDesc('id')
                ->get(),

            'quotations' => Quotation::query()
                ->select('id', 'reference_no')
                ->orderByDesc('id')
                ->get(),

            'types' => Document::query()
                ->distinct()
                ->pluck('type'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'draft', 'archived'])],
            'documentable_type' => [
                'required',
                Rule::in(['customer', 'shipment', 'quotation']),
            ],
            'documentable_id' => ['required', 'integer', 'min:1'],
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
            ],
        ]);

        $models = [
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        ];

        $modelClass = $models[$validated['documentable_type']];

        $related = $modelClass::query()
            ->find($validated['documentable_id']);

        if (! $related) {
            throw ValidationException::withMessages([
                'documentable_id' => 'The selected record does not exist.',
            ]);
        }

        $file = $request->file('file');

        $path = $file->store('documents', 'local');

        if ($path === false) {
            throw ValidationException::withMessages([
                'file' => 'Unable to store the uploaded file.',
            ]);
        }

        try {
            DB::transaction(function () use (
                $validated,
                $related,
                $file,
                $path,
                $request
            ) {
                $document = Document::create([
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'status' => $validated['status'] ?? 'active',
                    'documentable_type' => $validated['documentable_type'],
                    'documentable_id' => $related->getKey(),
                    'shipment_id' => $related instanceof Shipment
                        ? $related->getKey()
                        : null,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_by' => $request->user()->getAuthIdentifier(),
                ]);

                $document->versions()->create([
                    'version' => 1,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $request->user()->getAuthIdentifier(),
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Document uploaded successfully.');
    }


    public function edit(Document $document): View
    {
        $document->load(['documentable', 'uploadedBy']);

        return view('admin.documents.edit', [
            'document' => $document,
            'customers' => Customer::query()
                ->select('id', 'company_name', 'contact_person')
                ->orderBy('company_name')
                ->get(),
            'shipments' => Shipment::query()
                ->select('id', 'shipment_no', 'booking_no')
                ->orderByDesc('id')
                ->get(),
            'quotations' => Quotation::query()
                ->select('id', 'reference_no')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function update(
        Request $request,
        Document $document
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
            'documentable_type' => [
                'required',
                Rule::in(['customer', 'shipment', 'quotation']),
            ],
            'documentable_id' => ['required', 'integer', 'min:1'],
            'file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
            ],
        ]);

        $models = [
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        ];

        $modelClass = $models[$validated['documentable_type']];

        $related = $modelClass::query()
            ->find($validated['documentable_id']);

        if (! $related) {
            throw ValidationException::withMessages([
                'documentable_id' => 'The selected record does not exist.',
            ]);
        }

        $file = $request->file('file');
        $path = null;

        if ($file) {
            $path = $file->store('documents', 'local');

            if ($path === false) {
                throw ValidationException::withMessages([
                    'file' => 'Unable to store the uploaded file.',
                ]);
            }
        }

        try {
            DB::transaction(function () use (
                $document,
                $validated,
                $related,
                $file,
                $path,
                $request
            ) {
                $lockedDocument = Document::query()
                    ->whereKey($document->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $attributes = [
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'status' => $validated['status'],
                    'documentable_type' => $validated['documentable_type'],
                    'documentable_id' => $related->getKey(),
                    'shipment_id' => $related instanceof Shipment
                        ? $related->getKey()
                        : null,
                ];

                if ($file) {
                    $latestVersion = (int) $lockedDocument
                        ->versions()
                        ->max('version');

                    $lockedDocument->versions()->create([
                        'version' => $latestVersion + 1,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $request->user()->getAuthIdentifier(),
                    ]);

                    $attributes['file_path'] = $path;
                    $attributes['original_name'] = $file->getClientOriginalName();
                    $attributes['uploaded_by'] = $request->user()->getAuthIdentifier();
                }

                $lockedDocument->update($attributes);
            }, 3);
        } catch (\Throwable $exception) {
            if ($path !== null) {
                Storage::disk('local')->delete($path);
            }

            if ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'file' => 'Another version was uploaded at the same time. Please try again.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.documents.edit', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function download(Document $document): StreamedResponse
    {
        $path = $document->file_path;

        abort_unless(
            is_string($path)
                && preg_match(
                    '~\Adocuments/[A-Za-z0-9/_-]+\.[A-Za-z0-9]+\z~D',
                    $path
                )
                && ! str_contains($path, '//')
                && ! str_contains($path, '..'),
            404
        );

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        $filename = $document->original_name
            ?: basename($path);

        return $disk->download($path, $filename);
    }


    public function preview(Document $document): StreamedResponse
    {
        $path = $document->file_path;

        abort_unless(
            is_string($path)
                && preg_match(
                    '~\Adocuments/[A-Za-z0-9/_-]+\.[A-Za-z0-9]+\z~D',
                    $path
                )
                && ! str_contains($path, '//')
                && ! str_contains($path, '..'),
            404
        );

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        $mime = $disk->mimeType($path);

        abort_unless(
            in_array($mime, [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ], true),
            415,
            'Preview is not supported for this file type.'
        );

        $filename = $document->original_name
            ?: basename($path);

        $response = $disk->response(
            $path,
            $filename,
            [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "sandbox",
                'Cache-Control' => 'private, no-store',
            ],
            'inline'
        );

        return $response;
    }


    public function versions(Document $document): View
    {
        $document->load([
            'documentable',
            'versions.uploadedBy',
        ]);

        return view('admin.documents.versions', [
            'document' => $document,
        ]);
    }


    public function downloadVersion(
        Document $document,
        int $version
    ): StreamedResponse {
        $documentVersion = $document->versions()
            ->whereKey($version)
            ->firstOrFail();

        $path = $documentVersion->file_path;

        abort_unless(
            is_string($path)
                && preg_match(
                    '~\Adocuments/[A-Za-z0-9/_-]+\.[A-Za-z0-9]+\z~D',
                    $path
                )
                && ! str_contains($path, '//')
                && ! str_contains($path, '..'),
            404
        );

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        return $disk->download(
            $path,
            $documentVersion->original_name ?: basename($path)
        );
    }


    public function previewVersion(
        Document $document,
        int $version
    ): StreamedResponse {
        $documentVersion = $document->versions()
            ->whereKey($version)
            ->firstOrFail();

        $path = $documentVersion->file_path;

        abort_unless(
            is_string($path)
                && preg_match(
                    '~\Adocuments/[A-Za-z0-9/_-]+\.[A-Za-z0-9]+\z~D',
                    $path
                )
                && ! str_contains($path, '//')
                && ! str_contains($path, '..'),
            404
        );

        $disk = Storage::disk('local');

        abort_unless($disk->exists($path), 404);

        $mime = $disk->mimeType($path);

        abort_unless(
            in_array($mime, [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ], true),
            415,
            'Preview is not supported for this file type.'
        );

        return $disk->response(
            $path,
            $documentVersion->original_name ?: basename($path),
            [
                'Content-Type' => $mime,
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => 'sandbox',
                'Cache-Control' => 'private, no-store',
            ],
            'inline'
        );
    }


    public function uploadVersion(
        Request $request,
        Document $document
    ): RedirectResponse {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp',
            ],
        ]);

        $file = $validated['file'];

        $path = $file->store('documents', 'local');

        if ($path === false) {
            throw ValidationException::withMessages([
                'file' => 'Unable to store the uploaded file.',
            ]);
        }

        try {
            DB::transaction(function () use ($document, $file, $path, $request) {
                $lockedDocument = Document::query()
                    ->whereKey($document->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $latestVersion = (int) $lockedDocument
                    ->versions()
                    ->max('version');

                $lockedDocument->versions()->create([
                    'version' => $latestVersion + 1,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $request->user()->getAuthIdentifier(),
                ]);

                $lockedDocument->update([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_by' => $request->user()->getAuthIdentifier(),
                ]);
            }, 3);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            if ($exception instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'file' => 'Another version was uploaded at the same time. Please try again.',
                ]);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.documents.versions', $document)
            ->with('success', 'New document version uploaded successfully.');
    }


    /**
     * Archive a document without deleting its files or version history.
     */
    public function destroy(Document $document): RedirectResponse
    {
        $document->update([
            'status' => 'archived',
        ]);

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Document archived successfully.');
    }



    /**
     * Display the PDF generation form.
     */
    public function pdfCreate(): View
    {
        return view('admin.documents.pdf-create', [
            'templates' => DocumentTemplate::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'customers' => Customer::query()->orderBy('id')->get(),
            'shipments' => Shipment::query()->orderBy('id')->get(),
            'quotations' => Quotation::query()->orderBy('id')->get(),
        ]);
    }



    /**
     * Preview a generated PDF without persisting it.
     */
    public function previewPdf(
        Request $request,
        DocumentPdfService $pdfService
    ): \Symfony\Component\HttpFoundation\Response {
        $validated = $request->validate([
            'template_id' => ['required', 'integer'],
            'document_type' => ['required', 'string', 'max:100'],
            'entity_type' => [
                'required',
                Rule::in(['customer', 'shipment', 'quotation']),
            ],
            'entity_id' => ['required', 'integer', 'min:1'],
            'orientation' => [
                'nullable',
                Rule::in(['portrait', 'landscape']),
            ],
        ]);

        $template = DocumentTemplate::query()
            ->where('is_active', true)
            ->findOrFail($validated['template_id']);

        if (
            $template->document_type !== $validated['document_type'] ||
            $template->entity_type !== $validated['entity_type']
        ) {
            throw ValidationException::withMessages([
                'template_id' => 'The template does not match the selected document and source types.',
            ]);
        }

        $model = match ($validated['entity_type']) {
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        };

        $entity = $model::query()->findOrFail($validated['entity_id']);

        $data = [
            $validated['entity_type'] => $entity->toArray(),
        ];

        $pdf = $pdfService->output(
            $template,
            $data,
            $validated['orientation'] ?? 'portrait'
        );

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }



    /**
     * Generate and persist a PDF document with its first version.
     */
    public function generatePdf(
        Request $request,
        DocumentPdfService $pdfService
    ): RedirectResponse {
        $validated = $request->validate([
            'template_id' => ['required', 'integer'],
            'document_type' => ['required', 'string', 'max:100'],
            'entity_type' => [
                'required',
                Rule::in(['customer', 'shipment', 'quotation']),
            ],
            'entity_id' => ['required', 'integer', 'min:1'],
            'file_name' => ['nullable', 'string', 'max:150'],
            'orientation' => [
                'nullable',
                Rule::in(['portrait', 'landscape']),
            ],
        ]);

        $template = DocumentTemplate::query()
            ->where('is_active', true)
            ->findOrFail($validated['template_id']);

        if (
            $template->document_type !== $validated['document_type'] ||
            $template->entity_type !== $validated['entity_type']
        ) {
            throw ValidationException::withMessages([
                'template_id' => 'The template does not match the selected document and source types.',
            ]);
        }

        $model = match ($validated['entity_type']) {
            'customer' => Customer::class,
            'shipment' => Shipment::class,
            'quotation' => Quotation::class,
        };

        $entity = $model::query()->findOrFail($validated['entity_id']);

        $name = trim($validated['file_name'] ?? '');

        if ($name === '') {
            $name = $template->name . '-' . now()->format('Ymd-His');
        }

        $name = preg_replace('/\.pdf$/i', '', $name);
        $name = trim($name);

        if ($name === '') {
            throw ValidationException::withMessages([
                'file_name' => 'Please provide a valid file name.',
            ]);
        }

        $originalName = $name . '.pdf';

        $path = 'documents/generated/' . Str::uuid() . '.pdf';

        $data = [
            $validated['entity_type'] => $entity->toArray(),
        ];

        $pdf = $pdfService->output(
            $template,
            $data,
            $validated['orientation'] ?? 'portrait'
        );

        $stored = false;

        try {
            if (Storage::disk('local')->put($path, $pdf) !== true) {
                throw new \RuntimeException('Unable to store generated PDF.');
            }

            $stored = true;

            DB::transaction(function () use (
                $validated,
                $entity,
                $name,
                $originalName,
                $path,
                $pdf
            ): void {
                $document = Document::query()->create([
                    'name' => $name,
                    'type' => $validated['document_type'],
                    'status' => 'active',
                    'documentable_type' => $validated['entity_type'],
                    'documentable_id' => $entity->getKey(),
                    'shipment_id' => $validated['entity_type'] === 'shipment'
                        ? $entity->getKey()
                        : null,
                    'file_path' => $path,
                    'original_name' => $originalName,
                    'uploaded_by' => auth()->id(),
                ]);

                $document->versions()->create([
                    'version' => 1,
                    'file_path' => $path,
                    'original_name' => $originalName,
                    'mime_type' => 'application/pdf',
                    'file_size' => strlen($pdf),
                    'uploaded_by' => auth()->id(),
                ]);
            });
        } catch (\Throwable $exception) {
            if ($stored) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'PDF document generated successfully.');
    }


}
