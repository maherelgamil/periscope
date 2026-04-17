<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema()->table($this->prefix().'jobs', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->index()->after('job_id');
        });
    }

    public function down(): void
    {
        $this->schema()->table($this->prefix().'jobs', function (Blueprint $table) {
            $table->dropIndex(['batch_id']);
            $table->dropColumn('batch_id');
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
