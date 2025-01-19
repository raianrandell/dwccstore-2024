<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SuperAdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = Session::get('loginId');
        
        if (!$userId) {
            return redirect('/superadminlogin')->with('fail', 'Please login to access the Super Admin');
        }

        $user = \App\Models\User::find($userId);

        if ($user && $user->user_role === 'Super Admin') {
            return $next($request);
        }

        return redirect('/superadminlogin')->with('fail', 'Unauthorized access.');
    }
}

