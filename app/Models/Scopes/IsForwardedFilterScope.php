<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * isForwardedFilterScope
 * This scope lets only divisions see requests, documents, etc. that have been forwarded.
 * However, it also lets office admins and superadmin see all requests, documents, etc.
 */
class IsForwardedFilterScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userMetadata = $user->user_metadata;

            // If user has metadata with non-null division and position
            // if ($userMetadata && ($userMetadata->ref_division_id !== null || $userMetadata->ref_position_id !== null)) {
            //     // Only show requests that have been forwarded
            //     $builder->whereHas('forwards', function ($query) use ($user) {
            //         $query->where('ref_division_id', $user->user_metadata->ref_division_id);
            //     });
            // }
            // Else (NULL metadata): show everything (no additional constraints)

            /**
             * * We changed the logic because of past revisions in database structure.
             * The logic about forwarded documents is still here but we will be determining office admins based on user metadata.
             * If the user is not an office admin, then only show requests that have been forwarded.
             */
            if ($userMetadata && $userMetadata->is_office_admin !== '1') {
                // Only show requests that have been forwarded
                $builder->whereHas('forwards', function ($query) use ($user) {
                    $query->where('ref_division_id', $user->user_metadata->ref_division_id);
                });
            }
        }
    }
}
