<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Rosetta CMS</title>
    
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
            --green-600: #16a34a;
            --green-800: #14532d;
            --red-500: #ef4444;
            --red-50: #fef2f2;
            --white: #ffffff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-700: #1d4ed8;
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
        .justify-between { justify-content: space-between; }
        .cursor-pointer { cursor: pointer; }
        .w-4 { width: 1rem; }
        .h-4 { height: 1rem; }
        .text-violet-600 { color: var(--violet-600); }
        .rounded { border-radius: 0.25rem; }
        .ml-2 { margin-left: 0.5rem; }
        .text-gray-500 { color: var(--gray-500); }
        .font-medium { font-weight: 500; }
        .hover\\:text-violet-700:hover { color: var(--violet-700); }
        .hover\\:underline:hover { text-decoration: underline; }
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
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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
        .mt-3 { margin-top: 0.75rem; }
        .mt-8 { margin-top: 2rem; }
        .text-gray-400 { color: var(--gray-400); }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        
        /* Info box styles */
        .bg-blue-50 { background: var(--blue-50); }
        .rounded-xl { border-radius: 0.75rem; }
        .p-4 { padding: 1rem; }
        .text-blue-500 { color: var(--blue-500); }
        .text-blue-700 { color: var(--blue-700); }
        .space-y-1 > * + * { margin-top: 0.25rem; }
        .text-blue-500 { color: var(--blue-500); }
        .mr-2 { margin-right: 0.5rem; }
        
        /* Status message styles */
        .bg-green-50 { background: var(--green-50); }
        .border-l-4 { border-left-width: 4px; }
        .border-green-500 { border-color: var(--green-500); }
        .m-6 { margin: 1.5rem; }
        .text-green-500 { color: var(--green-500); }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .mr-3 { margin-right: 0.75rem; }
        .text-green-800 { color: var(--green-800); }
        
        /* Flex gap */
        .space-x-4 > * + * { margin-left: 1rem; }
        .w-8 { width: 2rem; }
        .h-8 { height: 2rem; }
        .rounded-full { border-radius: 9999px; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .font-medium { font-weight: 500; }
        .text-gray-700 { color: var(--gray-700); }
        .w-px { width: 1px; }
        .bg-gray-300 { background: var(--gray-300); }
        
        /* Round check card */
        .check-card { transition: transform 0.3s; }
        .check-card:hover { transform: translateY(-2px); }
        
        /* Password requirements box */
        .bg-blue-50 { background: var(--blue-50); }
        .border-l-4 { border-left-width: 4px; }
        .border-blue-500 { border-color: var(--blue-500); }
        .rounded-r-lg { border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
        .items-start { align-items: flex-start; }
        .mt-0\\.5 { margin-top: 0.125rem; }
        .flex-shrink-0 { flex-shrink: 0; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        
        /* List styles */
        .list-disc { list-style-type: disc; }
        .list-inside { list-style-position: inside; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-gray-500 { color: var(--gray-500); }
        
        /* Grid */
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .gap-5 { gap: 1.25rem; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        
        /* Background colors for checks */
        .bg-green-50 { background: var(--green-50); }
        .bg-red-50 { background: var(--red-50); }
        .border-2 { border-width: 2px; }
        .border-green-500 { border-color: var(--green-500); }
        .border-red-500 { border-color: var(--red-500); }
        .text-green-500 { color: var(--green-500); }
        .text-red-500 { color: var(--red-500); }
        
        /* Status badges */
        .inline-flex { display: inline-flex; }
        .items-center { align-items: center; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .rounded-full { border-radius: 9999px; }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .font-medium { font-weight: 500; }
        .bg-green-100 { background: #dcfce7; }
        .text-green-800 { color: #166534; }
        .bg-red-100 { background: #fee2e2; }
        .text-red-800 { color: #991b1b; }
        .mr-1 { margin-right: 0.25rem; }
        
        /* Card borders */
        .hover\\:border-violet-200:hover { border-color: #ddd6fe; }
        .transition-colors { transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; }
        
        /* Text colors for checks */
        .text-gray-800 { color: var(--gray-800); }
        .text-blue-600 { color: var(--blue-600); }
        .text-purple-600 { color: #9333ea; }
        .text-teal-600 { color: #0d9488; }
        .text-orange-600 { color: #ea580c; }
        .bg-blue-100 { background: #dbeafe; }
        .bg-purple-100 { background: #ede9fe; }
        .bg-teal-100 { background: #ccfbf1; }
        .bg-orange-100 { background: #ffedd5; }
        .mr-3 { margin-right: 0.75rem; }
        
        /* Button states */
        .cursor-not-allowed { cursor: not-allowed; }
        .opacity-50 { opacity: 0.5; }
        
        /* PT/Border top */
        .pt-6 { padding-top: 1.5rem; }
        .border-t { border-top-width: 1px; }
        .border-gray-200 { border-color: var(--gray-200); }
        
        /* Max-width */
        .max-w-2xl { max-width: 42rem; }
        .w-20 { width: 5rem; }
        .h-20 { height: 5rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .text-2xl { font-size: 1.5rem; line-height: 2rem; }
        .w-10 { width: 2.5rem; }
        .h-10 { height: 2.5rem; }
        .rounded-xl { border-radius: 0.75rem; }
        
        /* Responsive */
        @media (max-width: 640px) {
            .text-3xl { font-size: 1.5rem; }
            .text-4xl { font-size: 1.875rem; }
            .p-8 { padding: 1.5rem; }
            .m-6 { margin: 1rem; }
        }
    </style>
</head>
<body>
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-2xl shadow-2xl" style="background: linear-gradient(to bottom right, var(--violet-600), var(--indigo-600));">
                <i class="fas fa-user-plus text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold bg-clip-text text-transparent" style="background: linear-gradient(to right, var(--violet-700), var(--indigo-700)); -webkit-background-clip: text; background-clip: text;">
                Create Account
            </h1>
            <p class="text-gray-600 mt-2">Join Rosetta CMS today</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <form method="POST" action="{{ route('register') }}" class="p-8">
                @csrf

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2 text-sm">
                        <i class="fas fa-user text-gray-400 mr-2"></i>Name
                    </label>
                    <div class="input-group">
                        <i class="fas fa-user text-gray-400" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input class="input-field" 
                               id="name" 
                               type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus 
                               placeholder="John Doe">
                    </div>
                    
                    @error('name')
                        <p class="text-red-500 text-xs mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
                
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
                               placeholder="john@example.com">
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
                        <i class="fas fa-lock text-gray-400 mr-2"></i>Confirm Password
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock text-gray-400" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);"></i>
                        <input class="input-field" 
                               id="password-confirm" 
                               type="password" 
                               name="password_confirmation" 
                               required 
                               placeholder="••••••••">
                        <button type="button" class="absolute right-3 top-1/2 text-gray-400 hover:text-gray-600 focus:outline-none cursor-pointer" onclick="togglePassword('password-confirm', 'toggleIcon2')" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </button>
                    </div>
                    
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Requirements -->
                <div class="mb-6 p-4 bg-blue-50 rounded-xl">
                    <p class="text-sm text-gray-700 font-medium mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>Password Requirements
                    </p>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li>Minimum 8 characters</li>
                        <li>At least one uppercase letter</li>
                        <li>At least one number</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>

            <div class="bg-gray-50 px-8 py-4 text-center border-t">
                <p class="text-gray-600 text-sm">
                    Already have an account? 
                    <a href="{{ route('login') }}" class="text-violet-600 font-medium hover:text-violet-700 hover:underline transition-colors" style="font-size: 0.875rem; font-weight: 500; color: var(--violet-600); text-decoration: none;">
                        Sign in <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </p>
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
