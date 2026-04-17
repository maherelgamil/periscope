<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema()->create($this->table(), function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hostname')->nullable();
            $table->unsignedInteger('pid')->nullable();
            $table->string('connection')->nullable();
            $table->json('queues')->nullable();
            $table->string('status', 32)->default('running')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema()->dropIfExists($this->table());
    }

    protected function schema()
    {
        return Schema::connection(config('periscope.storage.connection'));
    }

    protected function table(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_').'workers';
    }
};
