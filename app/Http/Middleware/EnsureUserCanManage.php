<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->canManage()) {
            abort(403, __('auth.forbidden'));
        }

        return $next($request);
    }
}
