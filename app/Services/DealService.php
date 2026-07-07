<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Deal;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DealService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function normalizeStage(array $data): array
    {
        if (($data['stage'] ?? null) === 'won') {
            $data['status'] = 'won';
            $data['probability'] = 100;
            $data['won_at'] = $data['won_at'] ?? now();
            $data['lost_at'] = null;
            $data['lost_reason'] = null;
        } elseif (($data['stage'] ?? null) === 'lost') {
            $data['status'] = 'lost';
            $data['probability'] = 0;
            $data['lost_at'] = $data['lost_at'] ?? now();
            $data['won_at'] = null;
        } else {
            $data['status'] = 'open';
            $data['won_at'] = null;
            $data['lost_at'] = null;
            $data['lost_reason'] = null;
        }

        return $data;
    }

    public function convertToProject(Deal $deal): Project
    {
        return DB::transaction(function () use ($deal) {
            if ($deal->project) {
                return $deal->project;
            }

            $project = Project::create([
                'user_id' => $deal->user_id,
                'client_id' => $deal->client_id,
                'deal_id' => $deal->id,
                'title' => $deal->title,
                'description' => $deal->description,
                'status' => 'planned',
                'priority' => 'medium',
                'budget' => $deal->value,
                'currency' => $deal->currency,
                'progress' => 0,
            ]);

            Activity::create([
                'user_id' => $deal->user_id,
                'client_id' => $deal->client_id,
                'deal_id' => $deal->id,
                'project_id' => $project->id,
                'type' => 'status_update',
                'body' => 'Deal converted to project.',
                'activity_at' => now(),
            ]);

            return $project;
        });
    }
}
