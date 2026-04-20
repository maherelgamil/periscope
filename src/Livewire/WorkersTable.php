<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Component;
use MaherElGamil\Periscope\Models\Worker;

class WorkersTable extends Component
{
    public function render()
    {
        $groups = Worker::query()
            ->orderByDesc('last_heartbeat_at')
            ->get()
            ->groupBy('hostname');

        return view('periscope::livewire.workers-table', [
            'groups' => $groups,
        ]);
    }
}
