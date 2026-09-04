<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'owner') {
            return $next($request);
        }

        // Kalau Guest coba-coba akses route sensitif:
        return redirect()->back()->with('error', 'Akses ditolak! Anda berada dalam mode Guest Read-Only.');
    }
}
