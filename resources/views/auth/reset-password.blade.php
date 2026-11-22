<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rosetta CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: theme('colors.gray.400');
        }
        .input-field {
            transition: all 0.2s;
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            border-color: theme('colors.violet.500');
        }
        .btn-primary {
            transition: all 0.3s;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.3);
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-violet-50 via-blue-50 to-teal-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 shadow-2xl">
                <i class="fas fa-key text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-violet-700 to-indigo-700 bg-clip-text text-transparent">
                Reset Password
            </h1>
            <p class="text-gray-600 mt-2">Choose a new secure password</p>
        </div>

        <div class="card bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <form method="POST" action="{{ route('password.store') }}" class="p-8">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-envelope text-gray-400 mr-2"></i>Email Address
                    </label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors @error('email') border-red-500 @enderror"
                               id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $email) }}"
                               required
                               autofocus
                               placeholder="admin@example.com">

                        @error('email')
                            <p class="text-red-500 text-xs mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-lock text-gray-400 mr-2"></i>New Password
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input class="input-field w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors @error('password') border-red-500 @enderror"
                               id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="new-password"
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="togglePassword('password', 'toggleIcon1')">
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
                        <i class="fas fa-lock"></i>
                        <input class="input-field w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors @error('password_confirmation') border-red-500 @enderror"
                               id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
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

                <button type="submit" class="btn-primary text-white text-base font-semibold py-3 px-6 rounded-xl w-full shadow-lg flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>
                    Reset Password
                </button>
            </form>

            <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-100">
                <p class="text-gray-600 text-sm">
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-violet-600 transition-colors">
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
