<div class="space-y-4">
    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-4 py-2">Job</th>
                    <th class="px-4 py-2">Queue</th>
                    <th class="px-4 py-2">Failed</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($jobs as $job)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2">
                            <a href="{{ route('periscope.jobs.show', $job->uuid) }}" class="font-medium text-sky-300 hover:underline">{{ $job->name }}</a>
                            <div class="font-mono text-xs text-slate-500">{{ $job->uuid }}</div>
                        </td>
                        <td class="px-4 py-2 text-slate-300">{{ $job->connection }} / {{ $job->queue }}</td>
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
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No failed jobs. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $jobs->links() }}</div>
</div>
