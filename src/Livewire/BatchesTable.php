<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Bus\BatchRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class BatchesTable extends Component
{
    use WithPagination;

    public function cancel(string $id): void
    {
        $batch = app(BatchRepository::class)->find($id);

        if ($batch !== null) {
            $batch->cancel();
            session()->flash('periscope.message', 'Batch cancelled.');
        }
    }

    public function retryFailed(string $id): void
    {
        $batch = app(BatchRepository::class)->find($id);

        if ($batch === null) {
            return;
        }

        $failed = (array) ($batch->failedJobIds ?? []);

        if ($failed === []) {
            session()->flash('periscope.message', 'No failed jobs to retry.');

            return;
        }

        foreach ($failed as $failedId) {
            Artisan::call('queue:retry', ['id' => [$failedId]]);
        }

        $count = count($failed);
        session()->flash('periscope.message', "Retried {$count} failed job(s) in batch.");
    }

    public function delete(string $id): void
    {
        $connection = config('queue.batching.database') ?? config('database.default');
        $table = config('queue.batching.table', 'job_batches');

        DB::connection($connection)->table($table)->where('id', $id)->delete();

        session()->flash('periscope.message', 'Batch record removed.');
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
