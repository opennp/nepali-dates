<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;

class NepaliDate
{
    protected string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public static function from(string $date): self
    {
        return new self($date);
    }

    /**
     * Convert English date to Nepali date.
     * Maps `english_date` to `nepali_date` using the `nepali_dates` table.
     */
    public function toNepaliDate(): string
    {
        $record = DB::table('nepali_dates')
            ->where('english_date', $this->date)
            ->first();

        if (!$record) {
            throw new Exception("Date {$this->date} is out of mapped range.");
        }

        return $record->nepali_date;
    }

    /**
     * Convert English date to Nepali date and return as an array.
     */
    public function toNepaliDateArray(): array
    {
        $nepaliDate = $this->toNepaliDate();

        $parts = explode('-', $nepaliDate);

        return [
            'year'  => $parts[0] ?? null,
            'month' => $parts[1] ?? null,
            'day'   => $parts[2] ?? null,
            'formatted' => $nepaliDate,
        ];
    }

    /**
     * Convert Nepali date to English date.
     * Maps `nepali_date` to `english_date` using the `nepali_dates` table.
     */
    public function toEnglishDate(): string
    {
        $record = DB::table('nepali_dates')
            ->where('nepali_date', $this->date)
            ->first();

        if (!$record) {
            throw new Exception("Date {$this->date} is out of mapped range.");
        }

        return $record->english_date;
    }

    /**
     * Convert Nepali date to English date and return as an array.
     */
    public function toEnglishDateArray(): array
    {
        $englishDate = $this->toEnglishDate();

        $parts = explode('-', $englishDate);

        return [
            'year'  => $parts[0] ?? null,
            'month' => $parts[1] ?? null,
            'day'   => $parts[2] ?? null,
            'formatted' => $englishDate,
        ];
    }

    /**
     * Get the total days in a specific Nepali month and year.
     * Uses simple SQL count since we have a daily mapped table.
     */
    public static function daysInMonth(int $month, int $year): int
    {
        $monthStr = str_pad((string)$month, 2, '0', STR_PAD_LEFT);

        return DB::table('nepali_dates')
            ->where('nepali_date', 'like', "{$year}-{$monthStr}-%")
            ->count();
    }

    /**
     * Get the total days in a specific Nepali year.
     * Uses simple SQL count since we have a daily mapped table.
     */
    public static function daysInYear(int $year): int
    {
        return DB::table('nepali_dates')
            ->where('nepali_date', 'like', "{$year}-%")
            ->count();
    }
}
