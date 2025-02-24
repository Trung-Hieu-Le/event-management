<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    public function toggleFavorite(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để yêu thích sự kiện này'], 401);
        }

        $user = auth()->user();
        $eventId = $request->event_id;

        $favorite = Favorite::where('event_id', $eventId)->where('user_id', $user->id)->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorited = false;
        } else {
            Favorite::create([
                'event_id' => $eventId,
                'user_id' => $user->id
            ]);
            $isFavorited = true;
        }

        $favoritesCount = Favorite::where('event_id', $eventId)->count();

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'favorites_count' => $favoritesCount
        ]);
    }

}