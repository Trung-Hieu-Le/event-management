<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        try {
            $events = Event::with(['participants', 'favorites'])
                        ->where('type', 'community')
                        ->get();
            $firstEvent = $events->first();
            $isJoined = false;

            if (auth()->check() && $firstEvent) {
                $isJoined = DB::table('event_users')
                ->where('event_id', $firstEvent->id)
                ->where('user_id', auth()->id())
                ->exists();
            }
            return view('events.index', compact('events', 'firstEvent', 'isJoined'));
        } catch (\Exception $e) {
            \Log::error('Error fetching events (controller): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::findOrFail($id);
            if (!$event) {
                return response()->json(['error' => 'Event not found'], 404);
            }

            $isJoined = false;
            if (auth()->check()) {
                $user = auth()->user();
                $isJoined = DB::table('event_users')
                            ->where('event_id', $id)
                            ->where('user_id', $user->id)
                            ->exists();
            }

            return response()->json([
                'event' => $event,
                'is_joined' => $isJoined
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching event (controller): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function addToFavorites(Request $request, Event $event)
    {
        try {
            $request->user()->favorites()->create(['event_id' => $event->id]);
            return redirect()->back()->with('success', 'Event added to favorites!');
        } catch (\Exception $e) {
            \Log::error('Error adding event to favorites (controller): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function updateDayOnly(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            $start = $request->input('start');
            $end = $request->input('end');

            if (!$start) {
                return response()->json(['success' => false, 'message' => 'Start time is required'], 422);
            }

            $event->start_time = Carbon::parse($start, 'Asia/Ho_Chi_Minh')->setTimezone('UTC')->format('Y-m-d H:i:s');
            $event->end_time = $end
                ? Carbon::parse($end, 'Asia/Ho_Chi_Minh')->setTimezone('UTC')->format('Y-m-d H:i:s')
                : null;

            $event->save();

            return response()->json(['success' => true, 'message' => 'Event updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Error updating event (controller): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        try {
            $request->validate([
                'id' => 'required|exists:events,id',
                'title' => 'required|string|max:255',
                'start_time' => 'required|date',
                'end_time' => 'nullable|date|after_or_equal:start_time',
                'location' => 'nullable|string|max:255',
                'type' => 'required|string|in:community,personal',
            ]);

            // Tìm sự kiện và cập nhật thông tin
            $event = Event::findOrFail($request->id);
            $event->title = $request->title;
            $event->start_time = $request->start_time;
            $event->end_time = $request->end_time;
            $event->location = $request->location;
            $event->type = $request->type ?? 'personal';
            $event->author_id = Auth::id();
            $event->save();

            return response()->json(['success' => true, 'message' => 'Event updated successfully!']);
        } catch (\Exception $e) {
            \Log::error('Error updating event (controller): ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'start_time' => 'required|date',
                'end_time' => 'nullable|date|after_or_equal:start_time',
                'location' => 'nullable|string|max:255',
                'type' => 'required|string|in:community,personal',
            ]);

            $event = Event::create([
                'title' => $request->title,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'type' => $request->type ?? 'personal',
                'author_id' => $request->author_id,
            ]);
            $event->participants()->attach(auth()->id());
            return redirect()->back()->with('success', 'Event created successfully!');
        } catch (\Exception $e) {
            \Log::error('Error creating event (controller): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function toggleJoinEvent(Request $request)
{
    if (!auth()->check()) {
        return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để tham gia sự kiện này'], 401);
    }

    $user = auth()->user();
    $eventId = $request->event_id;
    $existing = DB::table('event_users')
                    ->where('event_id', $eventId)
                    ->where('user_id', $user->id)
                    ->first();

    if ($existing) {
        DB::table('event_users')
            ->where('event_id', $eventId)
            ->where('user_id', $user->id)
            ->delete();
        $isJoined = false;
    } else {
        DB::table('event_users')->insert([
            'event_id' => $eventId,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $isJoined = true;
    }

    $participantsCount = DB::table('event_users')->where('event_id', $eventId)->count();

    return response()->json([
        'success' => true,
        'is_joined' => $isJoined,
        'participants_count' => $participantsCount
    ]);
}

}
