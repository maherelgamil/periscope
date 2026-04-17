<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\MonitoredJob;

class FailedJobsTable extends Component
{
    use WithPagination;

    public function retry(string $uuid): void
    {
        $job = MonitoredJob::query()->where('uuid', $uuid)->first();

        if ($job === null || $job->job_id === null) {
            return;
        }

        Artisan::call('queue:retry', ['id' => [$job->job_id]]);

        session()->flash('periscope.message', "Retry dispatched for job {$job->job_id}.");
    }

    public function forget(string $uuid): void
    {
        $job = MonitoredJob::query()->where('uuid', $uuid)->first();

        if ($job === null) {
            return;
        }

        if ($job->job_id !== null) {
            Artisan::call('queue:forget', ['id' => $job->job_id]);
        }

        $job->delete();

        session()->flash('periscope.message', 'Failed job removed.');
    }

    public function render()
    {
        return view('periscope::livewire.failed-jobs-table', [
            'jobs' => MonitoredJob::query()
                ->where('status', MonitoredJob::STATUS_FAILED)
                ->latest('finished_at')
                ->paginate(25),
        ]);
    }
}
