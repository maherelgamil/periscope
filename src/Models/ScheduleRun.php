<?php

namespace MaherElGamil\Periscope\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRun extends Model
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $guarded = [];

    protected $casts = [
        'runtime_ms' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_').'schedules';
    }

    public function getConnectionName(): ?string
    {
        return config('periscope.storage.connection') ?? parent::getConnectionName();
    }
}
