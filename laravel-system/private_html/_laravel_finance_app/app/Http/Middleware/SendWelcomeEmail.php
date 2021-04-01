<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SendWelcomeEmail
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

        if (is_null(Auth::user()->welcome_email_sent_at)) {
            
            Auth::user()->notify(new \App\Notifications\WelcomeNotification());

            Auth::user()->welcome_email_sent_at = now();
            Auth::user()->save();
        }

        return $next($request);
    }
}
