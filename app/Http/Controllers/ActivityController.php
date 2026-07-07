<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesCrmRelations;
use App\Models\Activity;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityController extends Controller
{
    use ValidatesCrmRelations;

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['nullable', $this->ownedExists('clients')],
            'deal_id' => ['nullable', $this->ownedExists('deals')],
            'project_id' => ['nullable', $this->ownedExists('projects')],
            'invoice_id' => ['nullable', $this->ownedExists('invoices')],
            'type' => ['required', Rule::in(array_keys(Activity::TYPES))],
            'body' => ['required', 'string', 'max:5000'],
            'activity_at' => ['nullable', 'date'],
        ]);

        $activity = Activity::create($data + [
            'activity_at' => $data['activity_at'] ?? now(),
        ]);

        if ($activity->client_id && in_array($activity->type, Activity::CONTACT_TYPES, true)) {
            Client::whereKey($activity->client_id)
                ->update(['last_contacted_at' => $activity->activity_at ?? now()]);
        }

        return back()->with('status', 'Activity saved.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $this->authorizeOwner($activity);
        $activity->delete();

        return back()->with('status', 'Activity deleted.');
    }
}
