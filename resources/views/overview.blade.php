@extends('periscope::layout')
@section('title', 'Overview')
@section('content')
    <livewire:periscope.overview-stats />
    <div class="mt-6">
        <livewire:periscope.throughput-chart />
    </div>
    <div class="mt-6">
        <livewire:periscope.workload-table />
    </div>
    <div class="mt-6">
        <livewire:periscope.workers-table />
    </div>
@endsection
