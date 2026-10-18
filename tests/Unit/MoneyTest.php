<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Formatage des montants en GNF (helpers gnf(), Money::short(), TVA 18 %). */
class MoneyTest extends TestCase
{
    /** Espace fine insécable, entre les groupes de milliers. */
    private const NNBSP = "\u{202F}";

    /** Espace insécable, devant l'unité. */
    private const NBSP = "\u{00A0}";

    public function test_gnf_formats_amounts_with_french_thousands_separators(): void
    {
        $this->assertSame('13'.self::NNBSP.'500'.self::NNBSP.'000'.self::NBSP.'GNF', Money::gnf(13_500_000));
        $this->assertSame('13'.self::NNBSP.'500'.self::NNBSP.'000', Money::gnf(13_500_000, false));
        $this->assertSame('950'.self::NBSP.'GNF', Money::gnf(950));
        $this->assertSame('0'.self::NBSP.'GNF', Money::gnf(0));
        $this->assertSame('0'.self::NBSP.'GNF', Money::gnf(null));
    }

    public function test_gnf_rounds_floats_to_whole_francs(): void
    {
        $this->assertSame('1'.self::NNBSP.'501'.self::NBSP.'GNF', Money::gnf(1500.5));
        $this->assertSame('1'.self::NNBSP.'500'.self::NBSP.'GNF', Money::gnf(1500.4));
    }

    public function test_gnf_helper_delegates_to_money(): void
    {
        $this->assertSame(Money::gnf(5_000_000), gnf(5_000_000));
        $this->assertSame(Money::gnf(5_000_000, false), gnf(5_000_000, false));
        $this->assertSame(Money::gnf(null), gnf(null));
    }

    #[DataProvider('shortAmounts')]
    public function test_short_abbreviates_large_amounts(int|float|null $amount, string $expected): void
    {
        $this->assertSame($expected, Money::short($amount));
    }

    /** @return array<string, array{int|float|null, string}> */
    public static function shortAmounts(): array
    {
        return [
            'millions ronds' => [5_000_000, '5'.self::NBSP.'M'.self::NBSP.'GNF'],
            'millions avec décimale' => [13_500_000, '13,5'.self::NBSP.'M'.self::NBSP.'GNF'],
            'milliards' => [1_500_000_000, '1'.self::NNBSP.'500'.self::NBSP.'M'.self::NBSP.'GNF'],
            'milliers' => [600_000, '600'.self::NBSP.'k'.self::NBSP.'GNF'],
            'milliers arrondis' => [1_500, '2'.self::NBSP.'k'.self::NBSP.'GNF'],
            'centaines' => [950, '950'.self::NBSP.'GNF'],
            'zéro' => [0, '0'.self::NBSP.'GNF'],
            'null' => [null, '0'.self::NBSP.'GNF'],
        ];
    }

    public function test_vat_is_18_percent_rounded_to_the_nearest_franc(): void
    {
        $this->assertSame(0.18, Money::VAT_RATE);
        $this->assertSame(180_000, Money::vat(1_000_000));
        $this->assertSame(900_000, Money::vat(5_000_000));
        $this->assertSame(0, Money::vat(0));
        $this->assertSame(1, Money::vat(3)); // 0,54 → 1
        $this->assertSame(0, Money::vat(2)); // 0,36 → 0
    }

    public function test_ttc_adds_vat_to_the_amount(): void
    {
        $this->assertSame(1_180_000, Money::ttc(1_000_000));
        $this->assertSame(5_900_000, Money::ttc(5_000_000));
        $this->assertSame(0, Money::ttc(0));
        $this->assertSame(7_777_777 + Money::vat(7_777_777), Money::ttc(7_777_777));
    }
}
