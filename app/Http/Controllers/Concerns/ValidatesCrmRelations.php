<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

trait ValidatesCrmRelations
{
    protected function ownedExists(string $table): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists($table, 'id')->where('user_id', auth()->id());
    }

    protected function authorizeOwner(Model $model): void
    {
        abort_unless((int) $model->getAttribute('user_id') === (int) auth()->id(), 404);
    }
}
