<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
 
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Laisser passer les invités, sera géré par 'auth'
        if (! $user) {
            return $next($request);
        }

        // Vérifie si l'URL commence par /admin
        if ($request->is('admin/*') || $request->is('admin')) {
            if ($user->hasRole('candidat')) {
                return redirect()->intended(route("home"));
            }
        }

        return $next($request);
    }
    
}
