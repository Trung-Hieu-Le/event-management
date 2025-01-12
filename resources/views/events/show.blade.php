@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $event->title }}</h1>
    <p>{{ $event->description }}</p>
    <p><strong>Start:</strong> {{ $event->start_time }}</p>
    <p><strong>Location:</strong> {{ $event->location }}</p>

    <!-- Google Maps -->
    @if ($event->latitude && $event->longitude)
        <div id="map" style="width: 100%; height: 400px;"></div>
        <script>
            function initMap() {
                var location = { lat: {{ $event->latitude }}, lng: {{ $event->longitude }} };
                var map = new google.maps.Map(document.getElementById('map'), { zoom: 15, center: location });
                var marker = new google.maps.Marker({ position: location, map: map });
            }
        </script>
        <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_API_KEY&callback=initMap"></script>
    @endif

    @auth
    <form method="POST" action="{{ route('events.favorite', $event) }}">
        @csrf
        <button type="submit" class="btn btn-success">Add to Favorites</button>
    </form>
    @endauth
</div>
@endsection
