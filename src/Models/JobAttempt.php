<?php

namespace MaherElGamil\Periscope\Models;

use Illuminate\Database\Eloquent\Model;

class JobAttempt extends Model
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'attempt' => 'integer',
        'runtime_ms' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_').'job_attempts';
    }

    public function getConnectionName(): ?string
    {
        return config('periscope.storage.connection') ?? parent::getConnectionName();
    }

    public function job()
    {
        return $this->belongsTo(MonitoredJob::class, 'job_uuid', 'uuid');
    }
}
