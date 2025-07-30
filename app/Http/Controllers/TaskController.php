<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project)
    {
        return response()->json($project->tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:1',
            // 'status' tidak diperlukan di sini karena defaultnya Draft
        ]);

        $task = $project->tasks()->create([
            'name' => $validatedData['name'],
            'weight' => $validatedData['weight'],
            'status' => 'Draft', // Set default status
        ]);

        // Refresh project status and progress after adding a task
        $project->updateStatusAndProgress();
        $project->refresh(); // Pastikan project di-refresh setelah update

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return response()->json($task->load('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:Draft,In Progress,Done',
            'weight' => 'required|integer|min:1',
        ]);

        $task->update($validatedData);

        // Cari project terkait
        $project = Project::find($task->project_id);
        if ($project) {
            $project->updateStatusAndProgress();
            $project->refresh(); // Pastikan data project di-refresh setelah update
            Log::info('Project status and progress updated after task update: ' . json_encode($project));
        } else {
            Log::warning('Project with ID ' . $task->project_id . ' not found after task update.');
        }

        return response()->json($task->load('project'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Log::info("Attempting to delete task: " . $task->id);
        try {
            $projectId = $task->project_id; // Simpan project_id sebelum task dihapus
            $task->delete();

            // Cari project terkait setelah task dihapus
            $project = Project::find($projectId);

            // Hanya panggil updateStatusAndProgress jika project ditemukan
            if ($project) {
                $project->updateStatusAndProgress();
                $project->refresh(); // Penting untuk memastikan data project di-refresh setelah update
                Log::info('Project status and progress updated after task deletion: ' . json_encode($project));
            } else {
                Log::warning('Project with ID ' . $projectId . ' not found after task deletion. Could be already deleted or invalid.');
            }

            // Mengembalikan 204 No Content adalah standar untuk DELETE sukses tanpa payload
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Error deleting task or updating project: ' . $e->getMessage(), ['exception' => $e, 'task_id' => $task->id ?? 'unknown', 'project_id_attempted' => $projectId ?? 'unknown']);
            // Mengembalikan respons JSON error dengan status 500
            return response()->json(['message' => 'Failed to delete task', 'error' => $e->getMessage()], 500);
        }
    }
}