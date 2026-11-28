<?php

namespace App\Http\Controllers;

use App\Services\InstallService;
use Illuminate\Http\Request;

/**
 * Optional web installer — same checks, same result as `php artisan install`.
 * All install logic lives in App\Services\InstallService.
 */
class InstallController extends Controller
{
    public function __construct(private readonly InstallService $installer)
    {
    }

    /**
     * Show the system check page (real preflight results, not a static list).
     */
    public function check(Request $request)
    {
        // Installation must be explicitly enabled by the server administrator.
        abort_unless(config('app.install_enabled', false), 403);

        $preflight = $this->installer->preflight(config('database.default'));

        return view('install.check', compact('preflight'));
    }

    /**
     * Show the installation form.
     */
    public function form(Request $request)
    {
        // Installation must be explicitly enabled by the server administrator.
        abort_unless(config('app.install_enabled', false), 403);

        $preflight = $this->installer->preflight(config('database.default'));

        if (!$preflight['all_passed']) {
            return redirect()->route('install.check')->withErrors(['message' => 'Please fix the issues above before continuing.']);
        }

        return view('install.form');
    }

    /**
     * Process the installation.
     */
    public function process(Request $request)
    {
        // Installation must be explicitly enabled by the server administrator.
        abort_unless(config('app.install_enabled', false), 403);

        $dbConnection = $request->input('db_connection', 'sqlite');
        $isSqlite = $dbConnection === 'sqlite';

        $validated = $request->validate([
            'db_connection' => 'required|in:mysql,pgsql,sqlite,sqlsrv',
            'db_host' => 'nullable|required_if:db_connection,mysql,pgsql,sqlsrv|regex:/^[A-Za-z0-9_.\-]+$/|max:255',
            'db_port' => 'nullable|numeric|min:1|max:65535',
            'db_database' => $isSqlite ? 'nullable|string|max:255' : 'required|regex:/^[A-Za-z0-9_\-]+$/|max:64',
            'db_username' => 'nullable|required_if:db_connection,mysql,pgsql,sqlsrv|string|max:64',
            'db_password' => 'nullable|string|max:255',
            'app_url' => 'required|url',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
            'site_name' => 'required|string|max:255',
        ]);

        $result = $this->installer->install([
            'site_name' => $validated['site_name'],
            'app_url' => $validated['app_url'],
            'admin_email' => $validated['admin_email'],
            'admin_password' => $validated['admin_password'],
            'db_connection' => $validated['db_connection'],
            'db_host' => $validated['db_host'] ?? null,
            'db_port' => $validated['db_port'] ?? null,
            'db_database' => $validated['db_database'] ?? null,
            'db_username' => $validated['db_username'] ?? null,
            'db_password' => $validated['db_password'] ?? null,
        ]);

        if (!$result['success']) {
            return back()->withErrors(['db_error' => implode(' ', $result['errors'])]);
        }

        return redirect()->route('login')->with('success', 'Installation completed successfully! You can now log in.');
    }
}
