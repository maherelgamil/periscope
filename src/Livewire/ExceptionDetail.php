<?php

namespace MaherElGamil\Periscope\Livewire;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use MaherElGamil\Periscope\Models\JobAttempt;

class ExceptionDetail extends Component
{
    use WithPagination;

    public string $class = '';

    public string $message = '';

    #[Url(as: 'hours')]
    public int $hours = 24;

    public function render()
    {
        $since = now()->subHours(max(1, $this->hours));

        $query = JobAttempt::query()
            ->where('status', JobAttempt::STATUS_FAILED)
            ->where('exception_class', $this->class)
            ->where('exception_message', $this->message)
            ->where('finished_at', '>=', $since)
            ->latest('finished_at');

        $total = (clone $query)->count();
        $distinct = (clone $query)->distinct('job_uuid')->count('job_uuid');
        $sample = (clone $query)->first();

        return view('periscope::livewire.exception-detail', [
            'attempts' => $query->paginate(25),
            'total' => $total,
            'distinct' => $distinct,
            'sample' => $sample,
        ]);
    }
}
