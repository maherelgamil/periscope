<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\MonitoredJob;

class BatchDetail extends Component
{
    use WithPagination;

    public string $batchId = '';

    public function render()
    {
        $connection = config('queue.batching.database') ?? config('database.default');
        $table = config('queue.batching.table', 'job_batches');

        $batch = DB::connection($connection)
            ->table($table)
            ->where('id', $this->batchId)
            ->first();

        $jobs = MonitoredJob::query()
            ->where('batch_id', $this->batchId)
            ->latest('id')
            ->paginate(25);

        return view('periscope::livewire.batch-detail', [
            'batch' => $batch,
            'jobs' => $jobs,
        ]);
    }
}
