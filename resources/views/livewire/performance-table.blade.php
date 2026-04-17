<div class="space-y-4">
    <div class="flex items-center gap-2 text-sm">
        <label class="text-slate-400">Window</label>
        <select wire:model.live="hours" class="rounded-md border border-slate-700 bg-slate-900 px-3 py-1.5">
            <option value="1">Last hour</option>
            <option value="6">Last 6 hours</option>
            <option value="24">Last 24 hours</option>
            <option value="168">Last 7 days</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th rowspan="2" class="px-4 py-2">Connection / Queue</th>
                    <th rowspan="2" class="px-4 py-2 text-right">Samples</th>
                    <th colspan="3" class="border-l border-slate-800 px-4 py-2 text-center">Runtime (ms)</th>
                    <th colspan="3" class="border-l border-slate-800 px-4 py-2 text-center">Wait (ms)</th>
                </tr>
                <tr>
                    <th class="border-l border-slate-800 px-4 py-1 text-right">p50</th>
                    <th class="px-4 py-1 text-right">p95</th>
                    <th class="px-4 py-1 text-right">p99</th>
                    <th class="border-l border-slate-800 px-4 py-1 text-right">p50</th>
                    <th class="px-4 py-1 text-right">p95</th>
                    <th class="px-4 py-1 text-right">p99</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($rows as $row)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2 text-white">{{ $row['connection'] }} / {{ $row['queue'] }}</td>
                        <td class="px-4 py-2 text-right text-slate-300">{{ number_format($row['count']) }}</td>
                        <td class="border-l border-slate-800 px-4 py-2 text-right text-slate-200">{{ $row['runtime_p50'] !== null ? number_format($row['runtime_p50']) : '—' }}</td>
                        <td class="px-4 py-2 text-right text-slate-200">{{ $row['runtime_p95'] !== null ? number_format($row['runtime_p95']) : '—' }}</td>
                        <td class="px-4 py-2 text-right text-amber-300">{{ $row['runtime_p99'] !== null ? number_format($row['runtime_p99']) : '—' }}</td>
                        <td class="border-l border-slate-800 px-4 py-2 text-right text-slate-200">{{ $row['wait_p50'] !== null ? number_format($row['wait_p50']) : '—' }}</td>
                        <td class="px-4 py-2 text-right text-slate-200">{{ $row['wait_p95'] !== null ? number_format($row['wait_p95']) : '—' }}</td>
                        <td class="px-4 py-2 text-right text-amber-300">{{ $row['wait_p99'] !== null ? number_format($row['wait_p99']) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No completed jobs in this window.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
