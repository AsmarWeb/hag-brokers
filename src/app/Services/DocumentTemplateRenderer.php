<?php

namespace App\Services;

class DocumentTemplateRenderer
{
    /**
     * Render a document template using nested data.
     *
     * Supported variables:
     *
     * {{company.name}}
     * {{customer.company_name}}
     * {{shipment.shipment_no}}
     * {{quotation.reference_no}}
     */
    public function render(
        string $content,
        array $data = []
    ): string {
        if ($content === '') {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | Add common generated values
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
        | Flatten nested data
        |--------------------------------------------------------------------------
        */

        $flatData = $this->flatten($data);

        /*
        |--------------------------------------------------------------------------
        | Replace variables
        |--------------------------------------------------------------------------
        */

        foreach ($flatData as $key => $value) {

            if ($value === null) {
                $value = '';
            }

            if (is_bool($value)) {
                $value = $value
                    ? 'Yes'
                    : 'No';
            }

            if (
                is_array($value) ||
                is_object($value)
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | We intentionally do NOT use e() here.
            |
            | Document templates contain HTML:
            |
            | <strong>{{company.name}}</strong>
            |
            | Escaping the entire value/content can interfere with
            | HTML-based document templates.
            |
            */

            $content = str_replace(
                '{{' . $key . '}}',
                (string) $value,
                $content
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove unresolved variables
        |--------------------------------------------------------------------------
        */

        $content = preg_replace(
            '/\{\{\s*[a-zA-Z0-9_.-]+\s*\}\}/',
            '',
            $content
        );

        return $content;
    }

    /**
     * Flatten nested arrays into dot notation.
     *
     * Example:
     *
     * company.name
     * customer.company_name
     * shipment.shipment_no
     */
    private function flatten(
        array $data,
        string $prefix = ''
    ): array {
        $result = [];

        foreach ($data as $key => $value) {

            $fullKey = $prefix === ''
                ? $key
                : $prefix . '.' . $key;

            if (is_array($value)) {

                $result += $this->flatten(
                    $value,
                    $fullKey
                );

                continue;
            }

            $result[$fullKey] = $value;
        }

        return $result;
    }
}
