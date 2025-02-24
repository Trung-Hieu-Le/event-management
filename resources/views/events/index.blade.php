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
                        <p>
                            @auth
                                @php $isFavorited = $event->favorites->contains('user_id', auth()->id()); @endphp
                                <i class="fa fa-heart {{ $isFavorited ? 'text-danger' : '' }}" 
                                   id="favorite-icon-{{ $event->id }}" 
                                   style="cursor: pointer;" 
                                   onclick="toggleFavorite({{ $event->id }})"></i>
                                <span id="favorites-count-{{ $event->id }}">{{ $event->favorites->count() }}</span> favorites |
                                <i class="fa fa-users"></i> 
                                <span id="participants-count-{{ $event->id }}">{{ $event->participants->count() }}</span> participants
                            @else
                                <i class="fa fa-heart"></i> <span>{{ $event->favorites->count() }}</span> favorites |
                                <i class="fa fa-users"></i> <span>{{ $event->participants->count() }}</span> participants
                            @endauth
                        </p>
                        <button class="btn btn-primary" onclick="loadEventDetails({{ $event->id }})">View Details</button>
                    </div>
                </div>
                @endforeach
            </div>

            @if($firstEvent)
                <div class="col-lg-4 col-sm-12" id="event-details">
                    <h2>{{ $firstEvent->title }}</h2>
                    <p>{{ $firstEvent->description }}</p>
                    <p>Start Time: {{ $firstEvent->start_time }}</p>
                    <p>End Time: {{ $firstEvent->end_time }}</p>
                    @if($firstEvent->location)
                        <p>Location: {{ $firstEvent->location }}</p>
                        <div id="map" style="height: 300px; width: 100%;"></div>
                    @endif
                    @auth
                        <button id="join-button-{{ $firstEvent->id }}" 
                                class="btn {{ $isJoined ? 'btn-danger' : 'btn-success' }}" 
                                onclick="toggleJoinEvent({{ $firstEvent->id }})">
                            {{ $isJoined ? 'Cancel' : 'Join' }}
                        </button>
                    @else
                        <button class="btn btn-success" disabled>Join</button>
                    @endauth
                </div>
            @endif

        @endif
    </div>
</div>

<script>
    function sendRequest(url, method, bodyData, callback) {
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(bodyData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) callback(data);
            else alert(data.message);
        })
        .catch(error => console.error('Err:', error));
    }

    function toggleFavorite(eventId) {
        sendRequest('/toggle-favorite', 'POST', { event_id: eventId }, data => {
            let icon = document.getElementById(`favorite-icon-${eventId}`);
            let countSpan = document.getElementById(`favorites-count-${eventId}`);
            icon.classList.toggle('text-danger', data.is_favorited);
            countSpan.textContent = data.favorites_count;
        });
    }

    function toggleJoinEvent(eventId) {
        sendRequest('/toggle-join-event', 'POST', { event_id: eventId }, data => {
            let button = document.getElementById(`join-button-${eventId}`);
            let countSpan = document.getElementById(`participants-count-${eventId}`);
            button.classList.toggle('btn-success', !data.is_joined);
            button.classList.toggle('btn-danger', data.is_joined);
            button.innerText = data.is_joined ? 'Cancel' : 'Join';
            countSpan.textContent = data.participants_count;
        });
    }

    function loadEventDetails(eventId) {
        fetch(`/events/${eventId}`)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                let detailsContainer = document.getElementById('event-details');
                let joinButtonHtml = `
                    <button id="join-button-${data.event.id}" class="btn ${data.is_joined ? 'btn-danger' : 'btn-success'}" 
                            onclick="toggleJoinEvent(${data.event.id})">
                        ${data.is_joined ? 'Cancel' : 'Join'}
                    </button>
                `;

                detailsContainer.innerHTML = `
                    <h2>${data.event.title}</h2>
                    <p>${data.event.description}</p>
                    <p>Start Time: ${data.event.start_time}</p>
                    <p>End Time: ${data.event.end_time}</p>
                    ${data.event.location ? `<p>Location: ${data.event.location}</p><div id="map" style="height: 300px; width: 100%;"></div>` : ''}
                    ${joinButtonHtml}
                `;
            })
            .catch(error => alert('Error: ' + error.message));
    }
</script>
@endsection
