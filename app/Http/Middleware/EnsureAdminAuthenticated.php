<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_logado')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Não autenticado.'], 401)
                : redirect()->route('login');
        }

        return $next($request);
    }
}
