<?php

namespace App\Http\Middleware;

use Closure;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(auth()->check()){
            if(auth()->user()->role->name == 'admin' || auth()->user()->role->name == 'super_admin'){
                
                return $next($request);

            }

        }
        
        abort(404);
    }
}
