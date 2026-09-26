<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(! $request->user() || $request->user()->role !== 'admin'){
            return response()->json([
                'message'=> 'Forbidden: Only administrators can perform this action.'
            ], 403);
        }
        return $next($request);
    }
}
