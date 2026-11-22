<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Rosetta CMS</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Inline CSS - Replacement for Tailwind utilities -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        :root {
            --violet-50: #f5f3ff;
            --violet-100: #ede9fe;
            --violet-500: #8b5cf6;
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
            --green-800: #14532d;
            --red-500: #ef4444;
            --red-50: #fef2f2;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(to bottom right, var(--violet-50), var(--blue-50), var(--teal-50));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .w-full {
            width: 100%;
        }
        
        .max-w-md {
            max-width: 28rem;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-8 {
            margin-bottom: 2rem;
        }
        
        .inline-flex {
            display: inline-flex;
        }
        
        .items-center {
            align-items: center;
        }
        
        .justify-center {
            justify-content: center;
        }
        
        .w-16 {
            width: 4rem;
        }
        
        .h-16 {
            height: 4rem;
        }
        
        .mb-4 {
            margin-bottom: 1rem;
        }
        
        .rounded-2xl {
            border-radius: 1rem;
        }
        
        .bg-gradient-to-br {
            background-image: linear-gradient(to bottom right, var(--value), var(--value));
        }
        
        .from-violet-600 {
            --value: var(--violet-600);
        }
        
        .to-indigo-600 {
            --value: var(--indigo-600);
        }
        
        .shadow-2xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .text-3xl {
            font-size: 1.875rem;
            line-height: 2.25rem;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .bg-clip-text {
            -webkit-background-clip: text;
            background-clip: text;
        }
        
        .text-transparent {
            color: transparent;
        }
        
        .text-violet-700 {
            --value: var(--violet-700);
        }
        
        .to-indigo-700 {
            --value: var(--indigo-700);
        }
        
        .text-gray-600 {
            color: var(--gray-600);
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .card {
            background: var(--white);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            border: 1px solid var(--gray-100);
        }
        
        .overflow-hidden {
            overflow: hidden;
        }
        
        .bg-white {
            background: var(--white);
        }
        
        .rounded-xl {
            border-radius: 0.75rem;
        }
        
        .border {
            border-width: 1px;
        }
        
        .border-gray-100 {
            border-color: var(--gray-100);
        }
        
        .bg-green-50 {
            background: var(--green-50);
        }
        
        .border-l-4 {
            border-left-width: 4px;
        }
        
        .border-green-500 {
            border-color: var(--green-500);
        }
        
        .p-4 {
            padding: 1rem;
        }
        
        .m-6 {
            margin: 1.5rem;
        }
        
        .text-green-500 {
            color: var(--green-500);
        }
        
        .text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }
        
        .mr-3 {
            margin-right: 0.75rem;
        }
        
        .text-green-800 {
            color: var(--green-800);
        }
        
        .font-medium {
            font-weight: 500;
        }
        
        .p-8 {
            padding: 2rem;
        }
        
        .mb-6 {
            margin-bottom: 1.5rem;
        }
        
        .text-gray-700 {
            color: var(--gray-700);
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .text-gray-400 {
            color: var(--gray-400);
        }
        
        .mr-2 {
            margin-right: 0.5rem;
        }
        
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
        
        .border-gray-300 {
            border-color: var(--gray-300);
        }
        
        .rounded-xl:focus {
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
            border-color: var(--violet-500);
            background: var(--white);
        }
        
        .focus\\:ring-2 {
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2);
        }
        
        .focus\\:ring-violet-500 {
            --ring-color: var(--violet-500);
            box-shadow: 0 0 0 2px var(--ring-color);
        }
        
        .focus\\:bg-white:focus {
            background: var(--white);
        }
        
        .transition-colors {
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .border-red-500 {
            border-color: var(--red-500);
        }
        
        .text-red-500 {
            color: var(--red-500);
        }
        
        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .flex {
            display: flex;
        }
        
        .items-center {
            align-items: center;
        }
        
        .mr-1 {
            margin-right: 0.25rem;
        }
        
        .mb-6 .flex {
            margin-bottom: 1.5rem;
        }
        
        .justify-between {
            justify-content: space-between;
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
        
        .w-4 {
            width: 1rem;
        }
        
        .h-4 {
            height: 1rem;
        }
        
        .text-violet-600 {
            color: var(--violet-600);
        }
        
        .border-gray-300 {
            border-color: var(--gray-300);
        }
        
        .rounded {
            border-radius: 0.25rem;
        }
        
        .focus\\:ring-violet-500:focus {
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            border-color: var(--violet-600);
        }
        
        .ml-2 {
            margin-left: 0.5rem;
        }
        
        .text-violet-600 {
            color: var(--violet-600);
        }
        
        .font-medium {
            font-weight: 500;
        }
        
        .hover\\:text-violet-700:hover {
            color: var(--violet-700);
        }
        
        .hover\\:underline:hover {
            text-decoration: underline;
        }
        
        .transition-colors {
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
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
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--violet-600) 0%, var(--indigo-600) 100%);
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .mr-2 {
            margin-right: 0.5rem;
        }
        
        .text-base {
            font-size: 1rem;
            line-height: 1.5rem;
        }
        
        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .w-full {
            width: 100%;
        }
        
        .flex {
            display: flex;
        }
        
        .items-center {
            align-items: center;
        }
        
        .justify-center {
            justify-content: center;
        }
        
        .bg-gray-50 {
            background: var(--gray-50);
        }
        
        .px-8 {
            padding-left: 2rem;
            padding-right: 2rem;
        }
        
        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        
        .border-t {
            border-top-width: 1px;
        }
        
        .border-gray-100 {
            border-color: var(--gray-100);
        }
        
        .text-gray-600 {
            color: var(--gray-600);
        }
        
        .mt-3 {
            margin-top: 0.75rem;
        }
        
        .text-gray-500 {
            color: var(--gray-500);
        }
        
        .hover\\:text-violet-600:hover {
            color: var(--violet-600);
        }
        
        .mr-2 {
            margin-right: 0.5rem;
        }
        
        .text-xs {
            font-size: 0.75rem;
            line-height: 1rem;
        }
        
        .mt-8 {
            margin-top: 2rem;
        }
        
        .text-gray-400 {
            color: var(--gray-400);
        }
        
        .text-sm {
            font-size: 0.875rem;
            line-height: 1.25rem;
        }
        
        @media (max-width: 640px) {
            .text-3xl {
                font-size: 1.5rem;
            }
            
            .p-8 {
                padding: 1.5rem;
            }
            
            .m-6 {
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl shadow-2xl" style="background: linear-gradient(to bottom right, var(--violet-600), var(--indigo-600));">
                <i class="fas fa-pen-nib text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent" style="background: linear-gradient(to right, var(--violet-700), var(--indigo-700)); -webkit-background-clip: text; background-clip: text;">
                Welcome Back
            </h1>
            <p class="text-gray-600 mt-2">Sign in to your admin dashboard</p>
        </div>

        <div class="card">
            <!-- Messages -->
            @if(session('status'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 m-6 flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <p class="text-green-800 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="p-8">
                @csrf

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
                               value="{{ old('email') }}" 
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
                        <i class="fas fa-lock text-gray-400 mr-2"></i>Password
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock text-gray-400" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input class="input-field" 
                               id="password" 
                               type="password" 
                               name="password" 
                               required 
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" onclick="togglePassword()" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); cursor: pointer;">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    
                    @error('password')
                        <p class="text-red-500 text-xs mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
                
                <div class="mb-6 flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input class="w-4 h-4 text-violet-600 border-gray-300 rounded focus:ring-violet-500" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700 text-sm">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-violet-600 font-medium hover:text-violet-700 hover:underline transition-colors" style="font-size: 0.875rem; font-weight: 500; color: var(--violet-600);">
                        Forgot password?
                    </a>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Sign In
                </button>
            </form>

            <div class="bg-gray-50 px-8 py-4 text-center border-t" style="background: var(--gray-50); padding-left: 2rem; padding-right: 2rem; padding-top: 1rem; padding-bottom: 1rem; border-top: 1px solid var(--gray-100);">
                @if($registrationEnabled ?? false)
                    <p class="text-gray-600 text-sm">
                        No account? 
                        <a href="{{ route('register') }}" class="text-violet-600 font-medium hover:text-violet-700 hover:underline" style="color: var(--violet-600); font-weight: 500; text-decoration: none;">
                            Create one <i class="fas fa-arrow-right ml-1 text-xs"></i>
                        </a>
                    </p>
                @endif
                <p class="text-gray-600 text-sm mt-3">
                    <a href="{{ url('/') }}" class="text-gray-500 hover:text-violet-600 transition-colors" style="color: var(--gray-500); text-decoration: none;">
                        <i class="fas fa-arrow-left mr-2"></i>Back to site
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
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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
