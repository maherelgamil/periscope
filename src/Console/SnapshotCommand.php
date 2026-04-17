<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MaherElGamil\Periscope\Models\QueueMetric;

class SnapshotCommand extends Command
{
    protected $signature = 'periscope:snapshot
        {--older-than=60 : Minutes after which minute-bucket rows are rolled up into hourly buckets}';

    protected $description = 'Roll per-minute Periscope metrics into hourly buckets';

    public function handle(): int
    {
        $threshold = now()->subMinutes((int) $this->option('older-than'))->startOfMinute();

        $connection = QueueMetric::query()->getModel()->getConnectionName();
        $table = QueueMetric::query()->getModel()->getTable();
        $db = DB::connection($connection);

        $rolled = 0;

        $db->transaction(function () use ($db, $table, $threshold, &$rolled) {
            $minuteRows = $db->table($table)
                ->where('period', 'minute')
                ->where('bucket', '<', $threshold)
                ->get();

            foreach ($minuteRows->groupBy(fn ($row) => sprintf(
                '%s|%s|%s',
                $row->connection,
                $row->queue,
                date('Y-m-d H:00:00', strtotime($row->bucket))
            )) as $key => $rows) {
                [$conn, $queue, $bucket] = explode('|', $key);

                $db->table($table)->updateOrInsert(
                    [
                        'connection' => $conn,
                        'queue' => $queue,
                        'period' => 'hour',
                        'bucket' => $bucket,
                    ],
                    [
                        'queued' => $rows->sum('queued'),
                        'processed' => $rows->sum('processed'),
                        'failed' => $rows->sum('failed'),
                        'runtime_ms_sum' => $rows->sum('runtime_ms_sum'),
                        'wait_ms_sum' => $rows->sum('wait_ms_sum'),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $rolled += $rows->count();
            }

            $db->table($table)
                ->where('period', 'minute')
                ->where('bucket', '<', $threshold)
                ->delete();
        });

        $this->components->info("Rolled {$rolled} minute row(s) into hourly buckets.");

        return self::SUCCESS;
    }
}
