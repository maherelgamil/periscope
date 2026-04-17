<div wire:poll.visible.10s class="overflow-hidden rounded-xl border border-slate-800">
    <table class="w-full divide-y divide-slate-800 text-left text-sm">
        <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
            <tr>
                <th class="px-4 py-2">Worker</th>
                <th class="px-4 py-2">Host</th>
                <th class="px-4 py-2">Connection</th>
                <th class="px-4 py-2">Queues</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Last heartbeat</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/50">
            @forelse ($workers as $worker)
                <tr>
                    <td class="px-4 py-2">
                        <div class="font-medium text-white">{{ $worker->name }}</div>
                        <div class="text-xs text-slate-500">pid {{ $worker->pid ?? '—' }}</div>
                    </td>
                    <td class="px-4 py-2 text-slate-300">{{ $worker->hostname ?? '—' }}</td>
                    <td class="px-4 py-2 text-slate-300">{{ $worker->connection ?? '—' }}</td>
                    <td class="px-4 py-2 text-slate-300">{{ is_array($worker->queues) ? implode(', ', $worker->queues) : '—' }}</td>
                    <td class="px-4 py-2">@include('periscope::partials.status-badge', ['status' => $worker->status])</td>
                    <td class="px-4 py-2 text-slate-400">{{ $worker->last_heartbeat_at?->diffForHumans() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No workers reporting. Run <code class="rounded bg-slate-800 px-1">php artisan periscope:supervise</code>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
