<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * RoleBasedFilterScope
 * This scope helps to filter records based on the roles of the user.
 * For example, users under role (office) APO created a record and only users under role APO should be able to see that record.
 * * This behavior is based on the activity_log table.
 * * The causer_id is the id of the user who created the record then we check the role of that user to validate and apply the scope.
 */
class RoleBasedFilterScope implements Scope
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

        //* Explaination of the query:
        //* This is a complex query that does the following:
        // This doesn't require relationship because it joins table in raw.
        // 1. Selects all activity logs where the subject_id is the id of the model and the subject_type is the class name of the model
        // 2. Joins the activity_log table with the users table to get the causer_id
        // 3. Joins the model_has_roles table with the users table to get the role_id
        // 4. Joins the roles table with the model_has_roles table to get the name of the role
        // 5. Checks if the name of the role is in the $roles array
        // $builder->whereExists(function ($query) use ($model, $roles) {
        //     $query->select(DB::raw(1))
        //         ->from('activity_log')
        //         ->join('users', 'activity_log.causer_id', '=', 'users.id')
        //         ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        //         ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
        //         ->whereColumn('activity_log.subject_id', $model->getTable() . '.id')
        //         ->where('activity_log.subject_type', get_class($model))
        //         ->where('activity_log.causer_type', User::class)
        //         ->whereIn('roles.name', $roles);
        // });

        /**
         ** This is a simpler query that does the same thing as the above query.
         * However, we need to add activities() relationship to each model you wish to apply this scope to.
         */
        $builder->whereHas('activities', function ($query) use ($roles) {
            $query->whereHas('causer', function ($q) use ($roles) {
                $q->whereHas('roles', function ($q) use ($roles) {
                    $q->whereIn('name', $roles);
                });
            });
        });

        // TODO: Records added by the super admin should be visible to all roles.
    }
}
