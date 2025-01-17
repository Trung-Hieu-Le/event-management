@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card mt-4">
        <div class="card-header">
            Profile Details
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
            <a href="{{ route('profile.change-password') }}" class="btn btn-secondary">Change Password</a>
        </div>
    </div>
</div>

@endsection
