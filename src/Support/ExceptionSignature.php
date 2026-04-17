<?php

namespace MaherElGamil\Periscope\Support;

use Throwable;

class ExceptionSignature
{
    public static function fromThrowable(?Throwable $e): array
    {
        if ($e === null) {
            return ['class' => null, 'message' => null];
        }

        return [
            'class' => self::truncate(get_class($e), 255),
            'message' => self::truncate((string) $e->getMessage(), 500),
        ];
    }

    public static function fromTraceString(?string $trace): array
    {
        if ($trace === null || $trace === '') {
            return ['class' => null, 'message' => null];
        }

        // First line of a PHP exception string looks like:
        //   RuntimeException: message goes here in /path/file.php:42
        $firstLine = strtok($trace, "\n");

        if (! preg_match('/^([\\\\A-Za-z0-9_]+):\s*(.+?)(?:\s+in\s+\S+:\d+)?$/', (string) $firstLine, $m)) {
            return ['class' => null, 'message' => self::truncate((string) $firstLine, 500)];
        }

        return [
            'class' => self::truncate(ltrim($m[1], '\\'), 255),
            'message' => self::truncate(trim($m[2]), 500),
        ];
    }

    protected static function truncate(string $value, int $max): string
    {
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }
}
