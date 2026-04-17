<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->schema()->table($this->prefix().'jobs', function (Blueprint $table) {
            $table->string('exception_class', 255)->nullable()->after('exception');
            $table->string('exception_message', 500)->nullable()->after('exception_class');
            $table->index(['exception_class', 'status']);
        });

        $this->schema()->table($this->prefix().'job_attempts', function (Blueprint $table) {
            $table->string('exception_class', 255)->nullable()->after('exception');
            $table->string('exception_message', 500)->nullable()->after('exception_class');
            $table->index(['exception_class', 'status']);
        });
    }

    public function down(): void
    {
        $this->schema()->table($this->prefix().'jobs', function (Blueprint $table) {
            $table->dropIndex(['exception_class', 'status']);
            $table->dropColumn(['exception_class', 'exception_message']);
        });

        $this->schema()->table($this->prefix().'job_attempts', function (Blueprint $table) {
            $table->dropIndex(['exception_class', 'status']);
            $table->dropColumn(['exception_class', 'exception_message']);
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
