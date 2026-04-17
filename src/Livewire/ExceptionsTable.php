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

    public function render()
    {
        $since = now()->subHours(max(1, $this->hours));

        $groups = JobAttempt::query()
            ->where('status', JobAttempt::STATUS_FAILED)
            ->whereNotNull('exception_class')
            ->where('finished_at', '>=', $since)
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
