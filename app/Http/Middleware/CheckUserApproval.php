<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckUserApproval
{
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated and approved
        if (Auth::check() && !Auth::user()->approved) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['Your account is not approved by admin.']);
        }
        return $next($request);
    }
}
