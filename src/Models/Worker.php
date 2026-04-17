<?php

namespace MaherElGamil\Periscope\Models;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_STOPPED = 'stopped';

    public const STATUS_STALE = 'stale';

    protected $guarded = [];

    protected $casts = [
        'queues' => 'array',
        'pid' => 'integer',
        'started_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_').'workers';
    }

    public function getConnectionName(): ?string
    {
        return config('periscope.storage.connection') ?? parent::getConnectionName();
    }
}
