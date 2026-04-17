<?php

namespace MaherElGamil\Periscope\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoredJob extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'attempts' => 'integer',
        'runtime_ms' => 'integer',
        'wait_ms' => 'integer',
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_').'jobs';
    }

    public function getConnectionName(): ?string
    {
        return config('periscope.storage.connection') ?? parent::getConnectionName();
    }

    public function history()
    {
        return $this->hasMany(JobAttempt::class, 'job_uuid', 'uuid')->orderBy('attempt');
    }
}
