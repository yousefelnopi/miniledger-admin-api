<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; // 1. لازم نستدعي المودل هنا

class LogLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            /** @var User $user */ // 2. السطر ده عشان الـ Intelephense يفهم وميديش Error
            $user = Auth::user();

            // 3. (تحسين الأداء) نحدث التاريخ بس لو عدى دقيقة على آخر ظهور
            // لو مش عايز الشرط ده وعايزه يحدث لحظياً، شيل جملة if وسيب السطرين اللي جواها
            if ($user->last_seen_at < now()->subMinute(1)) {
                $user->last_seen_at = now();
                $user->save();
            }
        }

        return $next($request);
    }
}
