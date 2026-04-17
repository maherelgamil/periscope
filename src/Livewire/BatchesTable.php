<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Bus\BatchRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class BatchesTable extends Component
{
    use WithPagination;

    public function cancel(string $id): void
    {
        $bus = app(BatchRepository::class);
        $batch = $bus->find($id);

        if ($batch !== null) {
            $batch->cancel();
            session()->flash('periscope.message', 'Batch cancelled.');
        }
    }

    public function render()
    {
        $connection = config('queue.batching.database') ?? config('database.default');
        $table = config('queue.batching.table', 'job_batches');

        $exists = Schema::connection($connection)->hasTable($table);

        $batches = collect();

        if ($exists) {
            $rows = DB::connection($connection)
                ->table($table)
                ->orderByDesc('created_at')
                ->paginate(25);

            $batches = $rows;
        }

        return view('periscope::livewire.batches-table', [
            'batches' => $batches,
            'exists' => $exists,
        ]);
    }
}
