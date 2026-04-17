<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema()->table($this->prefix().'jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('memory_peak_bytes')->nullable()->after('runtime_ms');
        });

        $this->schema()->table($this->prefix().'job_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('memory_peak_bytes')->nullable()->after('runtime_ms');
        });
    }

    public function down(): void
    {
        $this->schema()->table($this->prefix().'jobs', function (Blueprint $table) {
            $table->dropColumn('memory_peak_bytes');
        });

        $this->schema()->table($this->prefix().'job_attempts', function (Blueprint $table) {
            $table->dropColumn('memory_peak_bytes');
        });
    }

    protected function schema()
    {
        return Schema::connection(config('periscope.storage.connection'));
    }

    protected function prefix(): string
    {
        return config('periscope.storage.table_prefix', 'periscope_');
    }
};
