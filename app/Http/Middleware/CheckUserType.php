<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $types): Response
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        if (!in_array(auth()->user()->type, $types)) {
            $errorMessage = 'Unauthorized: You are not a ';
            $errorMessage .= implode(' or ', $types) . ' user';
            return response()->json(['error' => $errorMessage], 403);
        }
        return $next($request);
    }
}