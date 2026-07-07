<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePinUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((bool) $request->session()->get('crm_unlocked', false)) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}
