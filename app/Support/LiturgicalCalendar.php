<?php

namespace App\Support;

use Carbon\Carbon;
use DateTimeImmutable;
use DateTimeInterface;

class LiturgicalCalendar
{
    public static function easterForYear(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($year, $month, $day, 0, 0, 0, 'Africa/Porto-Novo')->startOfDay();
    }

    /**
     * @return array{period: array{key: string, label: string}, color: array{key: string, label: string, hex: string}}
     */
    public static function forDate(DateTimeInterface $date): array
    {
        $day = Carbon::instance(DateTimeImmutable::createFromInterface($date))
            ->setTimezone('Africa/Porto-Novo')
            ->startOfDay();
        $year = $day->year;
        $easter = self::easterForYear($year);
        $ashWednesday = $easter->copy()->subDays(46);
        $palmSunday = $easter->copy()->subDays(7);
        $holyThursday = $easter->copy()->subDays(3);
        $goodFriday = $easter->copy()->subDays(2);
        $holySaturday = $easter->copy()->subDay();
        $pentecost = $easter->copy()->addDays(49);
        $laetare = $ashWednesday->copy()->addDays(21)->startOfWeek(Carbon::SUNDAY);
        $gaudete = self::gaudeteSunday($year);
        $advent = self::adventSunday($year);
        $baptism = self::baptismOfTheLord($year);

        $period = 'ordinary';
        $label = 'Temps ordinaire';
        $colorKey = 'green';
        $colorLabel = 'Vert';
        $hex = '#15803D';

        if ($day->equalTo($pentecost)) {
            return self::result('pentecost', 'Pentecôte', 'red', 'Rouge', '#DC2626');
        }

        if ($day->greaterThanOrEqualTo($easter) && $day->lessThan($pentecost)) {
            return self::result('easter', 'Temps pascal', 'white', 'Blanc', '#FFFFFF');
        }

        if ($day->equalTo($palmSunday)) {
            return self::result('palm_sunday', 'Dimanche des Rameaux', 'red', 'Rouge', '#DC2626');
        }

        if ($day->equalTo($holyThursday)) {
            return self::result('holy_thursday', 'Jeudi saint', 'white', 'Blanc', '#FFFFFF');
        }

        if ($day->equalTo($goodFriday)) {
            return self::result('good_friday', 'Vendredi saint', 'red', 'Rouge', '#DC2626');
        }

        if ($day->equalTo($holySaturday)) {
            return self::result('holy_saturday', 'Samedi saint', 'white', 'Blanc', '#FFFFFF');
        }

        if ($day->greaterThanOrEqualTo($ashWednesday) && $day->lessThan($holyThursday)) {
            if ($day->equalTo($laetare)) {
                return self::result('laetare', '4e dimanche du Carême', 'rose', 'Rose', '#F472B6');
            }

            return self::result('lent', 'Carême', 'violet', 'Violet', '#7C3AED');
        }

        if ($day->greaterThanOrEqualTo($advent) && $day->lt(Carbon::create($year, 12, 25, 0, 0, 0, 'Africa/Porto-Novo'))) {
            if ($day->equalTo($gaudete)) {
                return self::result('gaudete', '3e dimanche de l’Avent', 'rose', 'Rose', '#F472B6');
            }

            return self::result('advent', 'Avent', 'violet', 'Violet', '#7C3AED');
        }

        if (($day->month === 12 && $day->day >= 25) || ($day->month === 1 && $day->lt($baptism))) {
            return self::result('christmas', 'Temps de Noël', 'white', 'Blanc', '#FFFFFF');
        }

        if ($day->equalTo($baptism)) {
            return self::result('baptism', 'Baptême du Seigneur', 'white', 'Blanc', '#FFFFFF');
        }

        return self::result($period, $label, $colorKey, $colorLabel, $hex);
    }

    public static function adventSunday(int $year): Carbon
    {
        $christmas = Carbon::create($year, 12, 25, 0, 0, 0, 'Africa/Porto-Novo')->startOfDay();

        return $christmas->copy()->subDays($christmas->dayOfWeek)->subDays(21);
    }

    public static function gaudeteSunday(int $year): Carbon
    {
        return self::adventSunday($year)->copy()->addDays(14);
    }

    public static function baptismOfTheLord(int $year): Carbon
    {
        $jan7 = Carbon::create($year, 1, 7, 0, 0, 0, 'Africa/Porto-Novo')->startOfDay();
        $offset = (7 - $jan7->dayOfWeek) % 7;

        return $jan7->copy()->addDays($offset);
    }

    /**
     * @return array{period: array{key: string, label: string}, color: array{key: string, label: string, hex: string}}
     */
    private static function result(string $periodKey, string $periodLabel, string $colorKey, string $colorLabel, string $hex): array
    {
        return [
            'period' => ['key' => $periodKey, 'label' => $periodLabel],
            'color' => ['key' => $colorKey, 'label' => $colorLabel, 'hex' => $hex],
        ];
    }
}
