<div wire:poll.visible.10s class="overflow-hidden rounded-xl border border-slate-800">
    <div class="border-b border-slate-800 bg-slate-900/80 px-4 py-3">
        <span class="text-sm font-semibold text-slate-200">Workers</span>
    </div>
    <table class="w-full divide-y divide-slate-800 text-left text-sm">
        <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
            <tr>
                <th class="px-4 py-3">Worker</th>
                <th class="px-4 py-3">Connection</th>
                <th class="px-4 py-3">Queues</th>
                <th class="px-4 py-3">Heartbeat</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/50">
            @forelse ($groups as $hostname => $workers)
                @php($hasRunning = $workers->contains('status', \MaherElGamil\Periscope\Models\Worker::STATUS_RUNNING))
                <tr class="bg-slate-900/80">
                    <td colspan="5" class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            <div @class([
                                'flex h-6 w-6 items-center justify-center rounded text-[10px] font-bold',
                                'bg-emerald-500/15 text-emerald-400' => $hasRunning,
                                'bg-slate-800 text-slate-500' => ! $hasRunning,
                            ])>{{ strtoupper(substr($hostname ?? 'U', 0, 1)) }}</div>
                            <span class="text-xs font-semibold text-slate-300">{{ $hostname ?? 'Unknown host' }}</span>
                            @if ($hasRunning)
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                </span>
                            @else
                                <span class="inline-flex h-2 w-2 rounded-full bg-slate-600"></span>
                            @endif
                        </div>
                    </td>
                </tr>
                @foreach ($workers as $worker)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-4 py-3 pl-12">
                            <div class="font-medium text-slate-100">{{ \Illuminate\Support\Str::after($worker->name, $hostname.'-') ?: $worker->name }}</div>
                            <div class="font-mono text-xs text-slate-500">pid {{ $worker->pid ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-300">{{ $worker->connection ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-300">{{ is_array($worker->queues) ? implode(', ', $worker->queues) : '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $worker->last_heartbeat_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @include('periscope::partials.status-badge', ['status' => $worker->status])
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                        No workers reporting. Run <code class="rounded bg-slate-800 px-1">php artisan periscope:supervise</code>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
