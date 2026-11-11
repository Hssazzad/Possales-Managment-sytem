@extends('adminlte::page')

@section('title', 'Clear System Cache')

@section('content_header')
    <h1>Clear System Cache</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card card-outline card-warning">
        <div class="card-body">
            <p>This clears Laravel's configuration, application, view, and optimization caches.</p>
            <form method="POST" action="{{ route('system.clear-cache.run') }}">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-broom mr-1"></i> Clear cache now
                </button>
            </form>
        </div>
    </div>
@stop
