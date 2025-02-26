@extends('layouts.app')

@section('content') 
<div class="container">
    <h2>Lời mời tham gia sự kiện</h2>
    @foreach($invites as $invite)
        <div class="card mb-3">
            <div class="card-body row">
                <div class="col-2">
                    <img src="{{ asset('storage/' . $invite->event->image) }}" alt="Event Image" class="img-fluid mb-2" style="max-height: 150px;">
                </div>
                <div class="col-8">
                    <h5 class="card-title">{{ $invite->event->name }}</h5>
                    <p><strong>Thời gian:</strong> {{ $invite->event->start_time }} - {{ $invite->event->end_time }}</p>
                </div>

                <div class="col-2 text-end">
                    <form action="/invites/{{ $invite->id }}/accept" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">Đồng ý</button>
                    </form>
                    <form action="/invites/{{ $invite->id }}/reject" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">Từ chối</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
