<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentPdfService
{
    /**
     * Document template renderer.
     */
    public function __construct(
        protected DocumentTemplateRenderer $renderer
    ) {}

    /**
     * Generate a PDF instance from a document template.
     *
     * Supported variables include:
     *
     * {{company.name}}
     * {{company.email}}
     * {{company.phone}}
     *
     * {{customer.company_name}}
     * {{customer.contact_person}}
     *
     * {{shipment.shipment_no}}
     * {{shipment.booking_no}}
     * {{shipment.origin_port}}
     * {{shipment.destination_port}}
     * {{shipment.vessel}}
     *
     * {{quotation.reference_no}}
     * {{quotation.total_charge}}
     *
     * {{generated_at}}
     * {{generated_date}}
     * {{generated_time}}
     */
    public function generate(
        DocumentTemplate $template,
        array $data = []
    ) {
        /*
        |--------------------------------------------------------------------------
        | Common generated values
        |--------------------------------------------------------------------------
        */

        $data['generated_at'] ??=
            now()->format('Y-m-d H:i:s');

        $data['generated_date'] ??=
            now()->format('Y-m-d');

        $data['generated_time'] ??=
            now()->format('H:i');

        /*
        |--------------------------------------------------------------------------
        | Render template sections
        |--------------------------------------------------------------------------
        */

        $content = $this->renderer->render(
            (string) $template->content,
            $data
        );

        $header = $this->renderer->render(
            (string) ($template->header ?? ''),
            $data
        );

        $footer = $this->renderer->render(
            (string) ($template->footer ?? ''),
            $data
        );

        $styles = $this->renderer->render(
            (string) ($template->styles ?? ''),
            $data
        );

        /*
        |--------------------------------------------------------------------------
        | Render final HTML document
        |--------------------------------------------------------------------------
        */

        $html = view(
            'documents.templates.render',
            [
                'template' => $template,
                'content' => $content,
                'header' => $header,
                'footer' => $footer,
                'styles' => $styles,
                'data' => $data,
            ]
        )->render();

        /*
        |--------------------------------------------------------------------------
        | Create Dompdf instance
        |--------------------------------------------------------------------------
        */

        return Pdf::loadHTML($html)
            ->setPaper(
                'a4',
                'portrait'
            );
    }

    /**
     * Generate PDF and return binary contents.
     */
    public function output(
        DocumentTemplate $template,
        array $data = [],
        string $orientation = 'portrait'
    ): string {
        $pdf = $this->generate(
            $template,
            $data
        );

        $pdf->setPaper(
            'a4',
            $this->normalizeOrientation(
                $orientation
            )
        );

        return $pdf->output();
    }

    /**
     * Save generated PDF.
     *
     * Returns the saved path.
     */
    public function save(
        DocumentTemplate $template,
        array $data,
        string $path,
        string $disk = 'local'
    ): string {
        $pdf = $this->generate(
            $template,
            $data
        );

        Storage::disk($disk)->put(
            $path,
            $pdf->output()
        );

        return $path;
    }

    /**
     * Generate and save PDF with selected orientation.
     */
    public function saveWithOrientation(
        DocumentTemplate $template,
        array $data,
        string $path,
        string $disk = 'local',
        string $orientation = 'portrait'
    ): string {
        $pdf = $this->generate(
            $template,
            $data
        );

        $pdf->setPaper(
            'a4',
            $this->normalizeOrientation(
                $orientation
            )
        );

        Storage::disk($disk)->put(
            $path,
            $pdf->output()
        );

        return $path;
    }

    /**
     * Normalize PDF orientation.
     */
    private function normalizeOrientation(
        string $orientation
    ): string {
        $orientation = strtolower(
            trim($orientation)
        );

        return in_array(
            $orientation,
            [
                'portrait',
                'landscape',
            ],
            true
        )
            ? $orientation
            : 'portrait';
    }
}
