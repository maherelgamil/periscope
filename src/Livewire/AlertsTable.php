<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\AlertRecord;

class AlertsTable extends Component
{
    use WithPagination;

    public function delete(int $id): void
    {
        AlertRecord::query()->whereKey($id)->delete();

        session()->flash('periscope.message', 'Alert record removed.');
    }

    public function render()
    {
        return view('periscope::livewire.alerts-table', [
            'alerts' => AlertRecord::query()->latest('fired_at')->paginate(25),
        ]);
    }
}
