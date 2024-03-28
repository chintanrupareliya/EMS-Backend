<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {    
        if ($request->expectsJson()) {
        return response()->json(['error' => 'Unauthenticated1 or Invalid Authentication Token'], 401);
    }

    return redirect()->guest(route('login'));
       
    }
}
