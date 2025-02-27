<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string',
                'event_id' => 'required|exists:events,id',
                'priority' => 'nullable|string',
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date',
                'assigned_to' => 'required|array',
                'assigned_to.*' => 'exists:users,id',
            ]);

            $task = Task::create([
                'title' => $validated['title'],
                'description' => $request->description,
                'status' => $validated['status'],
                'event_id' => $validated['event_id'],
                'author_id' => Auth::id(),
                'deleted' => 0,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'priority' => $validated['priority'] ?? 'low'
            ]);
            if ($request->has('assigned_to')) {
                $task->users()->sync($request->assigned_to);
            }

            return response()->json(['message' => 'Task đã được tạo thành công!']);
        } catch (\Exception $e) {
            \Log::error('Error creating task: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi tạo task'], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'status' => 'required|in:to-do,doing,done,failed,reworking',
            ]);

            $task = Task::findOrFail($request->task_id);
            $task->update(['status' => $request->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error updating task status: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi cập nhật task'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->update($request->only(['title', 'description', 'priority', 'start_time', 'end_time']));
            if ($request->has('assigned_to')) {
                $task->users()->sync($request->assigned_to);
            }
            return response()->json(['message' => 'Task cập nhật thành công!']);
        } catch (\Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi cập nhật task'], 500);
        }
    }

    public function delete($id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->update(['deleted' => 1]);
            return response()->json(['message' => 'Task đã được chuyển vào Deleted!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Có lỗi xảy ra khi xóa Task'], 500);
        }
    }

    public function restore($id)
    {
        try {
            $task = Task::findOrFail($id);
            $task->update(['deleted' => 0]);
            return response()->json(['message' => 'Task đã được khôi phục!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Có lỗi xảy ra khi khôi phục Task'], 500);
        }
    }

}
