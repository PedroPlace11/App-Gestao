<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

class LogActivityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log API requests that modify data
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && $request->is('api/*')) {
            $user = $request->user();

            if ($user) {
                $method = strtolower($request->method());
                $path = str_replace('api/v1/', '', $request->path());

                $description = match($method) {
                    'post' => 'Criou novo ' . $path,
                    'put', 'patch' => 'Atualizou ' . $path,
                    'delete' => 'Eliminou ' . $path,
                    default => 'Modificou ' . $path,
                };

                Activity::causedBy($user)
                    ->withProperties([
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'method' => $request->method(),
                        'path' => $request->path(),
                    ])
                    ->log($description);
            }
        }

        return $next($request);
    }
}
