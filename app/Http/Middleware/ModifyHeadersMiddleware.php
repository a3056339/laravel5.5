<?php

namespace App\Http\Middleware;

use Closure;

class ModifyHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type,X-Requested-With,Access-Token');
        $response->header('Access-Control-Allow-Methods', 'PUT, GET, POST, DELETE, OPTIONS');
        return $response;
    }
}
