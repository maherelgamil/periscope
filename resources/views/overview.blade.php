@extends('periscope::layout')
@section('title', 'Overview')
@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Overview</h1>
    <livewire:periscope.overview-stats />
    <div class="mt-6">
        <livewire:periscope.throughput-chart />
    </div>
@endsection
