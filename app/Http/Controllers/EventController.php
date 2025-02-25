<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function show($id)
    {
        try {
            $event = Event::with('tasks')->findOrFail($id);
            return view('event.show', compact('event', 'id'));
        } catch (\Exception $e) {
            \Log::error('Error showing event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error showing event.');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date|after_or_equal:start_time',
                'image' => 'nullable|image',
            ]);

            $event = new Event([
                'name' => $request->name,
                'author_id' => Auth::id(),
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('covers', 'public');
                $event->image = $path;
            }

            $event->save();
            $event->users()->attach(Auth::id());

            return redirect()->route('home')->with('success', 'Event created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error storing event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error creating event.');
        }
    }

    public function update(Request $request, Event $event)
    {
        if (Auth::id() !== $event->author_id) {
            return redirect()->route('home')->with('error', 'Unauthorized');
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date|after_or_equal:start_time',
                'image' => 'nullable|image',
            ]);

            $event->name = $request->name;
            $event->start_time = $request->start_time;
            $event->end_time = $request->end_time;

            if ($request->hasFile('image')) {
                if ($event->image) {
                    \Storage::disk('public')->delete($event->image);
                }
                $path = $request->file('image')->store('covers', 'public');
                $event->image = $path;
            } else {
                if ($event->image) {
                    \Storage::disk('public')->delete($event->image);
                }
                $event->image = 'covers/default.jpg';
            }

            $event->save();

            return redirect()->route('home')->with('success', 'Event updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error updating event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error updating event.');
        }
    }

    public function destroy(Event $event)
    {
        if (Auth::id() !== $event->author_id) {
            return redirect()->route('home')->with('error', 'Unauthorized');
        }

        try {
            $event->delete();
            return redirect()->route('home')->with('success', 'Event deleted.');
        } catch (\Exception $e) {
            \Log::error('Error deleting event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Error deleting event.');
        }
    }

    public function inviteUser(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'event_id' => 'required|exists:events,id',
        ]);

        try {
            $user = User::where('username', $request->identifier)
                        ->orWhere('email', $request->identifier)
                        ->first();

            if (!$user) {
                return response()->json(['error' => 'User không tồn tại'], 404);
            }

            if ($user->id == Auth::id()) {
                return response()->json(['error' => 'Không thể mời chính mình'], 400);
            }

            EventInvitation::updateOrCreate(
                ['event_id' => $request->event_id, 'invitee_id' => $user->id],
                ['inviter_id' => Auth::id(), 'status' => 'pending']
            );

            return response()->json(['success' => 'Đã gửi lời mời']);
        } catch (\Exception $e) {
            \Log::error('Error inviting user: ' . $e->getMessage());
            return response()->json(['error' => 'Error inviting user'], 500);
        }
    }

    public function respondInvite(Request $request)
    {
        $request->validate([
            'invitation_id' => 'required|exists:event_invitations,id',
            'response' => 'required|in:accepted,rejected',
        ]);

        try {
            $invitation = EventInvitation::findOrFail($request->invitation_id);

            if ($invitation->invitee_id != Auth::id()) {
                return response()->json(['error' => 'Không có quyền xử lý'], 403);
            }

            $invitation->update(['status' => $request->response]);

            return response()->json(['success' => 'Phản hồi thành công']);
        } catch (\Exception $e) {
            \Log::error('Error responding to invitation: ' . $e->getMessage());
            return response()->json(['error' => 'Error responding to invitation'], 500);
        }
    }

    public function getInvitations()
    {
        try {
            $invitations = EventInvitation::with('event', 'inviter')
                ->where('invitee_id', Auth::id())
                ->where('status', 'pending')
                ->get();

            return response()->json($invitations);
        } catch (\Exception $e) {
            \Log::error('Error fetching invitations: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching invitations'], 500);
        }
    }
}