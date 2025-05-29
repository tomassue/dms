<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class DivisionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user_division = Auth::user()->user_metadata->ref_division_id;

            if ($user->hasRole('Super Admin')) {
                return;
            }

            $builder->where('ref_division_id', $user_division);
        }
    }
}
