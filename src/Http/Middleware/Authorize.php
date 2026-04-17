<?php

namespace MaherElGamil\Periscope\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Authorize
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Gate::check('viewPeriscope', [$request->user()]), 403);

        return $next($request);
    }
}
