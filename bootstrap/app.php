<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Publish posts whose scheduled publish date has arrived, and
        // unpublish posts whose expiry date has arrived.
        $schedule->command('posts:publish-due')->everyMinute();
        $schedule->command('posts:expire')->everyMinute();

        // Email the site admin a digest of recently published posts.
        $schedule->command('posts:digest')->dailyAt('08:00');

        // Permanently delete trashed posts older than 30 days.
        $schedule->command('posts:purge')->dailyAt('04:00');

        // Create a backup of the database and media library every day.
        $schedule->command('backups:create')->dailyAt('03:00');
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SetActiveLanguage::class);
        $middleware->append(\App\Http\Middleware\CurrentSite::class);
        $middleware->append(\App\Http\Middleware\CheckSiteIpRules::class);
        $middleware->append(\App\Http\Middleware\RedirectMiddleware::class);
        $middleware->append(\App\Http\Middleware\CheckMaintenanceMode::class);
        $middleware->append(\App\Http\Middleware\InlineEdit::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\TrimStringsWithLimits::class);
        $middleware->append(\App\Http\Middleware\RecordPageView::class);
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'api.key' => \App\Http\Middleware\AuthenticateApiKey::class,
            'log.activity' => \App\Http\Middleware\LogAdminActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render a branded 410 page for gone resources.
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 410) {
                return response()
                    ->view('errors.410', ['status' => 410], 410)
                    ->header('Content-Type', 'text/html; charset=UTF-8');
            }

            return null;
        });
    })->create();
