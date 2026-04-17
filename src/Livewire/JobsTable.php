<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\MonitoredJob;

class JobsTable extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'queue')]
    public string $queue = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'tag')]
    public string $tag = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = MonitoredJob::query()->latest('id');

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->queue !== '') {
            $query->where('queue', $this->queue);
        }

        if ($this->tag !== '') {
            $query->where('tags', 'like', '%"'.$this->tag.'"%');
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('uuid', $this->search)
                    ->orWhere('job_id', $this->search);
            });
        }

        return view('periscope::livewire.jobs-table', [
            'jobs' => $query->paginate(25),
            'queues' => MonitoredJob::query()->select('queue')->distinct()->pluck('queue'),
        ]);
    }
}
