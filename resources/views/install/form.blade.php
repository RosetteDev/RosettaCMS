<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosetta CMS - Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #f5f5f5; color: #333; line-height: 1.6; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 30px; text-align: center; font-size: 28px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #34495e; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 15px; transition: border-color 0.3s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #3498db; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        .button { width: 100%; padding: 14px; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.3s; margin-top: 10px; }
        .button:hover { background: #2980b9; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success-message { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Rosetta CMS - Database Setup</h1>

        @if(session('success'))
            <div class="success-message">
                {{ session('success') }}
                <a href="{{ route('login') }}" style="color: #155724; text-decoration: underline; margin-left: 10px;">Go to Login &rarr;</a>
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('install.process') }}">
            @csrf

            <h2 style="color: #34495e; margin: 30px 0 20px; font-size: 18px;">Database Configuration</h2>

            <div class="form-group">
                <label for="db_connection">Database Driver</label>
                <select name="db_connection" id="db_connection">
                    <option value="mysql" {{ old('db_connection', 'mysql') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                    <option value="pgsql" {{ old('db_connection') == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                    <option value="sqlite" {{ old('db_connection') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="db_host">Host</label>
                    <input type="text" name="db_host" id="db_host" value="{{ old('db_host', '127.0.0.1') }}" required>
                </div>
                <div class="form-group">
                    <label for="db_port">Port</label>
                    <input type="number" name="db_port" id="db_port" value="{{ old('db_port', '3306') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="db_database">Database Name</label>
                <input type="text" name="db_database" id="db_database" value="{{ old('db_database') }}" required>
            </div>

            <div class="form-group">
                <label for="db_username">Username</label>
                <input type="text" name="db_username" id="db_username" value="{{ old('db_username') }}" required>
            </div>

            <div class="form-group">
                <label for="db_password">Password</label>
                <input type="password" name="db_password" id="db_password" value="{{ old('db_password') }}">
            </div>

            <h2 style="color: #34495e; margin: 30px 0 20px; font-size: 18px;">Site Configuration</h2>

            <div class="form-group">
                <label for="app_url">Site URL</label>
                <input type="url" name="app_url" id="app_url" value="{{ old('app_url', 'https://crm.rosettedevelopment.com') }}" required>
            </div>

            <div class="form-group">
                <label for="site_name">Site Name</label>
                <input type="text" name="site_name" id="site_name" value="{{ old('site_name', 'Rosetta CMS') }}" required>
            </div>

            <h2 style="color: #34495e; margin: 30px 0 20px; font-size: 18px;">Admin Account</h2>

            <div class="form-group">
                <label for="admin_email">Admin Email</label>
                <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required>
            </div>

            <div class="form-group">
                <label for="admin_password">Admin Password</label>
                <input type="password" name="admin_password" id="admin_password" required>
            </div>

            <div class="form-group">
                <label for="admin_password_confirmation">Confirm Password</label>
                <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required>
            </div>

            <button type="submit" class="button">Run Installation</button>
        </form>

        <div class="back-link">
            <a href="{{ route('install.check') }}">&larr; Back to Check</a>
        </div>
    </div>
</body>
</html>
