<?php

namespace MaherElGamil\Periscope\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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

    public function queues()
    {
        return view('periscope::queues');
    }
}
