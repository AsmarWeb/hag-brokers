<?php

namespace Tests\Unit;

use App\Services\Revenue\ReportMoney;
use PHPUnit\Framework\TestCase;

class ReportMoneyTest extends TestCase
{
    public function test_decimal_addition_and_large_values_are_exact(): void
    {
        $this->assertSame('0.30', ReportMoney::add('0.10', '0.20'));
        $this->assertSame('1000000000000000000000000.00', ReportMoney::add('999999999999999999999999.99', '0.01'));
        $this->assertSame('12.50', ReportMoney::add('00012.5', '0'));
    }

    public function test_display_format_does_not_round_through_float(): void
    {
        $this->assertSame('12,345,678,901,234,567.89', ReportMoney::format('12345678901234567.89'));
        $this->assertSame('0.00', ReportMoney::format('0'));
    }

    public function test_invalid_precision_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReportMoney::normalize('1.234');
    }

    public function test_negative_amount_is_not_silently_converted(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReportMoney::normalize('-1.00');
    }
}
