<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsAgent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
            Route::middleware('web')->prefix('agent')->name('agent.')->group(base_path('routes/agent.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('agent', 'agent/*')
            ? route('agent.login')
            : route('admin.login'));

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'agent' => EnsureUserIsAgent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    // TEMPORAIRE : à retirer une fois le problème trouvé
    $exceptions->render(function (\Throwable $e, $request) {
        if (! env('SHOW_ERRORS')
            || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
            || $e instanceof \Illuminate\Validation\ValidationException
            || $e instanceof \Illuminate\Auth\AuthenticationException) {
            return null;
        }
        return response('<pre style="white-space:pre-wrap">'
            .e(get_class($e).' : '.$e->getMessage().' — '.basename($e->getFile()).':'.$e->getLine())
            .'</pre>', 500);
    });
})->create();
