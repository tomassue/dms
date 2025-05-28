<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OfficeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     * This scope filters record based on the office the user is under.
     * But if the user is Super Admin, it will not apply the scope.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('Super Admin')) {
                return;
            }

            $builder->where('office_id', $user->roles()->first()->id);
        }
    }
}
