<?php

namespace MaherElGamil\Periscope;

use Illuminate\Support\ServiceProvider;

abstract class PeriscopeApplicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->gate();
    }

    /**
     * Define the Periscope authorization gate. Override in the app-side
     * provider to decide who can access the dashboard outside of local.
     */
    abstract protected function gate(): void;
}
