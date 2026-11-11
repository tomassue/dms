<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows access to any authenticated user that is assigned to an office (a.k.a. role).
 *
 * Replaces the hardcoded `role:Super Admin|APOO|...` list in routes/web.php so that
 * newly created offices/roles are automatically granted access without editing the routes.
 */
class EnsureUserHasOffice
{
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        $user = $request->user($guard);

        if (!$user) {
            throw UnauthorizedException::notLoggedIn();
        }

        // User must be assigned to at least one office (role) under the current guard.
        $guardName = $guard ?? config('auth.defaults.guard');

        $hasOffice = $user->roles()
            ->where('guard_name', $guardName)
            ->exists();

        if (!$hasOffice) {
            throw UnauthorizedException::forRoles([]);
        }

        return $next($request);
    }
}
