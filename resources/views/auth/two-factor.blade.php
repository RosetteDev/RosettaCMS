<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Rosetta CMS</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --violet-500: #8b5cf6;
            --violet-600: #7c3aed;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-700: #374151;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
        }

        .card-header {
            padding: 2rem 2rem 1rem;
            text-align: center;
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
        }

        .card-body { padding: 2rem; }

        .icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: var(--violet-600);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
        }

        h1 { font-size: 1.5rem; color: #1f2937; margin-bottom: 0.5rem; }
        p { color: var(--gray-700); font-size: 0.95rem; line-height: 1.5; }

        .code-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 2rem;
            letter-spacing: 0.6rem;
            text-align: center;
            font-weight: 700;
            color: #1f2937;
            outline: none;
            transition: border-color 0.2s;
            font-family: 'Inter', monospace;
        }
        .code-input:focus { border-color: var(--violet-500); }

        .btn {
            width: 100%;
            padding: 0.85rem;
            background: var(--violet-600);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: var(--violet-500); }

        .error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .hint {
            font-size: 0.8rem;
            color: #6b7280;
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <h1>Two-Factor Authentication</h1>
            <p>Enter the 6-digit code from your authenticator app to continue.</p>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="error">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.2fa.verify') }}">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label for="code" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700); font-size: 0.85rem;">
                        <i class="fas fa-key mr-2"></i>Authentication code
                    </label>
                    <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                           autocomplete="one-time-code" autofocus required class="code-input">
                    @error('code')
                        <p style="color: #ef4444; font-size: 0.8rem; margin-top: 0.5rem;">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn"><i class="fas fa-check mr-2"></i>Verify</button>
            </form>

            <p class="hint">Can't access your app? Log in and disable two-factor authentication from your profile.</p>
        </div>
    </div>
</body>
</html>
