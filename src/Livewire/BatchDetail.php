<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Bus\BatchRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\MonitoredJob;

class BatchDetail extends Component
{
    use WithPagination;

    public string $batchId = '';

    public function cancel(): void
    {
        $batch = app(BatchRepository::class)->find($this->batchId);

        if ($batch !== null) {
            $batch->cancel();
            session()->flash('periscope.message', 'Batch cancelled.');
        }
    }

    public function retryFailed(): void
    {
        $batch = app(BatchRepository::class)->find($this->batchId);
        $failed = (array) ($batch?->failedJobIds ?? []);

        if ($failed === []) {
            session()->flash('periscope.message', 'No failed jobs to retry.');

            return;
        }

        foreach ($failed as $failedId) {
            Artisan::call('queue:retry', ['id' => [$failedId]]);
        }

        session()->flash('periscope.message', count($failed).' failed job(s) retried.');
    }

    public function delete()
    {
        $connection = config('queue.batching.database') ?? config('database.default');
        $table = config('queue.batching.table', 'job_batches');

        DB::connection($connection)->table($table)->where('id', $this->batchId)->delete();

        return $this->redirectRoute('periscope.batches', navigate: true);
    }

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
