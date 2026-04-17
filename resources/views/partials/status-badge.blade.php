@php($colors = [
    'queued' => 'bg-slate-700/60 text-slate-200',
    'running' => 'bg-sky-500/20 text-sky-300',
    'completed' => 'bg-emerald-500/20 text-emerald-300',
    'failed' => 'bg-rose-500/20 text-rose-300',
    'stopped' => 'bg-slate-700/60 text-slate-200',
    'stale' => 'bg-amber-500/20 text-amber-300',
])
<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $colors[$status] ?? 'bg-slate-700/60 text-slate-200' }}">
    {{ ucfirst($status) }}
</span>
