<?php

namespace MaherElGamil\Periscope\Alerts;

interface Rule
{
    /**
     * Evaluate the rule and return an Alert if the condition is met.
     */
    public function evaluate(array $config): ?Alert;
}
