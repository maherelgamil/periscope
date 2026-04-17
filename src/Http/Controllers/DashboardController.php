<?php

namespace MaherElGamil\Periscope\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use MaherElGamil\Periscope\Models\MonitoredJob;
use MaherElGamil\Periscope\Repositories\JobRepository;

class DashboardController extends Controller
{
    public function overview()
    {
        return view('periscope::overview');
    }

    public function jobs(Request $request)
    {
        return view('periscope::jobs', [
            'filters' => $request->only(['status', 'queue', 'search']),
        ]);
    }

    public function job(string $uuid, JobRepository $jobs)
    {
        $job = $jobs->findByUuid($uuid);

        abort_if($job === null, 404);

        $job->load('history');

        return view('periscope::job', ['job' => $job]);
    }

    public function failed()
    {
        return view('periscope::failed');
    }

    public function workers()
    {
        return view('periscope::workers');
    }

    public function exceptions()
    {
        return view('periscope::exceptions');
    }

    public function alerts()
    {
        return view('periscope::alerts');
    }

    public function schedules()
    {
        return view('periscope::schedules');
    }

    public function exception(Request $request)
    {
        $class = (string) $request->query('class', '');
        $message = (string) $request->query('message', '');

        abort_if($class === '', 404);

        return view('periscope::exception', [
            'class' => $class,
            'message' => $message,
        ]);
    }

    public function retry(string $uuid, Request $request, JobRepository $jobs)
    {
        $job = $jobs->findByUuid($uuid);

        abort_if($job === null, 404);

        if ($job->status === MonitoredJob::STATUS_FAILED && $job->job_id !== null) {
            Artisan::call('queue:retry', ['id' => [$job->job_id]]);
            $message = 'Retry dispatched.';
        } elseif ($job->payload !== null) {
            Queue::connection($job->connection)->pushRaw($job->payload, $job->queue);
            $message = 'Job re-dispatched with original payload.';
        } else {
            $message = 'Cannot retry — payload is missing.';
        }

        return redirect()
            ->route('periscope.jobs.show', $job->uuid)
            ->with('periscope.message', $message);
    }

    public function queues()
    {
        return view('periscope::queues');
    }

    public function batches()
    {
        return view('periscope::batches');
    }

    public function batch(string $id)
    {
        return view('periscope::batch', ['id' => $id]);
    }

    public function performance()
    {
        return view('periscope::performance');
    }
}
