<?php
namespace App\Services;

use Carbon\Carbon;

class UgandaHolidays
{
    public static function getHolidays(int $year): array
    {
        return [
            "$year-01-01" => "New Year's Day",
            "$year-01-26" => "Liberation Day",
            "$year-02-16" => "Archbishop Janani Luwum Day",
            "$year-03-08" => "International Women's Day",
            "$year-05-01" => "Labour Day",
            "$year-06-03" => "Martyr's Day (Uganda Martyrs)",
            "$year-06-09" => "National Heroes Day",
            "$year-10-09" => "Independence Day",
            "$year-12-25" => "Christmas Day",
            "$year-12-26" => "Boxing Day",
            // Easter (calculated)
            self::getGoodFriday($year)   => "Good Friday",
            self::getEasterMonday($year) => "Easter Monday",
            // Eid (approximate - adjust yearly)
            "$year-04-10" => "Eid al-Fitr (approximate)",
            "$year-06-17" => "Eid al-Adha (approximate)",
        ];
    }

    public static function isHoliday(string $date): bool
    {
        $year = Carbon::parse($date)->year;
        return array_key_exists($date, self::getHolidays($year));
    }

    public static function getHolidayName(string $date): ?string
    {
        $year = Carbon::parse($date)->year;
        return self::getHolidays($year)[$date] ?? null;
    }

    public static function getUpcoming(int $count = 5): array
    {
        $today = Carbon::today();
        $year  = $today->year;
        $holidays = array_merge(
            self::getHolidays($year),
            self::getHolidays($year + 1)
        );
        ksort($holidays);
        $upcoming = [];
        foreach ($holidays as $date => $name) {
            if (Carbon::parse($date)->gte($today)) {
                $upcoming[$date] = $name;
                if (count($upcoming) >= $count) break;
            }
        }
        return $upcoming;
    }

    private static function getGoodFriday(int $year): string
    {
        $easter = self::easterDate($year);
        return Carbon::parse($easter)->subDays(2)->format('Y-m-d');
    }

    private static function getEasterMonday(int $year): string
    {
        $easter = self::easterDate($year);
        return Carbon::parse($easter)->addDay()->format('Y-m-d');
    }

    private static function easterDate(int $year): string
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
        $day   = (($h + $l - 7 * $m + 114) % 31) + 1;
        return "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    }
}
