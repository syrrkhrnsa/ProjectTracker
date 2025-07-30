<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log; 

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $appends = ['completion_progress', 'status'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getCompletionProgressAttribute()
    {
        $totalWeight = $this->tasks->sum('weight');
        $completedWeight = $this->tasks->where('status', 'Done')->sum('weight');

        if ($totalWeight === 0) {
            return 0.0;
        }

        return round(($completedWeight / $totalWeight) * 100, 2);
    }

    public function getStatusAttribute()
    {
        if ($this->tasks->isEmpty()) {
            return 'Draft';
        }

        $hasInProgress = $this->tasks->contains('status', 'In Progress');
        $allDone = $this->tasks->every('status', 'Done');

        if ($allDone) {
            return 'Done';
        } elseif ($hasInProgress) {
            return 'In Progress';
        } else {
            
            return 'Draft';
        }
    }

    // Method untuk memperbarui status dan progress project
    public function updateStatusAndProgress()
    {
        
        $this->attributes['status'] = $this->getStatusAttribute();
        $this->attributes['completion_progress'] = $this->getCompletionProgressAttribute();

        
        if ($this->exists && $this->isDirty()) {
            Log::info('Attempting to save Project ID ' . $this->id . ' with new data: ' . json_encode($this->toArray()));
            try {
                $this->save(); 
            } catch (\Exception $e) {
                Log::error('Error saving project in updateStatusAndProgress: ' . $e->getMessage(), ['project_id' => $this->id, 'project_data' => $this->toArray()]);
            }
        } elseif (!$this->exists) {
            Log::warning('updateStatusAndProgress called on a non-existent project model. This should not happen.');
        } else {
            Log::info('No changes detected for project ID ' . $this->id . ', skipping save in updateStatusAndProgress.');
        }

        $this->refresh(); 
    }
}