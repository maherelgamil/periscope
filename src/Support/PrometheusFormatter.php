<?php

namespace MaherElGamil\Periscope\Support;

class PrometheusFormatter
{
    public function format(array $data): string
    {
        $lines = [];

        $lines[] = '# HELP periscope_jobs_processed_total Jobs processed successfully';
        $lines[] = '# TYPE periscope_jobs_processed_total counter';
        foreach ($data['jobs'] as $row) {
            $lines[] = $this->metric('periscope_jobs_processed_total', $row['processed'], [
                'connection' => $row['connection'], 'queue' => $row['queue'],
            ]);
        }

        $lines[] = '# HELP periscope_jobs_failed_total Jobs that failed';
        $lines[] = '# TYPE periscope_jobs_failed_total counter';
        foreach ($data['jobs'] as $row) {
            $lines[] = $this->metric('periscope_jobs_failed_total', $row['failed'], [
                'connection' => $row['connection'], 'queue' => $row['queue'],
            ]);
        }

        $lines[] = '# HELP periscope_jobs_queued_total Jobs pushed onto a queue';
        $lines[] = '# TYPE periscope_jobs_queued_total counter';
        foreach ($data['jobs'] as $row) {
            $lines[] = $this->metric('periscope_jobs_queued_total', $row['queued'], [
                'connection' => $row['connection'], 'queue' => $row['queue'],
            ]);
        }

        $lines[] = '# HELP periscope_runtime_ms_sum Cumulative job runtime in milliseconds';
        $lines[] = '# TYPE periscope_runtime_ms_sum counter';
        foreach ($data['runtime'] as $row) {
            $lines[] = $this->metric('periscope_runtime_ms_sum', $row['runtime_ms_sum'], [
                'connection' => $row['connection'], 'queue' => $row['queue'],
            ]);
        }

        $lines[] = '# HELP periscope_wait_ms_sum Cumulative queue wait time in milliseconds';
        $lines[] = '# TYPE periscope_wait_ms_sum counter';
        foreach ($data['runtime'] as $row) {
            $lines[] = $this->metric('periscope_wait_ms_sum', $row['wait_ms_sum'], [
                'connection' => $row['connection'], 'queue' => $row['queue'],
            ]);
        }

        $lines[] = '# HELP periscope_queue_pending Jobs currently ready to be processed';
        $lines[] = '# TYPE periscope_queue_pending gauge';
        foreach ($data['queues'] as $row) {
            if ($row['pending'] !== null) {
                $lines[] = $this->metric('periscope_queue_pending', $row['pending'], [
                    'connection' => $row['connection'], 'queue' => $row['queue'],
                ]);
            }
        }

        $lines[] = '# HELP periscope_queue_delayed Jobs scheduled for the future';
        $lines[] = '# TYPE periscope_queue_delayed gauge';
        foreach ($data['queues'] as $row) {
            if ($row['delayed'] !== null) {
                $lines[] = $this->metric('periscope_queue_delayed', $row['delayed'], [
                    'connection' => $row['connection'], 'queue' => $row['queue'],
                ]);
            }
        }

        $lines[] = '# HELP periscope_queue_reserved Jobs claimed by a worker';
        $lines[] = '# TYPE periscope_queue_reserved gauge';
        foreach ($data['queues'] as $row) {
            if ($row['reserved'] !== null) {
                $lines[] = $this->metric('periscope_queue_reserved', $row['reserved'], [
                    'connection' => $row['connection'], 'queue' => $row['queue'],
                ]);
            }
        }

        $lines[] = '# HELP periscope_workers Workers by status';
        $lines[] = '# TYPE periscope_workers gauge';
        foreach ($data['workers'] as $status => $count) {
            $lines[] = $this->metric('periscope_workers', $count, ['status' => $status]);
        }

        $lines[] = '# HELP periscope_jobs_current Monitored jobs by current status';
        $lines[] = '# TYPE periscope_jobs_current gauge';
        foreach ($data['jobs_current'] as $status => $count) {
            $lines[] = $this->metric('periscope_jobs_current', $count, ['status' => $status]);
        }

        return implode("\n", $lines)."\n";
    }

    protected function metric(string $name, int $value, array $labels): string
    {
        $parts = [];

        foreach ($labels as $key => $val) {
            $parts[] = sprintf('%s="%s"', $key, addslashes((string) $val));
        }

        return sprintf('%s{%s} %d', $name, implode(',', $parts), $value);
    }
}
