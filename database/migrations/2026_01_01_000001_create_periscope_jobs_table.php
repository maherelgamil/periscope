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
            $table->uuid('uuid')->unique();
            $table->string('job_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('connection')->index();
            $table->string('queue')->index();
            $table->string('status', 32)->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedBigInteger('runtime_ms')->nullable();
            $table->unsignedBigInteger('wait_ms')->nullable();
            $table->json('tags')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('exception')->nullable();
            $table->timestamp('queued_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamps();

            $table->index(['status', 'finished_at']);
            $table->index(['queue', 'status']);
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
        return config('periscope.storage.table_prefix', 'periscope_').'jobs';
    }
};
