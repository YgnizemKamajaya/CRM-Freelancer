<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

trait ValidatesCrmRelations
{
    protected function ownedExists(string $table): Exists
    {
        return Rule::exists($table, 'id');
    }

    protected function authorizeOwner(Model $model): void
    {
        abort_unless($model->exists, 404);
    }
}
