<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-2">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, uuid, exception…"
               class="w-80 rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm placeholder-slate-500 focus:border-sky-500 focus:outline-none">

        <select wire:model.live="queue" class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5 text-sm">
            <option value="">All queues</option>
            @foreach ($queues as $q)
                <option value="{{ $q }}">{{ $q }}</option>
            @endforeach
        </select>

        @if (count($selected) > 0)
            <div class="ml-auto flex items-center gap-2 text-sm">
                <span class="text-slate-400">{{ count($selected) }} selected</span>
                <button wire:click="retrySelected" class="rounded-md bg-sky-500/20 px-3 py-1.5 text-xs font-medium text-sky-300 hover:bg-sky-500/30">Retry all</button>
                <button wire:click="forgetSelected" wire:confirm="Remove selected jobs?" class="rounded-md bg-rose-500/20 px-3 py-1.5 text-xs font-medium text-rose-300 hover:bg-rose-500/30">Forget all</button>
            </div>
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="w-10 px-4 py-2"></th>
                    <th class="px-4 py-2">Job</th>
                    <th class="px-4 py-2">Queue</th>
                    <th class="px-4 py-2">Exception</th>
                    <th class="px-4 py-2">Failed</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($jobs as $job)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2">
                            <input type="checkbox" wire:model.live="selected" value="{{ $job->uuid }}" class="rounded border-slate-600 bg-slate-800">
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('periscope.jobs.show', $job->uuid) }}" class="font-medium text-sky-300 hover:underline">{{ $job->name }}</a>
                            <div class="font-mono text-xs text-slate-500">{{ $job->uuid }}</div>
                        </td>
                        <td class="px-4 py-2 text-slate-300">{{ $job->connection }} / {{ $job->queue }}</td>
                        <td class="px-4 py-2">
                            <div class="text-rose-300">{{ class_basename($job->exception_class ?? '') ?: '—' }}</div>
                            <div class="truncate text-xs text-slate-500" title="{{ $job->exception_message }}">{{ \Illuminate\Support\Str::limit($job->exception_message ?? '', 80) }}</div>
                        </td>
                        <td class="px-4 py-2 text-slate-400">{{ $job->finished_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">
                            <button wire:click="retry('{{ $job->uuid }}')"
                                    class="rounded-md bg-sky-500/20 px-2 py-1 text-xs font-medium text-sky-300 hover:bg-sky-500/30">
                                Retry
                            </button>
                            <button wire:click="forget('{{ $job->uuid }}')"
                                    wire:confirm="Remove this job from Periscope?"
                                    class="rounded-md bg-rose-500/20 px-2 py-1 text-xs font-medium text-rose-300 hover:bg-rose-500/30">
                                Forget
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No failed jobs. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $jobs->links() }}</div>
</div>
