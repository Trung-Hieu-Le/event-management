<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index($event_id)
    {
        try {
            return Task::where('event_id', $event_id)->get();
        } catch (\Exception $e) {
            \Log::error('Error fetching tasks: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching tasks'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'status' => 'required|string',
                'event_id' => 'required|exists:events,id',
                'priority' => 'nullable|string',
                'start_time' => 'nullable|date',
                'end_time' => 'nullable|date',
                'assigned_to' => 'nullable|string'
            ]);

            Task::create([
                'title' => $validated['title'],
                'description' => $request->description,
                'status' => $validated['status'],
                'event_id' => $validated['event_id'],
                'assigned_to' => $request->assigned_to,
                'author_id' => Auth::id(),
                'deleted' => 0,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'priority' => $validated['priority'] ?? 'low'
            ]);

            return response()->json(['message' => 'Task created successfully!']);
        } catch (\Exception $e) {
            \Log::error('Error creating task: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating task'], 500);
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
            return response()->json(['error' => 'Error updating task status'], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        try {
            $task->update($request->all());
            return response()->json($task);
        } catch (\Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage());
            return response()->json(['error' => 'Error updating task'], 500);
        }
    }

    public function softDelete(Task $task)
    {
        try {
            $task->update(['deleted' => true]);
            return response()->json(['message' => 'Task moved to deleted']);
        } catch (\Exception $e) {
            \Log::error('Error soft deleting task: ' . $e->getMessage());
            return response()->json(['error' => 'Error soft deleting task'], 500);
        }
    }

}
