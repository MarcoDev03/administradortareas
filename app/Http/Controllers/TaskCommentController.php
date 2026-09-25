<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    public function index(Task $task)
    {
        $comments = $task->comments()
            ->with('user:id,name,role')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'user_id' => $c->user_id,
                    'user_name' => $c->user ? $c->user->name : 'Usuario',
                    'user_initials' => $c->user ? $c->user->initials : 'U',
                    'user_role' => $c->user ? $c->user->role : '',
                    'comment' => $c->comment,
                    'created_at' => $c->created_at ? $c->created_at->diffForHumans() : '',
                    'created_at_formatted' => $c->created_at ? $c->created_at->format('d/m/Y H:i') : '',
                    'can_delete' => Auth::id() === $c->user_id || Auth::user()->hasFullAccess(),
                ];
            });

        return response()->json([
            'success' => true,
            'task_id' => $task->id,
            'task_title' => $task->title,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request, Task $task)
    {
        $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => trim($request->comment),
        ]);

        $comment->load('user:id,name,role');

        $formattedComment = [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'user_name' => $comment->user ? $comment->user->name : 'Usuario',
            'user_initials' => $comment->user ? $comment->user->initials : 'U',
            'user_role' => $comment->user ? $comment->user->role : '',
            'comment' => $comment->comment,
            'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : '',
            'created_at_formatted' => $comment->created_at ? $comment->created_at->format('d/m/Y H:i') : '',
            'can_delete' => true,
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'comment' => $formattedComment,
                'total_comments' => $task->comments()->count(),
            ]);
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroy(TaskComment $comment)
    {
        $user = Auth::user();

        if ($comment->user_id !== $user->id && !$user->hasFullAccess()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'No tienes permiso para eliminar este comentario.'], 403);
            }
            return back()->with('error', 'No tienes permiso para eliminar este comentario.');
        }

        $taskId = $comment->task_id;
        $comment->delete();

        $totalComments = TaskComment::where('task_id', $taskId)->count();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'task_id' => $taskId,
                'total_comments' => $totalComments,
            ]);
        }

        return back()->with('success', 'Comentario eliminado.');
    }
}
