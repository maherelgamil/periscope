<div class="space-y-4">
    @if (! $exists)
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-200">
            Laravel's <code class="rounded bg-slate-800 px-1">job_batches</code> table does not exist. Run
            <code class="rounded bg-slate-800 px-1">php artisan make:queue-batches-table</code> and
            <code class="rounded bg-slate-800 px-1">php artisan migrate</code> to enable batch tracking.
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Progress</th>
                    <th class="px-4 py-2 text-right">Total</th>
                    <th class="px-4 py-2 text-right">Pending</th>
                    <th class="px-4 py-2 text-right">Failed</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Created</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($batches as $batch)
                    @php
                        $done = $batch->total_jobs - $batch->pending_jobs;
                        $pct = $batch->total_jobs > 0 ? (int) round($done / $batch->total_jobs * 100) : 0;
                        $status = $batch->cancelled_at ? 'cancelled' : ($batch->finished_at ? ($batch->failed_jobs > 0 ? 'failed' : 'completed') : 'running');
                    @endphp
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-2">
                            <div class="font-medium text-white">{{ $batch->name ?: 'Unnamed batch' }}</div>
                            <div class="font-mono text-[11px] text-slate-500">{{ $batch->id }}</div>
                        </td>
                        <td class="px-4 py-2">
                            <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-800">
                                <div class="h-full bg-emerald-400/70" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="mt-0.5 text-xs text-slate-400">{{ $done }} / {{ $batch->total_jobs }}</div>
                        </td>
                        <td class="px-4 py-2 text-right text-slate-200">{{ number_format($batch->total_jobs) }}</td>
                        <td class="px-4 py-2 text-right text-slate-300">{{ number_format($batch->pending_jobs) }}</td>
                        <td class="px-4 py-2 text-right text-rose-300">{{ number_format($batch->failed_jobs) }}</td>
                        <td class="px-4 py-2">@include('periscope::partials.status-badge', ['status' => $status])</td>
                        <td class="px-4 py-2 text-slate-400">{{ \Illuminate\Support\Carbon::createFromTimestamp($batch->created_at)->diffForHumans() }}</td>
                        <td class="px-4 py-2 text-right">
                            @if (! $batch->finished_at && ! $batch->cancelled_at)
                                <button wire:click="cancel('{{ $batch->id }}')" wire:confirm="Cancel this batch?"
                                    class="rounded-md bg-rose-500/20 px-2 py-1 text-xs font-medium text-rose-300 hover:bg-rose-500/30">
                                    Cancel
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No batches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($batches instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div>{{ $batches->links() }}</div>
    @endif
</div>
