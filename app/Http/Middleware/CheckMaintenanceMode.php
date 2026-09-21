<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah mode maintenance aktif di pengaturan
        $isMaintenance = (string) setting('maintenance_enabled', '0') === '1';

        if (! $isMaintenance) {
            return $next($request);
        }

        // Pengecualian route penting (login, logout, auth, up check)
        if ($request->is('login') ||
            $request->is('logout') ||
            $request->is('password/*') ||
            $request->is('up') ||
            $request->is('build/*') ||
            $request->is('storage/*')
        ) {
            return $next($request);
        }

        // Cek apakah user sedang login
        $user = $request->user();

        if ($user) {
            // Superadmin selalu diizinkan bypass maintenance
            if ($user->hasRole('Superadmin')) {
                return $next($request);
            }

            // Cek role lain yang diizinkan melalui setting
            $allowedRolesJson = setting('maintenance_allowed_roles', '[]');
            $allowedRoles = json_decode($allowedRolesJson, true) ?: [];

            if (! empty($allowedRoles) && $user->hasAnyRole($allowedRoles)) {
                return $next($request);
            }
        }

        // Jika request via AJAX / JSON API
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'maintenance',
                'message' => setting('maintenance_message', 'Sistem sedang dalam pemeliharaan berkala.'),
                'estimated_end_time' => setting('maintenance_end_time'),
            ], 503);
        }

        // Render halaman maintenance 503
        return response()->view('errors.503', [
            'title' => setting('maintenance_title', 'Sistem Dalam Pemeliharaan'),
            'message' => setting('maintenance_message', 'Saat ini kami sedang melakukan peningkatan sistem dan pemeliharaan berkala untuk kenyamanan Anda. Sistem akan segera dapat diakses kembali.'),
            'endTime' => setting('maintenance_end_time'),
        ], 503);
    }
}
