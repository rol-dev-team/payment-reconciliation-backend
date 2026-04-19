<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateMonthlyPartition extends Command
{
    protected $signature = 'partition:create';
    protected $description = 'Create monthly partitions safely from pmax';

    public function handle()
    {
        $monthsToCreate = 2; // create next 2 months

        foreach (['comparisons', 'comparisons_history'] as $table) {

            for ($i = 0; $i < $monthsToCreate; $i++) {

                $date = Carbon::now()->addMonths($i);
                $partition = 'p' . $date->format('Ym');
                $value = $date->format('Ym') + 1;

                // 🔍 check exists
                $exists = DB::select("
                    SELECT PARTITION_NAME
                    FROM INFORMATION_SCHEMA.PARTITIONS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = '$table'
                    AND PARTITION_NAME = '$partition'
                ");

                if (empty($exists)) {

                    DB::statement("
                        ALTER TABLE $table
                        REORGANIZE PARTITION pmax INTO (
                            PARTITION $partition VALUES LESS THAN ($value),
                            PARTITION pmax VALUES LESS THAN MAXVALUE
                        )
                    ");

                    $this->info("✅ Created partition: $partition in $table");

                } else {
                    $this->info("⚠️ Already exists: $partition in $table");
                }
            }
        }

        return Command::SUCCESS;
    }
}
