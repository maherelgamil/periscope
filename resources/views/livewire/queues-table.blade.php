<div wire:poll.5s class="overflow-hidden rounded-xl border border-slate-800">
    <table class="w-full divide-y divide-slate-800 text-left text-sm">
        <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
            <tr>
                <th class="px-4 py-2">Connection</th>
                <th class="px-4 py-2">Queue</th>
                <th class="px-4 py-2 text-right">Pending</th>
                <th class="px-4 py-2 text-right">Delayed</th>
                <th class="px-4 py-2 text-right">Reserved</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/50">
            @forelse ($rows as $row)
                <tr>
                    <td class="px-4 py-2 text-slate-300">{{ $row['connection'] }}</td>
                    <td class="px-4 py-2 text-white">{{ $row['queue'] }}</td>
                    <td class="px-4 py-2 text-right text-slate-200">{{ $row['pending'] ?? '—' }}</td>
                    <td class="px-4 py-2 text-right text-slate-200">{{ $row['delayed'] ?? '—' }}</td>
                    <td class="px-4 py-2 text-right text-slate-200">{{ $row['reserved'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Configure queues in <code class="rounded bg-slate-800 px-1">config/periscope.php</code>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
