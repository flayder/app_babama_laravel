<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->key;
        if ($token != env('API_KEY')) {
            return response()->json(['message' => 'Not valid Api Key'], 401);
        }

        return $next($request);
    }
}
