<?php

namespace Tests\Unit;

use App\Support\LiturgicalCalendar;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class LiturgicalCalendarTest extends TestCase
{
    public function test_known_easter_dates(): void
    {
        $dates = [
            2024 => '2024-03-31',
            2025 => '2025-04-20',
            2026 => '2026-04-05',
            2027 => '2027-03-28',
        ];

        foreach ($dates as $year => $expected) {
            $this->assertSame($expected, LiturgicalCalendar::easterForYear($year)->toDateString());
        }
    }

    public function test_known_lent_and_pentecost_dates(): void
    {
        $this->assertSame('lent', LiturgicalCalendar::forDate(Carbon::parse('2026-02-18'))['period']['key']);
        $this->assertSame('palm_sunday', LiturgicalCalendar::forDate(Carbon::parse('2026-03-29'))['period']['key']);
        $this->assertSame('pentecost', LiturgicalCalendar::forDate(Carbon::parse('2026-05-24'))['period']['key']);
    }

    public function test_advent_and_gaudete_dates(): void
    {
        $this->assertSame('advent', LiturgicalCalendar::forDate(Carbon::parse('2026-11-29'))['period']['key']);
        $this->assertSame('gaudete', LiturgicalCalendar::forDate(Carbon::parse('2026-12-13'))['period']['key']);
    }

    public function test_easter_is_always_a_sunday_in_the_expected_range_over_thirty_years(): void
    {
        for ($year = 2020; $year < 2050; $year++) {
            $easter = LiturgicalCalendar::easterForYear($year);
            $this->assertSame(Carbon::SUNDAY, $easter->dayOfWeek);
            $this->assertTrue($easter->month === 3
                ? $easter->day >= 22
                : ($easter->month === 4 && $easter->day <= 25));
        }
    }
}
