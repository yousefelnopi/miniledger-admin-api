<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'CheckAdmin' => \App\Http\Middleware\CheckAdmin::class,
            'CheckCashier' => \App\Http\Middleware\CheckCashier::class,
        ]);
        $middleware->append(\App\Http\Middleware\LogLastSeen::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // لو الريكويست مسار مش موجود أصلاً (زي api/productssss)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',   // ✅
                    'message' => 'Endpoint Not Found',
                    'data'    => null
                ], 404);
            }
        });

        // لو الريكويست بيدور على ID منتج أو قسم مش موجود في الداتابيز
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                // هنا نقدر نجيب اسم الموديل اللي مش موجود عشان الرسالة تبقى ديناميكية وشيك
                $modelName = class_basename($e->getModel());

                return response()->json([
                    'status'  => 'error',   // ✅
                    'message' => "{$modelName} Not Found in Database",
                    'data'    => null
                ], 404);
            }
        });
    })->create();
