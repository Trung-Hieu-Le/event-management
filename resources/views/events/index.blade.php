@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @if($events->isEmpty())
            <div class="col-12">
                <h1>No events are currently happening.</h1>
            </div>
        @else
            <div class="col-lg-8 col-sm-12" style="max-height: 80vh; overflow-y: auto;">
                <h1>All Events</h1>
                @foreach ($events as $event)
                <div class="card mb-3">
                    <div class="card-body">
                        <h5>{{ $event->title }}</h5>
                        <p>{{ $event->description }}</p>
                        <p class="text-muted"><em>Start Time: {{ $event->start_time }}</em></p>
                        <p class="text-muted"><em>End Time: {{ $event->end_time }}</em></p>
                        <p class="text-muted"><em>Location: {{ $event->location }}</em></p>
                        <button class="btn btn-primary" onclick="loadEventDetails({{ $event->id }})">View Details</button>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="col-lg-4 col-sm-12" id="event-details">
                <h2>{{ $events->first()->title }}</h2>
                <p>{{ $events->first()->description }}</p>
                <p>Start Time: {{ $events->first()->start_time }}</p>
                <p>End Time: {{ $events->first()->end_time }}</p>
                <button class="btn btn-success">Join Event</button>
            </div>
        @endif
    </div>
</div>

<script>
    function loadEventDetails(eventId) {
        
        fetch(`/events/${eventId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                
                document.getElementById('event-details').innerHTML = `
                    <h2>${data.title}</h2>
                    <p>${data.description}</p>
                    <p>Start Time: ${data.start_time}</p>
                    <p>End Time: ${data.end_time}</p>
                    <button class="btn btn-success">Join Event</button>
                `;
            })
            .catch(error => {
                alert('There was a problem with the fetch operation: ' + error.message);
            });
    }
</script>
@endsection
