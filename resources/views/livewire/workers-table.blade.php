<div wire:poll.visible.10s class="space-y-6">
    @forelse ($groups as $hostname => $workers)
        @php($hasRunning = $workers->contains('status', \MaherElGamil\Periscope\Models\Worker::STATUS_RUNNING))

        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/60 shadow-xl shadow-black/20">

            {{-- Host header --}}
            <div class="flex items-center justify-between border-b border-slate-800 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div @class([
                        'flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold',
                        'bg-emerald-500/15 text-emerald-400' => $hasRunning,
                        'bg-slate-800 text-slate-500' => ! $hasRunning,
                    ])>
                        {{ strtoupper(substr($hostname ?? 'U', 0, 1)) }}
                    </div>
                    <span class="font-semibold text-white">{{ $hostname ?? 'Unknown host' }}</span>
                </div>
                @if ($hasRunning)
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                    </span>
                @else
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-slate-600"></span>
                @endif
            </div>

            {{-- Worker cards grid --}}
            <div class="grid grid-cols-1 gap-px bg-slate-800/60 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($workers as $worker)
                    <div class="bg-slate-900/60 px-5 py-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-slate-100">{{ $worker->name }}</div>
                                <div class="mt-0.5 font-mono text-xs text-slate-500">pid {{ $worker->pid ?? '—' }}</div>
                            </div>
                            @include('periscope::partials.status-badge', ['status' => $worker->status])
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3 text-xs">
                            <div>
                                <div class="font-medium uppercase tracking-wide text-slate-500">Connection</div>
                                <div class="mt-1 text-slate-300">{{ $worker->connection ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="font-medium uppercase tracking-wide text-slate-500">Queues</div>
                                <div class="mt-1 truncate text-slate-300">{{ is_array($worker->queues) ? implode(', ', $worker->queues) : '—' }}</div>
                            </div>
                            <div>
                                <div class="font-medium uppercase tracking-wide text-slate-500">Heartbeat</div>
                                <div class="mt-1 text-slate-300">{{ $worker->last_heartbeat_at?->diffForHumans() ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    @empty
        <div class="rounded-2xl border border-slate-800 px-4 py-10 text-center text-slate-500">
            No workers reporting. Run <code class="rounded bg-slate-800 px-1">php artisan periscope:supervise</code>.
        </div>
    @endforelse
</div>
