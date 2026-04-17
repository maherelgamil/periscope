<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\MonitoredJob;

class FailedJobsTable extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'queue')]
    public string $queue = '';

    /** @var array<int, string> */
    public array $selected = [];

    public function updating(): void
    {
        $this->resetPage();
    }

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
        $this->selected = array_values(array_diff($this->selected, [$uuid]));

        session()->flash('periscope.message', 'Failed job removed.');
    }

    public function retrySelected(): void
    {
        $jobIds = MonitoredJob::query()
            ->whereIn('uuid', $this->selected)
            ->whereNotNull('job_id')
            ->pluck('job_id')
            ->all();

        if ($jobIds === []) {
            return;
        }

        Artisan::call('queue:retry', ['id' => $jobIds]);
        $count = count($jobIds);
        $this->selected = [];

        session()->flash('periscope.message', "Retried {$count} job(s).");
    }

    public function forgetSelected(): void
    {
        $jobs = MonitoredJob::query()->whereIn('uuid', $this->selected)->get();

        foreach ($jobs as $job) {
            if ($job->job_id !== null) {
                Artisan::call('queue:forget', ['id' => $job->job_id]);
            }
            $job->delete();
        }

        $count = $jobs->count();
        $this->selected = [];

        session()->flash('periscope.message', "Removed {$count} job(s).");
    }

    public function render()
    {
        $query = MonitoredJob::query()
            ->where('status', MonitoredJob::STATUS_FAILED)
            ->latest('finished_at');

        if ($this->queue !== '') {
            $query->where('queue', $this->queue);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('uuid', $this->search)
                    ->orWhere('job_id', $this->search)
                    ->orWhere('exception_class', 'like', '%'.$this->search.'%')
                    ->orWhere('exception_message', 'like', '%'.$this->search.'%');
            });
        }

        return view('periscope::livewire.failed-jobs-table', [
            'jobs' => $query->paginate(25),
            'queues' => MonitoredJob::query()
                ->where('status', MonitoredJob::STATUS_FAILED)
                ->select('queue')
                ->distinct()
                ->pluck('queue'),
        ]);
    }
}
