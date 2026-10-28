<?php

namespace Tests\Unit;

use App\Services\Rating\RatingCalculationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RatingFormulaTest extends TestCase
{
    #[DataProvider('totals')]
    public function test_exact_total_uses_decimal_coefficient_once(int $base, string $coefficient, string $expected): void
    {
        self::assertSame($expected, RatingCalculationService::totalPoints($base, $coefficient));
    }

    public static function totals(): array
    {
        return [[60, '1.20', '72.00'], [40, '1.00', '40.00'], [1, '1.15', '1.15'], [0, '1.20', '0.00'], [1234567, '1.20', '1481480.40']];
    }
}
