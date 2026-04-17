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
            $table->string('command');
            $table->string('expression', 100)->nullable();
            $table->string('status', 32)->index();
            $table->unsignedBigInteger('runtime_ms')->nullable();
            $table->text('output')->nullable();
            $table->text('exception')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamps();

            $table->index(['command', 'finished_at']);
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
        return config('periscope.storage.table_prefix', 'periscope_').'schedules';
    }
};
