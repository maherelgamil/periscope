<?php

namespace MaherElGamil\Periscope\Console;

use Illuminate\Console\Command;
use MaherElGamil\Periscope\Alerts\AlertManager;

class CheckAlertsCommand extends Command
{
    protected $signature = 'periscope:alerts:check';

    protected $description = 'Evaluate Periscope alert rules and dispatch notifications';

    public function handle(AlertManager $manager): int
    {
        $alerts = $manager->evaluate();

        if ($alerts === []) {
            $this->components->info('No alerts fired.');

            return self::SUCCESS;
        }

        foreach ($alerts as $alert) {
            $this->components->warn("[{$alert->severity}] {$alert->title}: {$alert->message}");
            $manager->dispatch($alert);
        }

        return self::SUCCESS;
    }
}
