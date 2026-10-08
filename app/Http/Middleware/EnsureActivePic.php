<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Token Sanctum tetap valid walau akun dinonaktifkan admin, jadi status akun
 * dicek ulang di setiap request API mobile.
 */
class EnsureActivePic
{
    public const ROLE_PIC = 2;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active || (int) $user->role_id !== self::ROLE_PIC) {
            $token = $user?->currentAccessToken();
            if ($token instanceof PersonalAccessToken) {
                $token->delete();
            }

            return response()->json([
                'success' => false,
                'message' => 'Sesi berakhir atau akun Anda dinonaktifkan. Silakan login kembali.',
            ], 401);
        }

        return $next($request);
    }
}
