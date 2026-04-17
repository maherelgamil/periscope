@extends('periscope::layout')
@section('title', $job->name)
@section('content')
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold">{{ $job->name }}</h1>
            <div class="mt-1 font-mono text-xs text-slate-500">{{ $job->uuid }}</div>
        </div>
        @include('periscope::partials.status-badge', ['status' => $job->status])
    </div>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ([
            'Connection' => $job->connection,
            'Queue' => $job->queue,
            'Attempts' => $job->attempts,
            'Runtime' => $job->runtime_ms !== null ? number_format($job->runtime_ms).' ms' : '—',
            'Wait' => $job->wait_ms !== null ? number_format($job->wait_ms).' ms' : '—',
            'Queued at' => $job->queued_at?->toDateTimeString() ?? '—',
            'Started at' => $job->started_at?->toDateTimeString() ?? '—',
            'Finished at' => $job->finished_at?->toDateTimeString() ?? '—',
        ] as $label => $value)
            <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-4">
                <div class="text-xs uppercase tracking-wide text-slate-400">{{ $label }}</div>
                <div class="mt-1 text-sm text-white">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    @if (! empty($job->tags))
        <div class="mt-6">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-400">Tags</h2>
            <div class="flex flex-wrap gap-1">
                @foreach ($job->tags as $tag)
                    <span class="inline-flex items-center rounded-full bg-slate-800 px-2 py-0.5 text-xs text-slate-200">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if ($job->attempts->isNotEmpty())
        <div class="mt-6">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-400">Attempts</h2>
            <div class="overflow-hidden rounded-xl border border-slate-800">
                <table class="w-full divide-y divide-slate-800 text-left text-sm">
                    <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Started</th>
                            <th class="px-4 py-2">Finished</th>
                            <th class="px-4 py-2">Runtime</th>
                            <th class="px-4 py-2">Exception</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @foreach ($job->attempts as $attempt)
                            <tr>
                                <td class="px-4 py-2 font-medium text-white">{{ $attempt->attempt }}</td>
                                <td class="px-4 py-2">@include('periscope::partials.status-badge', ['status' => $attempt->status])</td>
                                <td class="px-4 py-2 text-slate-400">{{ $attempt->started_at?->toDateTimeString() ?? '—' }}</td>
                                <td class="px-4 py-2 text-slate-400">{{ $attempt->finished_at?->toDateTimeString() ?? '—' }}</td>
                                <td class="px-4 py-2 text-slate-300">{{ $attempt->runtime_ms !== null ? number_format($attempt->runtime_ms).' ms' : '—' }}</td>
                                <td class="px-4 py-2 text-slate-400">
                                    @if ($attempt->exception)
                                        <span class="font-mono text-xs text-rose-300">{{ \Illuminate\Support\Str::limit(strtok($attempt->exception, "\n"), 60) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($job->exception)
        <div class="mt-6">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-rose-300">Exception</h2>
            <pre class="overflow-auto rounded-xl border border-rose-900/50 bg-rose-950/40 p-4 text-xs text-rose-200">{{ $job->exception }}</pre>
        </div>
    @endif

    @if ($job->payload)
        <div class="mt-6">
            <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-slate-400">Payload</h2>
            <pre class="overflow-auto rounded-xl border border-slate-800 bg-slate-900/60 p-4 text-xs text-slate-200">{{ $job->payload }}</pre>
        </div>
    @endif
@endsection
