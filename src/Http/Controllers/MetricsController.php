<?php

namespace MaherElGamil\Periscope\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use MaherElGamil\Periscope\Support\MetricsCollector;
use MaherElGamil\Periscope\Support\PrometheusFormatter;

class MetricsController extends Controller
{
    public function prometheus(MetricsCollector $collector, PrometheusFormatter $formatter): Response
    {
        return response($formatter->format($collector->collect()), 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    public function json(MetricsCollector $collector)
    {
        return response()->json($collector->collect());
    }
}
