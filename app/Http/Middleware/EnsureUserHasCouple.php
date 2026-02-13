<?php

namespace App\Http\Middleware;

use App\Services\CoupleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasCouple
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $coupleService = app(CoupleService::class);
        $couple = $coupleService->getUserCouple($request->user());

        if (! $couple) {
            return redirect()->route('couple.create-or-join')
                ->with('message', 'Please create or join a couple to continue.');
        }

        return $next($request);
    }
}
