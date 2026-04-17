@extends('periscope::layout')
@section('title', 'Failed')
@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Failed jobs</h1>
    <livewire:periscope.failed-jobs-table />
@endsection
