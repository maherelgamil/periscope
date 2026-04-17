<div class="space-y-4">
    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full divide-y divide-slate-800 text-left text-sm">
            <thead class="bg-slate-900/60 text-xs uppercase text-slate-400">
                <tr>
                    <th class="px-4 py-2">Alert</th>
                    <th class="px-4 py-2">Severity</th>
                    <th class="px-4 py-2">Channels</th>
                    <th class="px-4 py-2">Fired</th>
                    <th class="px-4 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($alerts as $alert)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-3">
                            <div class="font-medium text-white">{{ $alert->title }}</div>
                            <div class="mt-0.5 text-xs text-slate-400">{{ $alert->message }}</div>
                            <div class="mt-0.5 font-mono text-[11px] text-slate-500">{{ $alert->key }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php($color = match($alert->severity) { 'error' => 'text-rose-300 bg-rose-500/10', 'warning' => 'text-amber-300 bg-amber-500/10', default => 'text-slate-300 bg-slate-700/60' })
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $color }}">{{ $alert->severity }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ is_array($alert->channels) ? implode(', ', $alert->channels) : '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $alert->fired_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="delete({{ $alert->id }})" wire:confirm="Remove this alert?"
                                class="rounded-md bg-slate-700/60 px-2 py-1 text-xs font-medium text-slate-200 hover:bg-slate-700">
                                Dismiss
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No alerts fired yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $alerts->links() }}</div>
</div>
