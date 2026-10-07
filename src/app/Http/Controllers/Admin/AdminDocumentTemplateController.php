<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDocumentTemplateController extends Controller
{
    /**
     * Display all document templates.
     */
    public function index()
    {
        $templates = DocumentTemplate::query()
            ->with(['createdBy', 'updatedBy'])
            ->latest()
            ->paginate(12);

        return view('admin.document-templates.index', compact('templates'));
    }

    /**
     * Show create template form.
     */
    public function create()
    {
        return view('admin.document-templates.create');
    }

    /**
     * Store a new document template.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:document_templates,slug',
            ],

            'entity_type' => [
                'required',
                'string',
                'max:100',
            ],

            'document_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'header' => [
                'nullable',
                'string',
            ],

            'content' => [
                'required',
                'string',
            ],

            'styles' => [
                'nullable',
                'string',
            ],

            'footer' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $data['slug'] = $data['slug']
            ?: Str::slug($data['name']);

        /*
        |--------------------------------------------------------------------------
        | Make sure generated slug is unique
        |--------------------------------------------------------------------------
        */

        $originalSlug = $data['slug'];
        $counter = 1;

        while (
            DocumentTemplate::where('slug', $data['slug'])->exists()
        ) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        /*
        |--------------------------------------------------------------------------
        | Template metadata
        |--------------------------------------------------------------------------
        */

        $data['metadata'] = [
            'header_color' => '#06101d',
            'font_family' => 'DejaVu Sans',
        ];

        $data['version'] = 1;

        if (auth()->check()) {
            $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
        }

        $data['is_active'] = $request->boolean('is_active');

        DocumentTemplate::create($data);

        return redirect()
            ->route('admin.document-templates.index')
            ->with(
                'success',
                'Document template created successfully.'
            );
    }

    /**
     * Show edit template form.
     */
    public function edit(DocumentTemplate $documentTemplate)
    {
        return view(
            'admin.document-templates.edit',
            compact('documentTemplate')
        );
    }

    /**
     * Update document template.
     */
    public function update(
        Request $request,
        DocumentTemplate $documentTemplate
    ) {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:document_templates,slug,' . $documentTemplate->id,
            ],

            'entity_type' => [
                'required',
                'string',
                'max:100',
            ],

            'document_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'header' => [
                'nullable',
                'string',
            ],

            'content' => [
                'required',
                'string',
            ],

            'styles' => [
                'nullable',
                'string',
            ],

            'footer' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Detect template content changes
        |--------------------------------------------------------------------------
        */

        $contentChanged =
            $documentTemplate->content !== $data['content']
            || $documentTemplate->styles !== ($data['styles'] ?? null)
            || $documentTemplate->header !== ($data['header'] ?? null)
            || $documentTemplate->footer !== ($data['footer'] ?? null);

        if ($contentChanged) {
            $documentTemplate->version =
                (int) $documentTemplate->version + 1;
        }

        /*
        |--------------------------------------------------------------------------
        | Preserve metadata
        |--------------------------------------------------------------------------
        */

        $data['metadata'] = $documentTemplate->metadata ?? [
            'header_color' => '#06101d',
            'font_family' => 'DejaVu Sans',
        ];

        $data['is_active'] = $request->boolean('is_active');

        if (auth()->check()) {
            $data['updated_by'] = auth()->id();
        }

        $documentTemplate->update($data);

        return redirect()
            ->route('admin.document-templates.index')
            ->with(
                'success',
                'Document template updated successfully.'
            );
    }

    /**
     * Delete document template.
     */
    public function destroy(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->delete();

        return redirect()
            ->route('admin.document-templates.index')
            ->with(
                'success',
                'Document template deleted successfully.'
            );
    }
}
