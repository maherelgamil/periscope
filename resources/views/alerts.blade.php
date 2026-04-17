@extends('periscope::layout')
@section('title', 'Alerts')
@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Alert history</h1>
    <livewire:periscope.alerts-table />
@endsection
