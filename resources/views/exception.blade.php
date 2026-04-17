@extends('periscope::layout')
@section('title', class_basename($class))
@section('content')
    <div class="mb-4">
        <a href="{{ route('periscope.exceptions') }}" class="text-xs text-slate-400 hover:text-sky-300">&larr; All exceptions</a>
    </div>
    <h1 class="text-2xl font-semibold text-rose-300">{{ class_basename($class) }}</h1>
    <div class="mt-1 font-mono text-xs text-slate-500">{{ $class }}</div>
    <div class="mt-2 text-sm text-slate-300">{{ $message }}</div>

    <div class="mt-6">
        <livewire:periscope.exception-detail :class="$class" :message="$message" />
    </div>
@endsection
