<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use MaherElGamil\Periscope\Models\MonitoredJob;

class ForgetCommand extends Command
{
    protected $signature = 'periscope:forget
        {--tag=* : Remove jobs carrying any of these tags}
        {--name= : Remove jobs whose class name matches this value (wildcards allowed)}
        {--status= : Only remove jobs in this status (queued, running, completed, failed)}
        {--dry-run : Show the count without deleting}';

    protected $description = 'Bulk-remove monitored jobs matching the given filters';

    public function handle(): int
    {
        $query = MonitoredJob::query();

        foreach ((array) $this->option('tag') as $tag) {
            $query->where('tags', 'like', '%"'.$tag.'"%');
        }

        if ($name = $this->option('name')) {
            $like = str_replace(['*', '?'], ['%', '_'], $name);
            $query->where('name', 'like', $like);
        }

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        if ($query->toBase()->wheres === []) {
            $this->components->error('Refusing to delete every job — pass at least one filter (--tag, --name, --status).');

            return self::FAILURE;
        }

        $count = $query->count();

        if ($count === 0) {
            $this->components->info('No jobs matched.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->components->info("Would delete {$count} job(s).");

            return self::SUCCESS;
        }

        $query->delete();
        $this->components->info("Deleted {$count} job(s).");

        return self::SUCCESS;
    }
}
