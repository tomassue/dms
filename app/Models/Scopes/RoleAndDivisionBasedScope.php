<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * RoleAndDivisionBasedScope
 * This global scope has the same logic from RoleBasedFilterScope
 * The only difference is that in RoleBasedFilterScope, we are using the "role" of the user is under as basis for the scope.
 * * In this scope, we use the "role" AND "division" the user is under as basis. Of course, through activities.
 * 
 * ? Also we can use the main model level for this. Meaning, we apply the division filter at the same level as the activities filter Like:
 * $builder->whereHas('activities', function ($q) use ($roles) {
 *       $q->whereHas('causer', function ($q) use ($roles) {
 *           $q->whereHas('roles', function ($q) use ($roles) {
 *               $q->whereIn('name', $roles);
 *           });
 *       });
 *   })
 *   ->whereHas('user_metadata', function ($q) use ($divisionId) {
 *       $q->where('ref_division_id', $divisionId);
 *   });
 * However, we have to create user_metadata connection.
 * 
 * * The code below, nests both conditions within the activity/causer relationship
 */
class RoleAndDivisionBasedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check() || Auth::user()->hasRole('Super Admin')) {
            return;
        }

        $user = Auth::user();
        $roles = $user->roles->pluck('name')->toArray();
        $divisionId = $user->user_metadata->division->id ?? null; // If the user has no division, it will be null. They can access their inputted outgoing documents.

        $builder->where(function ($query) use ($roles, $divisionId) {
            // Both conditions must be true (AND)
            $query->whereHas('activities', function ($q) use ($roles, $divisionId) {
                $q->whereHas('causer', function ($q) use ($roles, $divisionId) {
                    $q->whereHas('roles', function ($q) use ($roles) {
                        $q->whereIn('name', $roles);
                    })
                        ->whereHas('user_metadata', function ($q) use ($divisionId) {
                            $q->where('ref_division_id', $divisionId);
                        });
                });
            });
        });
    }
}
