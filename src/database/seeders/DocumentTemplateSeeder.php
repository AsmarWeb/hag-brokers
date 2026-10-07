<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id');

        $templates = [

            /*
            |--------------------------------------------------------------------------
            | 1. Quotation
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Quotation',
                'slug' => 'quotation',
                'entity_type' => 'quotation',
                'document_type' => 'quotation',

                'header' => '
                    <div class="document-label">
                        FREIGHT QUOTATION
                    </div>
                ',

                'content' => '
                    <div class="meta-grid">
                        <div>
                            <span class="label">Quotation Reference</span>
                            <strong>{{quotation.reference_no}}</strong>
                        </div>

                        <div>
                            <span class="label">Date</span>
                            <strong>{{quotation.created_at}}</strong>
                        </div>

                        <div>
                            <span class="label">Status</span>
                            <strong>{{quotation.status}}</strong>
                        </div>

                        <div>
                            <span class="label">Customer</span>
                            <strong>{{customer.company_name}}</strong>
                        </div>
                    </div>

                    <h2>Shipment Details</h2>

                    <table>
                        <tr>
                            <th>Origin</th>
                            <td>{{quotation.origin_port}}</td>
                            <th>Destination</th>
                            <td>{{quotation.destination_port}}</td>
                        </tr>

                        <tr>
                            <th>Cargo Type</th>
                            <td>{{quotation.cargo_type}}</td>
                            <th>Container Type</th>
                            <td>{{quotation.container_type}}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{quotation.quantity}}</td>
                            <th>Weight</th>
                            <td>{{quotation.weight}}</td>
                        </tr>
                    </table>

                    <h2>Commercial Offer</h2>

                    <div class="total-box">
                        <span>Total Freight Charge</span>
                        <strong>{{quotation.total_charge}}</strong>
                    </div>

                    <div class="notice">
                        This quotation is subject to carrier availability,
                        final cargo details and the applicable terms and conditions.
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                        letter-spacing: 1px;
                        margin-bottom: 25px;
                    }

                    .meta-grid {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 12px;
                        margin-bottom: 30px;
                    }

                    .meta-grid > div {
                        border: 1px solid #d9e1e8;
                        padding: 12px;
                    }

                    .label {
                        display: block;
                        color: #64748b;
                        font-size: 10px;
                        margin-bottom: 5px;
                    }

                    .total-box {
                        margin-top: 25px;
                        padding: 18px;
                        background: #eef9fc;
                        border-left: 5px solid #22d3ee;
                        display: flex;
                        justify-content: space-between;
                    }

                    .notice {
                        margin-top: 25px;
                        padding: 15px;
                        background: #f8fafc;
                        font-size: 10px;
                    }
                ',

                'footer' => '
                    <div>
                        This quotation was generated electronically by {{company.name}}.
                    </div>
                ',
            ],

            /*
            |--------------------------------------------------------------------------
            | 2. Commercial Invoice
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Commercial Invoice',
                'slug' => 'commercial-invoice',
                'entity_type' => 'shipment',
                'document_type' => 'commercial_invoice',

                'header' => '
                    <div class="document-label">
                        COMMERCIAL INVOICE
                    </div>
                ',

                'content' => '
                    <div class="invoice-info">
                        <div>
                            <strong>Invoice To</strong>
                            <p>{{customer.company_name}}</p>
                            <p>{{customer.email}}</p>
                            <p>{{customer.phone}}</p>
                        </div>

                        <div>
                            <strong>Shipment</strong>
                            <p>{{shipment.shipment_no}}</p>
                            <p>Booking: {{shipment.booking_no}}</p>
                            <p>Status: {{shipment.status}}</p>
                        </div>
                    </div>

                    <h2>Cargo Information</h2>

                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Container</th>
                                <th>Quantity</th>
                                <th>Weight</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>{{shipment.cargo_type}}</td>
                                <td>{{shipment.container_type}}</td>
                                <td>{{shipment.quantity}}</td>
                                <td>{{shipment.weight}}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="invoice-total">
                        <span>Total Amount</span>
                        <strong>{{quotation.total_charge}}</strong>
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                        margin-bottom: 25px;
                    }

                    .invoice-info {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 30px;
                    }

                    .invoice-info > div {
                        width: 47%;
                        border: 1px solid #d9e1e8;
                        padding: 15px;
                    }

                    .invoice-info p {
                        margin: 4px 0;
                    }

                    .invoice-total {
                        margin-top: 30px;
                        padding: 18px;
                        text-align: right;
                        border-top: 3px solid #06101d;
                        font-size: 18px;
                    }
                ',

                'footer' => '
                    Payment and commercial terms are subject to the agreed service conditions.
                ',
            ],

            /*
            |--------------------------------------------------------------------------
            | 3. Packing List
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Packing List',
                'slug' => 'packing-list',
                'entity_type' => 'shipment',
                'document_type' => 'packing_list',

                'header' => '<div class="document-label">PACKING LIST</div>',

                'content' => '
                    <div class="meta-grid">
                        <div>
                            <span class="label">Shipment No.</span>
                            <strong>{{shipment.shipment_no}}</strong>
                        </div>

                        <div>
                            <span class="label">Booking No.</span>
                            <strong>{{shipment.booking_no}}</strong>
                        </div>

                        <div>
                            <span class="label">Customer</span>
                            <strong>{{customer.company_name}}</strong>
                        </div>

                        <div>
                            <span class="label">Cargo Type</span>
                            <strong>{{shipment.cargo_type}}</strong>
                        </div>
                    </div>

                    <h2>Cargo Packing Details</h2>

                    <table>
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Container Type</th>
                                <th>Quantity</th>
                                <th>Total Weight</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>{{shipment.cargo_type}}</td>
                                <td>{{shipment.container_type}}</td>
                                <td>{{shipment.quantity}}</td>
                                <td>{{shipment.weight}}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="signature-space">
                        <div>Prepared By</div>
                        <div>Authorized Signature</div>
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .meta-grid {
                        margin: 25px 0;
                    }

                    .meta-grid div {
                        display: inline-block;
                        width: 46%;
                        margin: 1%;
                        border: 1px solid #d9e1e8;
                        padding: 12px;
                    }

                    .label {
                        display: block;
                        color: #64748b;
                        font-size: 10px;
                    }

                    .signature-space {
                        margin-top: 80px;
                        display: flex;
                        justify-content: space-between;
                    }

                    .signature-space div {
                        width: 40%;
                        border-top: 1px solid #475569;
                        padding-top: 8px;
                    }
                ',

                'footer' => 'Packing List — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 4. Booking Confirmation
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Booking Confirmation',
                'slug' => 'booking-confirmation',
                'entity_type' => 'shipment',
                'document_type' => 'booking_confirmation',

                'header' => '<div class="document-label">BOOKING CONFIRMATION</div>',

                'content' => '
                    <div class="success-box">
                        Your shipment booking has been confirmed.
                    </div>

                    <h2>Booking Details</h2>

                    <table>
                        <tr>
                            <th>Shipment Number</th>
                            <td>{{shipment.shipment_no}}</td>
                        </tr>

                        <tr>
                            <th>Booking Number</th>
                            <td>{{shipment.booking_no}}</td>
                        </tr>

                        <tr>
                            <th>Customer</th>
                            <td>{{customer.company_name}}</td>
                        </tr>

                        <tr>
                            <th>Origin</th>
                            <td>{{shipment.origin_port}}</td>
                        </tr>

                        <tr>
                            <th>Destination</th>
                            <td>{{shipment.destination_port}}</td>
                        </tr>

                        <tr>
                            <th>Preferred Date</th>
                            <td>{{shipment.preferred_date}}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>{{shipment.status}}</td>
                        </tr>
                    </table>

                    <h2>Important Information</h2>

                    <p>
                        Please verify all shipment and cargo information before
                        submitting final shipping instructions.
                    </p>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .success-box {
                        margin: 25px 0;
                        padding: 18px;
                        background: #ecfeff;
                        border: 1px solid #67e8f9;
                        font-weight: 700;
                    }
                ',

                'footer' => 'Booking Confirmation — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 5. Shipping Instruction
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Shipping Instruction',
                'slug' => 'shipping-instruction',
                'entity_type' => 'shipment',
                'document_type' => 'shipping_instruction',

                'header' => '<div class="document-label">SHIPPING INSTRUCTION</div>',

                'content' => '
                    <h2>Shipper Information</h2>

                    <table>
                        <tr>
                            <th>Customer</th>
                            <td>{{customer.company_name}}</td>
                        </tr>

                        <tr>
                            <th>Email</th>
                            <td>{{customer.email}}</td>
                        </tr>

                        <tr>
                            <th>Phone</th>
                            <td>{{customer.phone}}</td>
                        </tr>
                    </table>

                    <h2>Shipment Instructions</h2>

                    <table>
                        <tr>
                            <th>Shipment No.</th>
                            <td>{{shipment.shipment_no}}</td>
                        </tr>

                        <tr>
                            <th>Booking No.</th>
                            <td>{{shipment.booking_no}}</td>
                        </tr>

                        <tr>
                            <th>Origin</th>
                            <td>{{shipment.origin_port}}</td>
                        </tr>

                        <tr>
                            <th>Destination</th>
                            <td>{{shipment.destination_port}}</td>
                        </tr>

                        <tr>
                            <th>Cargo</th>
                            <td>{{shipment.cargo_type}}</td>
                        </tr>

                        <tr>
                            <th>Container</th>
                            <td>{{shipment.container_type}}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{shipment.quantity}}</td>
                        </tr>

                        <tr>
                            <th>Weight</th>
                            <td>{{shipment.weight}}</td>
                        </tr>
                    </table>

                    <div class="notice">
                        The above instructions are submitted for shipment processing
                        and documentation purposes.
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .notice {
                        margin-top: 25px;
                        padding: 15px;
                        background: #f8fafc;
                        border-left: 4px solid #22d3ee;
                    }
                ',

                'footer' => 'Shipping Instruction — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 6. Bill of Lading
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Bill of Lading',
                'slug' => 'bill-of-lading',
                'entity_type' => 'shipment',
                'document_type' => 'bill_of_lading',

                'header' => '<div class="document-label">BILL OF LADING</div>',

                'content' => '
                    <div class="bl-notice">
                        DRAFT — FOR DOCUMENTATION PURPOSES
                    </div>

                    <table>
                        <tr>
                            <th>Shipper</th>
                            <td>{{customer.company_name}}</td>
                        </tr>

                        <tr>
                            <th>Shipment No.</th>
                            <td>{{shipment.shipment_no}}</td>
                        </tr>

                        <tr>
                            <th>Booking No.</th>
                            <td>{{shipment.booking_no}}</td>
                        </tr>

                        <tr>
                            <th>Port of Loading</th>
                            <td>{{shipment.origin_port}}</td>
                        </tr>

                        <tr>
                            <th>Port of Discharge</th>
                            <td>{{shipment.destination_port}}</td>
                        </tr>
                    </table>

                    <h2>Cargo Description</h2>

                    <table>
                        <tr>
                            <th>Cargo Type</th>
                            <td>{{shipment.cargo_type}}</td>
                        </tr>

                        <tr>
                            <th>Container Type</th>
                            <td>{{shipment.container_type}}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{shipment.quantity}}</td>
                        </tr>

                        <tr>
                            <th>Weight</th>
                            <td>{{shipment.weight}}</td>
                        </tr>
                    </table>

                    <div class="signature-space">
                        <div>Carrier / Agent</div>
                        <div>Authorized Signature</div>
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .bl-notice {
                        margin: 20px 0;
                        padding: 12px;
                        border: 1px solid #94a3b8;
                        text-align: center;
                        font-weight: 700;
                    }

                    .signature-space {
                        margin-top: 90px;
                        display: flex;
                        justify-content: space-between;
                    }

                    .signature-space div {
                        width: 40%;
                        border-top: 1px solid #475569;
                        padding-top: 8px;
                    }
                ',

                'footer' => 'Bill of Lading — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 7. Delivery Order
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Delivery Order',
                'slug' => 'delivery-order',
                'entity_type' => 'shipment',
                'document_type' => 'delivery_order',

                'header' => '<div class="document-label">DELIVERY ORDER</div>',

                'content' => '
                    <div class="release-box">
                        DELIVERY RELEASE
                    </div>

                    <p>
                        Please release the cargo described below to the authorized
                        consignee upon completion of all applicable requirements.
                    </p>

                    <table>
                        <tr>
                            <th>Consignee</th>
                            <td>{{customer.company_name}}</td>
                        </tr>

                        <tr>
                            <th>Shipment No.</th>
                            <td>{{shipment.shipment_no}}</td>
                        </tr>

                        <tr>
                            <th>Booking No.</th>
                            <td>{{shipment.booking_no}}</td>
                        </tr>

                        <tr>
                            <th>Destination</th>
                            <td>{{shipment.destination_port}}</td>
                        </tr>

                        <tr>
                            <th>Cargo</th>
                            <td>{{shipment.cargo_type}}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{shipment.quantity}}</td>
                        </tr>
                    </table>

                    <div class="authorization">
                        Authorized by: {{company.name}}
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .release-box {
                        margin: 25px 0;
                        padding: 15px;
                        text-align: center;
                        border: 2px solid #06101d;
                        font-weight: 700;
                    }

                    .authorization {
                        margin-top: 50px;
                        padding-top: 15px;
                        border-top: 1px solid #94a3b8;
                    }
                ',

                'footer' => 'Delivery Order — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 8. Freight Invoice
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Freight Invoice',
                'slug' => 'freight-invoice',
                'entity_type' => 'quotation',
                'document_type' => 'freight_invoice',

                'header' => '<div class="document-label">FREIGHT INVOICE</div>',

                'content' => '
                    <div class="invoice-header">
                        <div>
                            <span>Bill To</span>
                            <strong>{{customer.company_name}}</strong>
                        </div>

                        <div>
                            <span>Reference</span>
                            <strong>{{quotation.reference_no}}</strong>
                        </div>
                    </div>

                    <h2>Freight Charges</h2>

                    <table>
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>Sea Freight & Logistics Services</td>
                                <td>{{quotation.total_charge}}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="grand-total">
                        <span>Grand Total</span>
                        <strong>{{quotation.total_charge}}</strong>
                    </div>

                    <p class="terms">
                        Payment is due according to the agreed commercial terms.
                    </p>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .invoice-header {
                        display: flex;
                        justify-content: space-between;
                        margin: 25px 0;
                    }

                    .invoice-header div {
                        width: 45%;
                    }

                    .invoice-header span {
                        display: block;
                        color: #64748b;
                        font-size: 10px;
                    }

                    .grand-total {
                        margin-top: 25px;
                        padding: 18px;
                        border-top: 3px solid #06101d;
                        display: flex;
                        justify-content: space-between;
                        font-size: 18px;
                    }

                    .terms {
                        margin-top: 30px;
                        color: #64748b;
                        font-size: 10px;
                    }
                ',

                'footer' => 'Freight Invoice — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 9. Certificate of Origin
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Certificate of Origin',
                'slug' => 'certificate-of-origin',
                'entity_type' => 'shipment',
                'document_type' => 'certificate_of_origin',

                'header' => '<div class="document-label">CERTIFICATE OF ORIGIN</div>',

                'content' => '
                    <p>
                        This document certifies the declared origin information
                        associated with the shipment described below.
                    </p>

                    <table>
                        <tr>
                            <th>Exporter</th>
                            <td>{{customer.company_name}}</td>
                        </tr>

                        <tr>
                            <th>Shipment Number</th>
                            <td>{{shipment.shipment_no}}</td>
                        </tr>

                        <tr>
                            <th>Country of Origin</th>
                            <td>{{company.country}}</td>
                        </tr>

                        <tr>
                            <th>Port of Loading</th>
                            <td>{{shipment.origin_port}}</td>
                        </tr>

                        <tr>
                            <th>Destination</th>
                            <td>{{shipment.destination_port}}</td>
                        </tr>

                        <tr>
                            <th>Cargo Description</th>
                            <td>{{shipment.cargo_type}}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{shipment.quantity}}</td>
                        </tr>

                        <tr>
                            <th>Weight</th>
                            <td>{{shipment.weight}}</td>
                        </tr>
                    </table>

                    <div class="certificate">
                        Certified by {{company.name}}
                    </div>

                    <div class="signature-space">
                        <div>Authorized Signature</div>
                        <div>Company Stamp</div>
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                        text-align: center;
                    }

                    .certificate {
                        margin-top: 35px;
                        padding: 20px;
                        border: 1px solid #94a3b8;
                        text-align: center;
                        font-weight: 700;
                    }

                    .signature-space {
                        margin-top: 90px;
                        display: flex;
                        justify-content: space-between;
                    }

                    .signature-space div {
                        width: 40%;
                        border-top: 1px solid #475569;
                        padding-top: 8px;
                    }
                ',

                'footer' => 'Certificate of Origin — {{company.name}}',
            ],

            /*
            |--------------------------------------------------------------------------
            | 10. Shipment Summary
            |--------------------------------------------------------------------------
            */

            [
                'name' => 'Shipment Summary',
                'slug' => 'shipment-summary',
                'entity_type' => 'shipment',
                'document_type' => 'shipment_summary',

                'header' => '<div class="document-label">SHIPMENT SUMMARY</div>',

                'content' => '
                    <div class="summary-status">
                        Shipment Status: <strong>{{shipment.status}}</strong>
                    </div>

                    <h2>Shipment Information</h2>

                    <table>
                        <tr>
                            <th>Shipment Number</th>
                            <td>{{shipment.shipment_no}}</td>
                        </tr>

                        <tr>
                            <th>Booking Number</th>
                            <td>{{shipment.booking_no}}</td>
                        </tr>

                        <tr>
                            <th>Customer</th>
                            <td>{{customer.company_name}}</td>
                        </tr>

                        <tr>
                            <th>Origin</th>
                            <td>{{shipment.origin_port}}</td>
                        </tr>

                        <tr>
                            <th>Destination</th>
                            <td>{{shipment.destination_port}}</td>
                        </tr>

                        <tr>
                            <th>Vessel</th>
                            <td>{{shipment.vessel}}</td>
                        </tr>

                        <tr>
                            <th>Cargo Type</th>
                            <td>{{shipment.cargo_type}}</td>
                        </tr>

                        <tr>
                            <th>Container Type</th>
                            <td>{{shipment.container_type}}</td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>{{shipment.quantity}}</td>
                        </tr>

                        <tr>
                            <th>Weight</th>
                            <td>{{shipment.weight}}</td>
                        </tr>

                        <tr>
                            <th>ETA</th>
                            <td>{{shipment.eta}}</td>
                        </tr>
                    </table>

                    <div class="summary-footer">
                        Prepared by {{company.name}}
                    </div>
                ',

                'styles' => '
                    .document-label {
                        font-size: 24px;
                        font-weight: 700;
                    }

                    .summary-status {
                        margin: 25px 0;
                        padding: 15px;
                        background: #ecfeff;
                        border: 1px solid #67e8f9;
                    }

                    .summary-footer {
                        margin-top: 40px;
                        padding-top: 15px;
                        border-top: 1px solid #cbd5e1;
                    }
                ',

                'footer' => 'Shipment Summary — {{company.name}}',
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                [
                    'slug' => $template['slug'],
                ],
                [
                    'name' => $template['name'],
                    'entity_type' => $template['entity_type'],
                    'document_type' => $template['document_type'],
                    'header' => $template['header'],
                    'content' => $template['content'],
                    'styles' => $template['styles'],
                    'footer' => $template['footer'],
                    'is_active' => true,
                    'version' => 1,
                    'metadata' => [
                        'header_color' => '#06101d',
                        'font_family' => 'DejaVu Sans',
                    ],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );
        }

        $this->command?->info(
            count($templates) . ' document templates seeded successfully.'
        );
    }
}
