<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CalendarTransformer{
     /**
     * Get calendar metadata based on a Gregorian (A.D.) date range.
     */
    public static function getMetadataByEnglishRange($startDate, $endDate)
    {
        $cacheKey = "calendar_en_range_" . md5($startDate . $endDate);

        return Cache::rememberForever($cacheKey, function () use ($startDate, $endDate) {
            $dates = DB::table('nepali_dates')
                ->whereBetween('english_date', [$startDate, $endDate])
                ->orderBy('english_date', 'asc')
                ->get();

            return static::transformToYearlyFormat($dates);
        });
    }

     /**
     * Get calendar metadata based on a Bikram Sambat (B.S.) year range.
     */
    public static function getMetadataByNepaliYearRange($startYear, $endYear)
    {
        $cacheKey = "calendar_np_range_{$startYear}_{$endYear}";

        return Cache::rememberForever($cacheKey, function () use ($startYear, $endYear) {
            $dates = DB::table('nepali_dates')
                ->whereBetween('nepali_date', ["{$startYear}-01-01", "{$endYear}-12-31"])
                ->orderBy('english_date', 'asc')
                ->get();

            return static::transformToYearlyFormat($dates);
        });
    }

     /**
     * Transforms a flat collection of dates into a compact yearly metadata structure.
     */
    public static function transformToYearlyFormat($dates)
    {
        if ($dates->isEmpty()) return [];

        return $dates->groupBy(function ($item) {
            return substr($item->nepali_date, 0, 4); // Group by Year (YYYY)
        })->map(function ($yearGroup, $year) {
            $months = [];
            
            // Group days within the year by Month
            $groupedByMonth = $yearGroup->groupBy(function ($item) {
                return substr($item->nepali_date, 5, 2); // Group by Month (MM)
            });

            foreach ($groupedByMonth as $monthGroup) {
                $months[] = [
                    $monthGroup->count(),             // Days in this month
                    $monthGroup->first()->english_date // The Gregorian start date
                ];
            }

            return [
                'y' => (int) $year,
                'months' => $months
            ];
        })->values();
    }
}
