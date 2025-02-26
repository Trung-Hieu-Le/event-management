<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class InviteController extends Controller
{
    public function inviteUser(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'event_id' => 'required|exists:events,id',
        ]);

        try {
            $user = User::where('name', $request->identifier)
                        ->orWhere('email', $request->identifier)
                        ->first();

            if (!$user) {
                return response()->json(['error' => 'User không tồn tại'], 404);
            }

            if ($user->id == Auth::id()) {
                return response()->json(['error' => 'Không thể mời chính mình'], 400);
            }

            // Kiểm tra user đã tham gia sự kiện chưa
            $event = Event::findOrFail($request->event_id);
            if ($event->users()->where('user_id', $user->id)->exists()) {
                return response()->json(['error' => 'Người dùng đã tham gia vào dự án'], 400);
            }

            // Kiểm tra xem đã gửi lời mời trước đó chưa
            $existingInvite = Invite::where('event_id', $request->event_id)
                ->where('invitee_id', $user->id)
                ->where('status', 'pending')
                ->exists();

            if ($existingInvite) {
                return response()->json(['error' => 'Bạn đã gửi lời mời cho người này rồi'], 400);
            }

            // Gửi lời mời mới
            Invite::create([
                'event_id' => $request->event_id,
                'invitee_id' => $user->id,
                'inviter_id' => Auth::id(),
                'status' => 'pending'
            ]);

            return response()->json(['success' => 'Đã gửi lời mời']);
        } catch (\Exception $e) {
            \Log::error('Error inviting user: ' . $e->getMessage());
            return response()->json(['error' => 'Error inviting user'], 500);
        }
    }


    public function listInvites()
    {
        $invites = Invite::where('invitee_id', auth()->id())->where('status', 'pending')->get();
        return view('invites.index', compact('invites'));
    }

    public function acceptInvite($id)
    {
        $invite = Invite::where('id', $id)->where('invitee_id', auth()->id())->first();
        if (!$invite) return redirect()->back()->with('error', 'Lời mời không tồn tại');

        $invite->update(['status' => 'accepted']);
        $invite->event->users()->attach(auth()->id());

        return redirect()->back()->with('success', 'Bạn đã tham gia sự kiện');
    }

    public function rejectInvite($id)
    {
        $invite = Invite::where('id', $id)->where('invitee_id', auth()->id())->first();
        if (!$invite) return redirect()->back()->with('error', 'Lời mời không tồn tại');

        $invite->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Bạn đã từ chối lời mời');
    }
}
