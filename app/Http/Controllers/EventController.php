<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use App\Models\Invite;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function show($id)
    {
        try {
            $event = Event::with(['tasks.users', 'users'])->findOrFail($id);
            return view('event.show', compact('event', 'id'));
        } catch (\Exception $e) {
            \Log::error('Error showing event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Lỗi khi hiển thị sự kiện: '.$e->getMessage());
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
                'image' => 'covers/default.jpg', // Ảnh mặc định
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('covers', 'public');
                $event->image = $path;
            }

            $event->save();
            $event->users()->attach(Auth::id());

            return redirect()->route('home')->with('success', 'Sự kiện được tạo thành công.');
        } catch (\Exception $e) {
            \Log::error('Error storing event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Lỗi khi tạo sự kiện: '.$e->getMessage());
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
                if ($event->image && $event->image !== 'covers/default.jpg') {
                    \Storage::disk('public')->delete($event->image);
                }
                $path = $request->file('image')->store('covers', 'public');
                $event->image = $path;
            } elseif (!$event->image) {
                $event->image = 'covers/default.jpg'; // Giữ ảnh cũ hoặc đặt ảnh mặc định
            }

            $event->save();

            return redirect()->route('home')->with('success', 'Sự kiện được cập nhật thành công.');
        } catch (\Exception $e) {
            \Log::error('Error updating event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Lỗi khi cập nhật sự kiện: '.$e->getMessage());
        }
    }


    public function destroy(Event $event)
    {
        if (Auth::id() !== $event->author_id) {
            return redirect()->route('home')->with('error', 'Unauthorized');
        }

        try {
            $event->delete();
            return redirect()->route('home')->with('success', 'Sự kiện được xóa thành công.');
        } catch (\Exception $e) {
            \Log::error('Error deleting event: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Lỗi khi xóa sự kiện: '.$e->getMessage());
        }
    }
}