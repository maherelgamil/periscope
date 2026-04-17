<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-300">
        <div>
            <span class="text-slate-500">Window:</span>
            <select wire:model.live="hours" class="ml-1 rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs">
                <option value="1">Last hour</option>
                <option value="6">Last 6 hours</option>
                <option value="24">Last 24 hours</option>
                <option value="168">Last 7 days</option>
            </select>
        </div>
        <div><span class="text-slate-500">Occurrences:</span> <span class="font-medium text-white">{{ number_format($total) }}</span></div>
        <div><span class="text-slate-500">Distinct jobs:</span> <span class="font-medium text-white">{{ number_format($distinct) }}</span></div>
    </div>

    @if ($sample && $sample->exception)
        <div class="rounded-xl border border-rose-900/40 bg-rose-950/30 p-4">
            <div class="mb-2 text-xs uppercase tracking-wide text-rose-300">Sample stack trace</div>
            <pre class="max-h-96 overflow-auto text-xs text-rose-200">{{ $sample->exception }}</pre>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-4 py-2">Job</th>
                    <th class="px-4 py-2">Attempt</th>
                    <th class="px-4 py-2">Runtime</th>
                    <th class="px-4 py-2">Failed</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($attempts as $attempt)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2">
                            <a href="{{ route('periscope.jobs.show', $attempt->job_uuid) }}" class="font-mono text-xs text-sky-300 hover:underline">{{ $attempt->job_uuid }}</a>
                        </td>
                        <td class="px-4 py-2 text-slate-300">#{{ $attempt->attempt }}</td>
                        <td class="px-4 py-2 text-slate-300">{{ $attempt->runtime_ms !== null ? number_format($attempt->runtime_ms).' ms' : '—' }}</td>
                        <td class="px-4 py-2 text-slate-400">{{ $attempt->finished_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No occurrences in this window.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $attempts->links() }}</div>
</div>
