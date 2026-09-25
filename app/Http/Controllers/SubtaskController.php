<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subtask;
use Illuminate\Http\Request;

class SubtaskController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $subtask = Subtask::create([
            'task_id' => $task->id,
            'title' => trim($request->title),
            'status' => 'no_iniciado',
        ]);

        if ($request->wantsJson()) {
            return response()->json($subtask);
        }

        return back()->with('success', 'Subtarea agregada.');
    }

    public function toggle(Request $request, Subtask $subtask)
    {
        $newStatus = $subtask->status === 'finalizado' ? 'no_iniciado' : 'finalizado';
        if ($request->has('status')) {
            $newStatus = $request->status;
        }

        $subtask->update(['status' => $newStatus]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $newStatus]);
        }

        return back()->with('success', 'Subtarea actualizada.');
    }

    public function destroy(Subtask $subtask)
    {
        $subtask->delete();
        return back()->with('success', 'Subtarea eliminada.');
    }
}
