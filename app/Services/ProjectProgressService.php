<?php

namespace App\Services;

use App\Models\Project;

class ProjectProgressService
{
    public function sync(Project $project): Project
    {
        $project->loadCount([
            'tasks as active_tasks_count' => fn ($query) => $query->where('status', '!=', 'cancelled'),
            'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'done'),
        ]);

        $total = (int) $project->active_tasks_count;
        $done = (int) $project->completed_tasks_count;
        $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        $updates = ['progress' => $progress];

        if ($project->status !== 'cancelled') {
            if ($total > 0 && $progress === 100) {
                $updates['status'] = 'completed';
                $updates['completed_at'] = $project->completed_at ?? now();
            } elseif ($project->status === 'completed') {
                $updates['status'] = $progress > 0 ? 'in_progress' : 'planned';
                $updates['completed_at'] = null;
            } elseif ($progress > 0 && $project->status === 'planned') {
                $updates['status'] = 'in_progress';
                $updates['completed_at'] = null;
            }
        }

        $project->forceFill($updates)->save();

        return $project->refresh();
    }
}
