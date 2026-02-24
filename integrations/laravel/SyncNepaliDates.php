<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class SyncNepaliDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opennp:sync-dates {year : The B.S. year to fetch (e.g. 1976)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and seed Bikram Sambat dates from the OpenNP repository for a given year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year');
        
        // Always use the raw content URL for JSON fetching
        $url = "https://raw.githubusercontent.com/opennp/nepali-dates/main/dates/{$year}.json";

        $this->info("Fetching data for B.S. {$year} from OpenNP...");

        try {
            // Failsafe 1: Timeout to prevent hanging if GitHub is down
            $response = Http::timeout(15)->get($url);

            if ($response->failed()) {
                $this->error("Failed to fetch data. Are you sure B.S. {$year} is published? (HTTP {$response->status()})");
                return self::FAILURE;
            }

            $dates = $response->json();

            // Failsafe 2: Validate the payload isn't empty or malformed
            if (empty($dates) || !is_array($dates)) {
                $this->error("The fetched JSON is empty or invalid.");
                return self::FAILURE;
            }

            $this->info("Fetched " . count($dates) . " records. Syncing to database...");

            // Failsafe 3: Strip the JSON 'id' so we don't mess with our local auto-incrementing primary keys
            $payload = collect($dates)->map(function ($date) {
                return [
                    'english_date' => $date['english_date'],
                    'nepali_date'  => $date['nepali_date'],
                ];
            });

            // Failsafe 4: Use transactions so a partial failure rolls back everything
            DB::beginTransaction();

            // Failsafe 5: Chunk the upserts to prevent memory exhaustion or MySQL "packet too large" errors
            $payload->chunk(500)->each(function ($chunk) {
                DB::table('nepali_dates')->upsert(
                    $chunk->toArray(),
                    ['english_date'], // The unique column to check for conflicts
                    ['nepali_date']   // The column to update if a conflict exists
                );
            });

            DB::commit();

            $this->info("Successfully synced B.S. {$year} dates!");
            return self::SUCCESS;

        } catch (Exception $e) {
            DB::rollBack();
            $this->error("A critical error occurred during the sync process:");
            $this->line($e->getMessage());
            return self::FAILURE;
        }
    }
}