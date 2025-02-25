<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

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
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $search = $request->query('search');
            $sort = $request->query('sort', 'latest'); // Sắp xếp mặc định: mới nhất
            $filter = $request->query('filter', 'all'); // Mặc định hiển thị tất cả

            $eventsQuery = Event::query()
                ->when($filter === 'author', fn($query) => $query->where('author_id', $user->id))
                ->when($filter === 'joined', fn($query) => $query->whereHas('users', fn($q) => $q->where('user_id', $user->id))->where('author_id', '!=', $user->id))
                ->when($search, fn($query) => $query->where('name', 'like', "%$search%"))
                ->when($sort === 'most_tasks', fn($query) => $query->withCount('tasks')->orderByDesc('tasks_count'))
                ->when($sort === 'latest', fn($query) => $query->orderByDesc('created_at'))
                ->when($sort === 'oldest', fn($query) => $query->orderBy('created_at'));

            $events = $eventsQuery->get();

            return view('home', compact('events', 'search', 'sort', 'filter'));
        } catch (\Exception $e) {
            \Log::error('Error fetching events: ' . $e->getMessage());
            return redirect()->back()->withErrors('An error occurred while fetching events.');
        }
    }

}
