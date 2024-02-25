<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->is_super) {
           return redirect(route('admin.service.show'));
        }

        return $next($request);
    }
}
