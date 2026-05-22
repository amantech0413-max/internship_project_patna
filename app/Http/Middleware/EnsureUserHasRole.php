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

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $slug = $user->roleSlug();

        foreach ($roles as $allowed) {
            // Legacy route name: any assignable staff role
            if ($allowed === 'college_coordinator') {
                if ($user->roleModel?->is_assignable) {
                    return $next($request);
                }

                continue;
            }

            if ($slug === $allowed) {
                return $next($request);
            }
        }

        return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
    }
}
