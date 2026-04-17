<div class="space-y-4">
    <div class="flex flex-wrap gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, uuid, job id…"
               class="w-64 rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm placeholder-slate-500 focus:border-sky-500 focus:outline-none">

        <select wire:model.live="status" class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm">
            <option value="">All statuses</option>
            <option value="queued">Queued</option>
            <option value="running">Running</option>
            <option value="completed">Completed</option>
            <option value="failed">Failed</option>
        </select>

        <select wire:model.live="queue" class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm">
            <option value="">All queues</option>
            @foreach ($queues as $q)
                <option value="{{ $q }}">{{ $q }}</option>
            @endforeach
        </select>

        <input type="text" wire:model.live.debounce.300ms="tag" placeholder="Tag"
               class="w-40 rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm placeholder-slate-500 focus:border-sky-500 focus:outline-none">

        <input type="datetime-local" wire:model.live="from" title="Queued on or after"
               class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
        <input type="datetime-local" wire:model.live="to" title="Queued on or before"
               class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-4 py-2">Job</th>
                    <th class="px-4 py-2">Queue</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Attempts</th>
                    <th class="px-4 py-2">Runtime</th>
                    <th class="px-4 py-2">Finished</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($jobs as $job)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2">
                            <a href="{{ route('periscope.jobs.show', $job->uuid) }}" class="font-medium text-sky-300 hover:underline">
                                {{ $job->name }}
                            </a>
                            <div class="font-mono text-xs text-slate-500">{{ $job->uuid }}</div>
                        </td>
                        <td class="px-4 py-2 text-slate-300">{{ $job->connection }} / {{ $job->queue }}</td>
                        <td class="px-4 py-2">
                            @include('periscope::partials.status-badge', ['status' => $job->status])
                        </td>
                        <td class="px-4 py-2 text-slate-300">{{ $job->attempts }}</td>
                        <td class="px-4 py-2 text-slate-300">{{ $job->runtime_ms !== null ? number_format($job->runtime_ms).' ms' : '—' }}</td>
                        <td class="px-4 py-2 text-slate-400">{{ $job->finished_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center">
                            <div class="text-slate-400">No jobs match your filters.</div>
                            <div class="mt-1 text-xs text-slate-500">Dispatch a queued job or adjust the filters above.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $jobs->links() }}</div>
</div>
