<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $userId)
    {

        if(auth()->check() && auth()->id() == $userId){
            

            return $next($request);


        }
    }
}
