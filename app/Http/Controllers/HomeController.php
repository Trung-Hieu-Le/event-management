<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        $userId = auth()->id();
        $events = Event::whereHas('participants', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get()->map(function ($event) use ($userId) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_time,
                'end' => $event->end_time,
                'location' => $event->location,
                'type' => $event->type,
                'description' => $event->description,
                'is_author' => $event->author_id == $userId,
            ];
        });
        return view('dashboard.index', compact('events'));
    }

}
