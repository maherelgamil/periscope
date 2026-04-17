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
                    <th class="px-4 py-2">Exception</th>
                    <th class="px-4 py-2 text-right">Occurrences</th>
                    <th class="px-4 py-2 text-right">Jobs affected</th>
                    <th class="px-4 py-2">First seen</th>
                    <th class="px-4 py-2">Last seen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/50">
                @forelse ($groups as $group)
                    <tr class="hover:bg-slate-900/40">
                        <td class="px-4 py-3">
                            <a href="{{ route('periscope.exceptions.show', ['class' => $group->exception_class, 'message' => $group->exception_message]) }}"
                               class="font-medium text-rose-300 hover:underline">{{ class_basename($group->exception_class) }}</a>
                            <div class="mt-0.5 truncate text-xs text-slate-400" title="{{ $group->exception_message }}">
                                {{ \Illuminate\Support\Str::limit($group->exception_message, 140) }}
                            </div>
                            <div class="mt-0.5 font-mono text-[11px] text-slate-500">{{ $group->exception_class }}</div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-white">{{ number_format($group->occurrences) }}</td>
                        <td class="px-4 py-3 text-right text-slate-300">{{ number_format($group->jobs_affected) }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ \Illuminate\Support\Carbon::parse($group->first_seen)->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ \Illuminate\Support\Carbon::parse($group->last_seen)->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No exceptions in this window. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
