<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmApiAccess
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if (! in_array($request->user()?->role, ['admin', 'editor', 'content'], true)) {
            return response()->json([
                'message' => 'Цей користувач не має доступу до CRM.',
            ], 403);
        }

        if ($request->bearerToken() !== null && ! $request->user()?->tokenCan('crm:access')) {
            return response()->json([
                'message' => 'Ця сесія не має доступу до мобільної CRM.',
            ], 403);
        }

        return $next($request);
    }
}
