<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosetta CMS - Installation Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; background: #f5f5f5; color: #333; line-height: 1.6; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 20px; font-size: 28px; }
        h2 { color: #34495e; margin: 30px 0 15px; font-size: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .check-item { padding: 12px; margin-bottom: 10px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; }
        .check-item.passed { background: #d4edda; color: #155724; }
        .check-item.failed { background: #f8d7da; color: #721c24; }
        .check-item .name { font-weight: 600; }
        .check-item .message { font-size: 14px; opacity: 0.9; }
        .status-icon { font-size: 18px; }
        .passed .status-icon { color: #28a745; }
        .failed .status-icon { color: #dc3545; }
        .button { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: 600; transition: background 0.3s; margin-top: 30px; }
        .button:hover { background: #2980b9; }
        .button:disabled { background: #95a5a6; cursor: not-allowed; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .required { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Installing Rosetta CMS</h1>
        
        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <h2>System Requirements</h2>
        
        <h3><span class="required">*</span> PHP Version</h3>
        <div class="check-item {{ $checks['php_version']['passed'] ? 'passed' : 'failed' }}">
            <div>
                <span class="name">{{ $checks['php_version']['name'] }}</span><br>
                <span class="message">{{ $checks['php_version']['message'] }}</span>
            </div>
            <span class="status-icon">{{ $checks['php_version']['passed'] ? '✓' : '✗' }}</span>
        </div>

        <h3><span class="required">*</span> PHP Extensions</h3>
        @foreach($checks['extensions']['extensions'] as $extension)
            <div class="check-item {{ $extension['passed'] ? 'passed' : 'failed' }}">
                <div>
                    <span class="name">{{ $extension['name'] }}</span><br>
                    <span class="message">{{ $extension['message'] }}</span>
                </div>
                <span class="status-icon">{{ $extension['passed'] ? '✓' : '✗' }}</span>
            </div>
        @endforeach

        <h3><span class="required">*</span> Directory Permissions</h3>
        @foreach($checks['directories']['directories'] as $dir)
            <div class="check-item {{ $dir['passed'] ? 'passed' : 'failed' }}">
                <div>
                    <span class="name">{{ $dir['name'] }}</span><br>
                    <span class="message">{{ $dir['message'] }}</span>
                </div>
                <span class="status-icon">{{ $dir['passed'] ? '✓' : '✗' }}</span>
            </div>
        @endforeach

        <h3><span class="required">*</span> Dependencies</h3>
        <div class="check-item {{ $checks['dependencies']['passed'] ? 'passed' : 'failed' }}">
            <div>
                <span class="name">{{ $checks['dependencies']['name'] }}</span><br>
                <span class="message">{{ $checks['dependencies']['message'] }}</span>
            </div>
            <span class="status-icon">{{ $checks['dependencies']['passed'] ? '✓' : '✗' }}</span>
        </div>

        <div style="margin-top: 30px; padding-top: 30px; border-top: 1px solid #eee; text-align: center;">
            <p style="margin-bottom: 20px;">
                @if($checks['all_passed'])
                    <span style="color: #28a745; font-weight: bold;">All checks passed!</span> You can proceed with the installation.
                @else
                    <span style="color: #dc3545; font-weight: bold;">Some checks failed.</span> Please fix the issues above before continuing.
                @endif
            </p>

            @if($checks['all_passed'])
                <a href="{{ route('install') }}" class="button">Start Installation</a>
            @else
                <button class="button" disabled>Fix Issues First</button>
            @endif
        </div>
    </div>
</body>
</html>
