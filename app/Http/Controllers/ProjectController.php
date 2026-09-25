<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'member_user_ids' => ['nullable', 'array'],
            'member_user_ids.*' => ['exists:users,id'],
            'links' => ['nullable', 'array'],
            'links.*.name' => ['required', 'string', 'max:255'],
            'links.*.url' => ['required', 'string', 'max:500'],
            'links.*.assigned_user_ids' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($request) {
            $user = Auth::user();

            $project = Project::create([
                'name' => trim($request->name),
                'created_by' => $user->id,
            ]);

            // Miembros del proyecto
            $memberIds = $request->input('member_user_ids', []);
            if (!in_array($user->id, $memberIds)) {
                $memberIds[] = $user->id;
            }
            $project->members()->sync($memberIds);

            // Enlaces
            if ($request->has('links')) {
                foreach ($request->links as $linkData) {
                    $url = trim($linkData['url']);
                    if (!preg_match('/^https?:\/\//i', $url)) {
                        $url = 'https://' . $url;
                    }
                    $link = ProjectLink::create([
                        'project_id' => $project->id,
                        'name' => trim($linkData['name']),
                        'url' => $url,
                    ]);
                    if (!empty($linkData['assigned_user_ids'])) {
                        $link->assignedUsers()->sync($linkData['assigned_user_ids']);
                    }
                }
            }
        });

        return back()->with('success', 'Proyecto creado exitosamente.');
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'member_user_ids' => ['nullable', 'array'],
            'member_user_ids.*' => ['exists:users,id'],
            'links' => ['nullable', 'array'],
            'links.*.name' => ['required', 'string', 'max:255'],
            'links.*.url' => ['required', 'string', 'max:500'],
            'links.*.assigned_user_ids' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($request, $project) {
            $user = Auth::user();

            $project->update([
                'name' => trim($request->name),
            ]);

            $memberIds = $request->input('member_user_ids', []);
            if (!in_array($user->id, $memberIds)) {
                $memberIds[] = $user->id;
            }
            $project->members()->sync($memberIds);

            // Recrear enlaces del proyecto
            $project->links()->delete();
            if ($request->has('links')) {
                foreach ($request->links as $linkData) {
                    $url = trim($linkData['url']);
                    if (!preg_match('/^https?:\/\//i', $url)) {
                        $url = 'https://' . $url;
                    }
                    $link = ProjectLink::create([
                        'project_id' => $project->id,
                        'name' => trim($linkData['name']),
                        'url' => $url,
                    ]);
                    if (!empty($linkData['assigned_user_ids'])) {
                        $link->assignedUsers()->sync($linkData['assigned_user_ids']);
                    }
                }
            }
        });

        return back()->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('dashboard')->with('success', 'Proyecto eliminado exitosamente.');
    }
}
