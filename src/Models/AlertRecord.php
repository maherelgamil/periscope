<?php

namespace MaherElGamil\Periscope\Models;

use Illuminate\Database\Eloquent\Model;

class AlertRecord extends Model
{
    protected $table;

    protected $guarded = [];

    protected $casts = [
        'context' => 'array',
        'channels' => 'array',
        'fired_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('periscope.storage.table_prefix', 'periscope_').'alerts';

        parent::__construct($attributes);
    }

    public function getConnectionName(): ?string
    {
        return config('periscope.storage.connection') ?? parent::getConnectionName();
    }
}
