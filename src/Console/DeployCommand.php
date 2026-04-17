<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use MaherElGamil\Periscope\Supervisors\Master;

class DeployCommand extends Command
{
    protected $signature = 'periscope:deploy
        {tag? : Optional deploy tag (e.g. git SHA); auto-generated if omitted}';

    protected $description = 'Signal the Periscope master to gracefully restart all workers after a deploy';

    public function handle(): int
    {
        $tag = $this->argument('tag') ?: Str::random(12);

        Cache::forever(Master::DEPLOY_TAG_KEY, $tag);

        $this->components->info("Deploy tag set to [{$tag}] — workers will respawn on the next tick.");

        return self::SUCCESS;
    }
}
