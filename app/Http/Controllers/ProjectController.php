<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Get all projects
    public function index()
    {
        $projects = Project::with('tasks')->get();
        return response()->json($projects);
    }

    // Create a new project
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'status' => 'Draft', // Default status
            'completion_progress' => 0.00, // Default progress
        ]);

        return response()->json($project, 201); 
    }

    // Get a single project
    public function show(Project $project)
    {
        return response()->json($project->load('tasks'));
    }

    // Update a project
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project->update([
            'name' => $request->name,
        ]);

        // Re-calculate project status and progress after update
        $project->updateStatusAndProgress();

        return response()->json($project->load('tasks'));
    }

    // Delete a project
    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(null, 204); // 204 No Content
    }

}