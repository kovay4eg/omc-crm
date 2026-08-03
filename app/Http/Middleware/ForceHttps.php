<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->isSecure()) {
            return redirect()->to(
                'https://'.$request->getHttpHost().$request->getRequestUri(),
                301,
            );
        }

        return $next($request);
    }
}
