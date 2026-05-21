<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $allowed = array_map(fn ($r) => UserRole::from($r), $roles);

        if (! in_array($user->role, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
