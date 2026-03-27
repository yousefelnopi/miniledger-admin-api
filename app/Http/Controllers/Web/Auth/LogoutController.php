<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Logout Process
     */
    public function logout(Request $request)
    {
        // حذف الـ Token الخاص بالجلسة الحالية
        $request->user()->currentAccessToken()->delete();

        // استخدام الـ Trait لإرجاع الرد الموحد
        return $this->successResponse(null, 'Logout Successfully');
    }
}
