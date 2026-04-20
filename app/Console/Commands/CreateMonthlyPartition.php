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
        $monthsToCreate = 2; // next 2 months

        foreach (['comparisons', 'comparisons_history'] as $table) {

            for ($i = 0; $i < $monthsToCreate; $i++) {

                // current month start
                $start = Carbon::now()->addMonths($i)->startOfMonth();

                // next month start (partition upper bound)
                $end = $start->copy()->addMonth();

                $partition = 'p' . $start->format('Ym');
                $value = $end->format('Y-m-d H:i:s'); // ✅ correct

                // 🔍 check exists
                $exists = DB::select("
                    SELECT PARTITION_NAME
                    FROM INFORMATION_SCHEMA.PARTITIONS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND PARTITION_NAME = ?
                ", [$table, $partition]);

                if (empty($exists)) {

                    DB::statement("
                        ALTER TABLE $table
                        REORGANIZE PARTITION pmax INTO (
                            PARTITION $partition VALUES LESS THAN ('$value'),
                            PARTITION pmax VALUES LESS THAN (MAXVALUE)
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
