<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Hiển thị thông tin profile
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    // Hiển thị form chỉnh sửa profile
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    // Lưu thông tin profile đã chỉnh sửa
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->update($request->only(['name', 'email']));

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }

    // Hiển thị form đổi mật khẩu
    public function changePasswordForm()
    {
        return view('profile.change-password');
    }

    // Lưu mật khẩu mới
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('profile.show')->with('success', 'Password changed successfully.');
    }
}