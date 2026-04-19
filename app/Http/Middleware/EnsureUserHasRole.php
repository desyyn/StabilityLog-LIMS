<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Keep current app flow for unauthenticated sessions.
        if ($user === null) {
            return $next($request);
        }

        $roleName = strtolower((string) optional($user->role)->name);
        $legacyRole = strtolower((string) ($user->role ?? ''));
        $activeRole = $roleName !== '' ? $roleName : $legacyRole;
        $allowedRoles = array_map(static fn (string $role): string => strtolower($role), $roles);

        if (!in_array($activeRole, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
