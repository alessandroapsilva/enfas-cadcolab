<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCorporateAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_logado')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Sessão expirada. Entre novamente.'], 401)
                : redirect('/login')->with('erro', 'Sua sessão expirou. Entre novamente.');
        }

        return $next($request);
    }
}
