<?php

use App\Exceptions\BusinessLogicException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            return back()->with('error', 'El recurso no fue encontrado.');
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            return redirect()->route('login');
        });

        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            return back()->with('error', 'No tienes autorización para realizar esta acción.');
        });

        $exceptions->render(function (BusinessLogicException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => [$e->key => [$e->getMessage()]]
                ], $e->getCode());
            }

            return back()
                ->withInput()
                ->withErrors([$e->key => $e->getMessage()]);
        });
    })->create();
