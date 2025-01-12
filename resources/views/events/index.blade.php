@extends('layouts.app')

@section('content')
<div class="container">
    <h1>All Events</h1>
    @foreach ($events as $event)
        <div class="card mb-3">
            <div class="card-body">
                <h5>{{ $event->title }}</h5>
                <p>{{ $event->description }}</p>
                <a href="{{ route('events.show', $event) }}" class="btn btn-primary">View Details</a>
            </div>
        </div>
    @endforeach
</div>
@endsection
