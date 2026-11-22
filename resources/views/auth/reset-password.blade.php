<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rosetta CMS</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Inline CSS - Replacement for Tailwind utilities -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        :root {
            --violet-50: #f5f3ff;
            --violet-600: #7c3aed;
            --violet-700: #6d28d9;
            --indigo-600: #4f46e5;
            --indigo-700: #4338ca;
            --blue-50: #eff6ff;
            --teal-50: #f0fdfa;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --green-50: #f0fdf4;
            --green-500: #22c55e;
            --green-600: #16a34a;
            --green-800: #14532d;
            --red-500: #ef4444;
            --red-50: #fef2f2;
            --white: #ffffff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-700: #1d4ed8;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(to bottom right, var(--violet-50), var(--blue-50), var(--teal-50));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .w-full { width: 100%; }
        .max-w-md { max-width: 28rem; }
        .text-center { text-align: center; }
        .mb-8 { margin-bottom: 2rem; }
        .inline-flex { display: inline-flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .w-16 { width: 4rem; }
        .h-16 { height: 4rem; }
        .mb-4 { margin-bottom: 1rem; }
        .rounded-2xl { border-radius: 1rem; }
        .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-bold { font-weight: 700; }
        .bg-clip-text { -webkit-background-clip: text; background-clip: text; }
        .text-transparent { color: transparent; }
        .text-gray-600 { color: var(--gray-600); }
        .mt-2 { margin-top: 0.5rem; }
        .bg-white { background: var(--white); }
        .rounded-xl { border-radius: 0.75rem; }
        .border { border-width: 1px; }
        .border-gray-100 { border-color: var(--gray-100); }
        .p-8 { padding: 2rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-gray-700 { color: var(--gray-700); }
        .font-semibold { font-weight: 600; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .input-group { position: relative; }
        .text-gray-400 { color: var(--gray-400); }
        .mr-2 { margin-right: 0.5rem; }
        .input-field {
            width: 100%;
            padding-left: 2.5rem;
            padding-right: 1rem;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            border-color: var(--violet-500);
            background: var(--white);
        }
        .border-gray-300 { border-color: var(--gray-300); }
        .border-red-500 { border-color: var(--red-500); }
        .text-red-500 { color: var(--red-500); }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .mt-2 { margin-top: 0.5rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .cursor-pointer { cursor: pointer; }
        .text-gray-500 { color: var(--gray-500); }
        .font-medium { font-weight: 500; }
        .hover\\:text-violet-700:hover { color: var(--violet-700); }
        .transition-colors { transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; }
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1rem;
            font-weight: 600;
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            border-radius: 0.75rem;
            width: 100%;
            background: linear-gradient(135deg, var(--violet-600) 0%, var(--indigo-600) 100%);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
        .mr-2 { margin-right: 0.5rem; }
        .text-base { font-size: 1rem; line-height: 1.5rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .bg-gray-50 { background: var(--gray-50); }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .border-t { border-top-width: 1px; }
        .mt-8 { margin-top: 2rem; }
        .text-gray-400 { color: var(--gray-400); }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        
        /* Info box styles */
        .bg-blue-50 { background: var(--blue-50); }
        .rounded-xl { border-radius: 0.75rem; }
        .p-4 { padding: 1rem; }
        .text-blue-500 { color: var(--blue-500); }
        .space-y-1 > * + * { margin-top: 0.25rem; }
        
        @media (max-width: 640px) {
            .text-3xl { font-size: 1.5rem; }
            .p-8 { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl shadow-2xl" style="background: linear-gradient(to bottom right, var(--violet-600), var(--indigo-600));">
                <i class="fas fa-key text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent" style="background: linear-gradient(to right, var(--violet-700), var(--indigo-700)); -webkit-background-clip: text; background-clip: text;">
                Reset Password
            </h1>
            <p class="text-gray-600 mt-2">Choose a new secure password</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <form method="POST" action="{{ route('password.store') }}" class="p-8">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-envelope text-gray-400 mr-2"></i>Email Address
                    </label>
                    <div class="input-group">
                        <i class="fas fa-envelope text-gray-400" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input class="input-field" 
                               id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $email) }}"
                               required
                               autofocus
                               placeholder="admin@example.com">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>New Password
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock text-gray-400" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input class="input-field" 
                               id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="new-password"
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer" onclick="togglePassword('password', 'toggleIcon1')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>Confirm New Password
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock text-gray-400" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input class="input-field" 
                               id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer" onclick="togglePassword('password_confirmation', 'toggleIcon2')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="mb-6 p-4 bg-blue-50 rounded-xl">
                    <p class="text-sm text-gray-700 font-medium mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>Requirements
                    </p>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li>Minimum 8 characters</li>
                        <li>At least one uppercase letter</li>
                        <li>At least one number</li>
                    </ul>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Reset Password
                </button>
            </form>

            <div class="bg-gray-50 px-8 py-4 text-center border-t">
                <p class="text-gray-600 text-sm">
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-violet-600 transition-colors" style="color: var(--gray-500); text-decoration: none;">
                        <i class="fas fa-arrow-left mr-2"></i>Back to login
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-8 text-gray-400 text-sm">
            <p>Rosetta CMS &copy; {{ date('Y') }} &bull; Professional Content Management System</p>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
