@extends('periscope::layout')
@section('title', 'Schedules')
@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Scheduled commands</h1>
    <livewire:periscope.schedules-table />
@endsection
