<?php
namespace Tests\Unit;
use App\Services\Revenue\CommissionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
class CommissionCalculatorTest extends TestCase {
    public function test_percent_and_half_up_rounding(): void {
        $c=new CommissionCalculator;
        $this->assertSame('25.00',$c->percent('1000.00',250));
        $this->assertSame('0.01',$c->percent('0.20',250));
        $this->assertSame('0.00',$c->percent('0.19',250));
    }
    public function test_fixed_and_spread(): void {
        $c=new CommissionCalculator;
        $this->assertSame('40.50',$c->fixed('40.5'));
        $this->assertSame('600.00',$c->spread('4800.00','4200.00'));
    }
    public function test_negative_spread_is_rejected(): void {
        $this->expectException(InvalidArgumentException::class);
        (new CommissionCalculator)->spread('100.00','101.00');
    }
    public function test_bad_money_precision_is_rejected(): void {
        $this->expectException(InvalidArgumentException::class);
        (new CommissionCalculator)->percent('1.999',200);
    }
    public function test_bps_limit(): void {
        $this->expectException(InvalidArgumentException::class);
        (new CommissionCalculator)->percent('10.00',10001);
    }
}
