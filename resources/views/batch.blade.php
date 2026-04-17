@extends('periscope::layout')
@section('title', 'Batch')
@section('content')
    <div class="mb-4">
        <a href="{{ route('periscope.batches') }}" class="text-xs text-slate-400 hover:text-sky-300">&larr; All batches</a>
    </div>
    <livewire:periscope.batch-detail :batch-id="$id" />
@endsection
