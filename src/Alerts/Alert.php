<?php

namespace MaherElGamil\Periscope\Alerts;

class Alert
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $message,
        public readonly string $severity = 'warning',
        public readonly array $context = [],
    ) {}
}
