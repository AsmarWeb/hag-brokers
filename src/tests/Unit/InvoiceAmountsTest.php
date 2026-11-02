<?php

namespace Tests\Unit;

use App\Services\Revenue\InvoiceAmounts;
use PHPUnit\Framework\TestCase;

class InvoiceAmountsTest extends TestCase
{
    public function test_exclusive_standard_tax_is_exact_and_rounded_per_line(): void
    {
        $result = (new InvoiceAmounts())->calculate('125.50', 'exclusive', 'standard', '1');
        $this->assertSame('125.50', $result['subtotal']);
        $this->assertSame('6.28', $result['tax_amount']);
        $this->assertSame('131.78', $result['total']);
        $this->assertSame('1.00000000', $result['exchange_rate']);
    }

    public function test_inclusive_standard_tax_keeps_approved_gross_amount(): void
    {
        $result = (new InvoiceAmounts())->calculate('100.00', 'inclusive', 'standard', '1');
        $this->assertSame('95.24', $result['subtotal']);
        $this->assertSame('4.76', $result['tax_amount']);
        $this->assertSame('100.00', $result['total']);
    }

    public function test_explicit_zero_rate_does_not_add_tax(): void
    {
        $result = (new InvoiceAmounts())->calculate('100.00', 'exclusive', 'zero', '1');
        $this->assertSame('0.00', $result['tax_amount']);
        $this->assertSame('100.00', $result['total']);
    }

    public function test_foreign_currency_conversion_uses_exact_rate_and_aed_totals(): void
    {
        $result = (new InvoiceAmounts())->calculate('100.00', 'exclusive', 'standard', '3.6725');
        $this->assertSame('367.25', $result['subtotal_aed']);
        $this->assertSame('18.36', $result['tax_aed']);
        $this->assertSame('385.61', $result['total_aed']);
    }

    public function test_no_default_tax_treatment_is_assumed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new InvoiceAmounts())->calculate('100.00', 'exclusive', '', '1');
    }

    public function test_zero_exchange_rate_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new InvoiceAmounts())->calculate('100.00', 'exclusive', 'standard', '0');
    }

    public function test_database_amount_overflow_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new InvoiceAmounts())->calculate('9999999999999999.99', 'exclusive', 'standard', '2');
    }
}
