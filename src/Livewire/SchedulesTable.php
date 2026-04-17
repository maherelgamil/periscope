<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\ScheduleRun;

class SchedulesTable extends Component
{
    use WithPagination;

    public function render()
    {
        return view('periscope::livewire.schedules-table', [
            'runs' => ScheduleRun::query()->latest('started_at')->paginate(25),
        ]);
    }
}
