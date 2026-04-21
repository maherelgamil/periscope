<div wire:poll.visible.5s class="overflow-hidden rounded-xl border border-slate-800">
    <div class="border-b border-slate-800 bg-slate-900/80 px-4 py-3">
        <span class="text-sm font-semibold text-slate-200">Current Workload</span>
    </div>
    <table class="w-full divide-y divide-slate-800/60 text-left text-sm">
        <thead class="bg-slate-900/40 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Queue</th>
                <th class="px-4 py-3 text-right">Jobs</th>
                <th class="px-4 py-3 text-right">Processes</th>
                <th class="px-4 py-3 text-right">Wait</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/40">
            @forelse ($rows as $row)
                <tr class="hover:bg-slate-800/30">
                    <td class="px-4 py-3 font-medium text-slate-100">{{ $row['queue'] }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-slate-400">{{ $row['jobs'] }}</td>
                    <td class="px-4 py-3 text-right tabular-nums text-slate-400">{{ $row['processes'] }}</td>
                    <td class="px-4 py-3 text-right text-slate-400">{{ $row['wait'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                        Configure queues in <code class="rounded bg-slate-800 px-1">config/periscope.php</code>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
