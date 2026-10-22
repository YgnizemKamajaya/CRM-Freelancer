<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait ValidatesCrmRelations
{
    protected function ownedExists(string $table): Exists
    {
        $rule = Rule::exists($table, 'id');
        if (in_array($table, ['clients', 'deals', 'projects', 'invoices', 'tasks', 'activities', 'journal_entries'], true)) {
            $existingId = $this->existingRelationId(Str::singular($table).'_id');
            $rule->where(fn ($query) => $query->whereNull('deleted_at')->when($existingId, fn ($query) => $query->orWhere('id', $existingId)));
        }

        return $rule;
    }

    /** Active choices plus the record's unchanged historical relationship on edit. */
    protected function relationOptions(string $model, string $foreignKey): Builder
    {
        $existingId = $this->existingRelationId($foreignKey);

        return $model::withTrashed()->where(fn ($query) => $query->whereNull('deleted_at')
            ->when($existingId, fn ($query) => $query->orWhere('id', $existingId)));
    }

    private function existingRelationId(string $foreignKey): mixed
    {
        foreach (request()->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                return $parameter->getAttribute($foreignKey);
            }
        }

        return null;
    }

    protected function authorizeOwner(Model $model): void
    {
        abort_unless($model->exists, 404);
    }
}
