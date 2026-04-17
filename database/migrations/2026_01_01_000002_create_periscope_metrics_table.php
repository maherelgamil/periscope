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
            $table->string('connection')->index();
            $table->string('queue')->index();
            $table->string('period', 16)->index();
            $table->timestamp('bucket')->index();
            $table->unsignedBigInteger('queued')->default(0);
            $table->unsignedBigInteger('processed')->default(0);
            $table->unsignedBigInteger('failed')->default(0);
            $table->unsignedBigInteger('runtime_ms_sum')->default(0);
            $table->unsignedBigInteger('wait_ms_sum')->default(0);
            $table->timestamps();

            $table->unique(['connection', 'queue', 'period', 'bucket'], 'periscope_metrics_unique');
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
        return config('periscope.storage.table_prefix', 'periscope_').'metrics';
    }
};
