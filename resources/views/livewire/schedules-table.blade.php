<div class="space-y-4">
    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-4 py-2">Command</th>
                    <th class="px-4 py-2">Expression</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Runtime</th>
                    <th class="px-4 py-2">Started</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($runs as $run)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2">
                            <div class="font-mono text-xs text-slate-200">{{ $run->command }}</div>
                            @if ($run->exception)
                                <div class="mt-1 truncate text-xs text-rose-300">{{ \Illuminate\Support\Str::limit(strtok($run->exception, "\n"), 80) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-mono text-xs text-slate-400">{{ $run->expression ?? '—' }}</td>
                        <td class="px-4 py-2">@include('periscope::partials.status-badge', ['status' => $run->status])</td>
                        <td class="px-4 py-2 text-slate-300">{{ $run->runtime_ms !== null ? number_format($run->runtime_ms).' ms' : '—' }}</td>
                        <td class="px-4 py-2 text-slate-400">{{ $run->started_at?->diffForHumans() ?? $run->finished_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No scheduled command runs recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $runs->links() }}</div>
</div>
