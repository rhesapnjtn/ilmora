<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $maintenance = (bool) Setting::get('maintenance_mode', false);
        } catch (\Throwable $e) {
            $maintenance = false;
        }

        if (! $maintenance) {
            return $next($request);
        }

        if ($request->is('up') || $request->is('build/*') || $request->is('storage/*') || $request->is('docs/*')) {
            return $next($request);
        }

        if ($request->is('api/auth/*') || $request->is('api/password/*') || $request->is('api/payment/webhook*')) {
            return $next($request);
        }

        if ($request->is('api/admin/*')) {
            $user = auth('sanctum')->user();
            if ($user !== null && $user->role === 'admin') {
                return $next($request);
            }
        }

        if ($request->is('api/*')) {
            return response()->json([
                'message' => 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.',
            ], 503);
        }

        if ($request->is('admin/*')) {
            $user = auth('sanctum')->user();
            if ($user !== null && $user->role === 'admin') {
                return $next($request);
            }
        }

        return response()->view('maintenance', [], 503);
    }
}
