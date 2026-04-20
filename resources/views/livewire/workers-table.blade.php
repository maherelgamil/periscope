<div wire:poll.visible.10s class="space-y-4">
    @forelse ($groups as $hostname => $workers)
        @php($hasRunning = $workers->contains('status', \MaherElGamil\Periscope\Models\Worker::STATUS_RUNNING))
        <div class="overflow-hidden rounded-xl border border-slate-800">
            <div class="flex items-center justify-between border-b border-slate-800 bg-slate-900/60 px-4 py-3">
                <span class="font-medium text-white">{{ $hostname ?? 'Unknown host' }}</span>
                @if ($hasRunning)
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="shrink-0 text-emerald-400">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12l3 3 5-5" />
                    </svg>
                @else
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="shrink-0 text-slate-500">
                        <circle cx="12" cy="12" r="10" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                    </svg>
                @endif
            </div>
            <table class="w-full divide-y divide-slate-800 text-left text-sm">
                <thead class="bg-slate-900/40 text-xs font-semibold uppercase text-slate-400">
                    <tr>
                        <th class="px-4 py-2">Supervisor</th>
                        <th class="px-4 py-2">Connection</th>
                        <th class="px-4 py-2">Queues</th>
                        <th class="px-4 py-2">Processes</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Last heartbeat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @foreach ($workers as $worker)
                        <tr class="hover:bg-slate-900/40">
                            <td class="px-4 py-2">
                                <div class="font-medium text-white">{{ $worker->name }}</div>
                                <div class="text-xs text-slate-500">pid {{ $worker->pid ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-2 text-slate-300">{{ $worker->connection ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-300">{{ is_array($worker->queues) ? implode(', ', $worker->queues) : '—' }}</td>
                            <td class="px-4 py-2 text-slate-300">1</td>
                            <td class="px-4 py-2">@include('periscope::partials.status-badge', ['status' => $worker->status])</td>
                            <td class="px-4 py-2 text-slate-400">{{ $worker->last_heartbeat_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="rounded-xl border border-slate-800 px-4 py-8 text-center text-slate-500">
            No workers reporting. Run <code class="rounded bg-slate-800 px-1">php artisan periscope:supervise</code>.
        </div>
    @endforelse
</div>
