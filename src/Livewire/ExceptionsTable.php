<?php

namespace MaherElGamil\Periscope\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use MaherElGamil\Periscope\Models\JobAttempt;

class ExceptionsTable extends Component
{
    #[Url(as: 'hours')]
    public int $hours = 24;

    #[Url(as: 'from')]
    public string $from = '';

    #[Url(as: 'to')]
    public string $to = '';

    public function render()
    {
        $query = JobAttempt::query()
            ->where('status', JobAttempt::STATUS_FAILED)
            ->whereNotNull('exception_class');

        if ($this->from !== '' || $this->to !== '') {
            if ($this->from !== '') {
                $query->where('finished_at', '>=', $this->from);
            }
            if ($this->to !== '') {
                $query->where('finished_at', '<=', $this->to);
            }
        } else {
            $query->where('finished_at', '>=', now()->subHours(max(1, $this->hours)));
        }

        $groups = $query
            ->select([
                'exception_class',
                'exception_message',
                DB::raw('COUNT(*) as occurrences'),
                DB::raw('COUNT(DISTINCT job_uuid) as jobs_affected'),
                DB::raw('MIN(finished_at) as first_seen'),
                DB::raw('MAX(finished_at) as last_seen'),
            ])
            ->groupBy('exception_class', 'exception_message')
            ->orderByDesc('occurrences')
            ->limit(100)
            ->get();

        return view('periscope::livewire.exceptions-table', [
            'groups' => $groups,
        ]);
    }
}
