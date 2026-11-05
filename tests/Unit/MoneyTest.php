<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_add_and_sub_are_precise(): void
    {
        $this->assertSame('1250000.00', Money::add('1000000.00', '250000.00'));
        $this->assertSame('750000.00', Money::sub('1000000.00', '250000.00'));
    }

    public function test_negate(): void
    {
        $this->assertSame('-50000.00', Money::negate('50000.00'));
        $this->assertSame('50000.00', Money::negate('-50000.00'));
    }

    public function test_compare(): void
    {
        $this->assertSame(-1, Money::compare('100.00', '200.00'));
        $this->assertSame(0, Money::compare('100.00', '100.00'));
        $this->assertSame(1, Money::compare('200.00', '100.00'));
    }

    public function test_handles_large_amounts_without_precision_loss(): void
    {
        $this->assertSame('20000000000.00', Money::add('9999999999.99', '10000000000.01'));
    }
}
