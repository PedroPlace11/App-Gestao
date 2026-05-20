<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

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
                $tenantId = $request->attributes->get('tenant_id')
                    ?? $request->session()->get('active_company_id')
                    ?? $user->active_company_id;

                $description = match($method) {
                    'post' => 'Criou novo ' . $path,
                    'put', 'patch' => 'Atualizou ' . $path,
                    'delete' => 'Eliminou ' . $path,
                    default => 'Modificou ' . $path,
                };

                // Only enforce custom action gate when it is explicitly defined.
                if (Gate::has('perform-action') && !Gate::allows('perform-action', [$tenantId, $method, $path])) {
                    abort(403, 'Unauthorized action.');
                }

                Activity::causedBy($user)
                    ->withProperties([
                        'tenant_id' => $tenantId,
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
