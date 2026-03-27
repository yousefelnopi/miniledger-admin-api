<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Http\Request;

Route::get('/reset-password-view', function (Request $request) {
    // هنباصي التوكن والإيميل اللي جايين من اللينك لصفحة الـ Blade
    return view('reset-password', [
        'token' => $request->token,
        'email' => $request->email
    ]);
});
