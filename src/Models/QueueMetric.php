<?php

namespace MaherElGamil\Periscope\Models;

use Illuminate\Database\Eloquent\Model;

class QueueMetric extends Model
{
    protected $guarded = [];

    protected $casts = [
        'bucket' => 'datetime',
        'queued' => 'integer',
        'processed' => 'integer',
        'failed' => 'integer',
        'runtime_ms_sum' => 'integer',
        'wait_ms_sum' => 'integer',
    ];

    public function getTable(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_').'metrics';
    }

    public function getConnectionName(): ?string
    {
        return config('periscope.storage.connection') ?? parent::getConnectionName();
    }
}
