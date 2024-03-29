<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user->status && $user->email_verification && $user->sms_verification) {
            return $next($request);
        }

        return redirect(route('user.check'));
    }
}
