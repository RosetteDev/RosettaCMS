<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosetta CMS - System Check</title>
    
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
            --violet-800: #5b37c8;
            --indigo-600: #4f46e5;
            --indigo-700: #4338ca;
            --blue-50: #eff6ff;
            --blue-100: #dbeafe;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
            --blue-700: #1d4ed8;
            --teal-50: #f0fdfa;
            --teal-600: #0d9488;
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
            --green-100: #dcfce7;
            --green-500: #22c55e;
            --green-600: #16a34a;
            --green-700: #15803d;
            --green-800: #14532d;
            --red-50: #fef2f2;
            --red-100: #fee2e2;
            --red-500: #ef4444;
            --red-600: #dc2626;
            --red-700: #b91c1c;
            --red-800: #991b1b;
            --white: #ffffff;
            --orange-100: #ffedd5;
            --orange-600: #ea580c;
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
        .max-w-4xl { max-width: 56rem; }
        .text-center { text-align: center; }
        .mb-8 { margin-bottom: 2rem; }
        .inline-flex { display: inline-flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .w-20 { width: 5rem; }
        .h-20 { height: 5rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .rounded-2xl { border-radius: 1rem; }
        .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); }
        .text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
        .font-bold { font-weight: 700; }
        .text-gray-600 { color: var(--gray-600); }
        .bg-white { background: var(--white); }
        .rounded-3xl { border-radius: 1.5rem; }
        .border { border-width: 1px; }
        .border-gray-100 { border-color: var(--gray-100); }
        .p-8 { padding: 2rem; }
        .text-2xl { font-size: 1.5rem; line-height: 2rem; }
        .text-gray-800 { color: var(--gray-800); }
        .mb-4 { margin-bottom: 1rem; }
        .text-gray-700 { color: var(--gray-700); }
        .mb-2 { margin-bottom: 0.5rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-green-600 { color: var(--green-600); }
        .text-red-600 { color: var(--red-600); }
        .bg-green-100 { background: var(--green-100); }
        .bg-red-100 { background: var(--red-100); }
        .p-4 { padding: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .items-center { align-items: center; }
        .mr-3 { margin-right: 0.75rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .font-medium { font-weight: 500; }
        .text-green-800 { color: var(--green-800); }
        .text-red-800 { color: var(--red-800); }
        .mt-6 { margin-top: 1.5rem; }
        .flex { display: flex; }
        .items-start { align-items: flex-start; }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .font-semibold { font-weight: 600; }
        .text-gray-600 { color: var(--gray-600); }
        .mt-3 { margin-top: 0.75rem; }
        .w-8 { width: 2rem; }
        .h-8 { height: 2rem; }
        .rounded-full { border-radius: 9999px; }
        .text-white { color: var(--white); }
        .flex-shrink-0 { flex-shrink: 0; }
        .justify-center { justify-content: center; }
        .items-center { align-items: center; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .font-bold { font-weight: 700; }
        .bg-violet-600 { background: var(--violet-600); }
        .space-x-4 > * + * { margin-left: 1rem; }
        .w-px { width: 1px; }
        .bg-gray-300 { background: var(--gray-300); }
        .text-gray-700 { color: var(--gray-700); }
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .gap-4 { gap: 1rem; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .text-green-700 { color: var(--green-700); }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .font-medium { font-weight: 500; }
        .mb-4 { margin-bottom: 1rem; }
        .bg-gray-50 { background: var(--gray-50); }
        .border-t { border-top-width: 1px; }
        .border-gray-200 { border-color: var(--gray-200); }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .btn-primary { transition: all 0.3s; background: linear-gradient(135deg, var(--violet-600) 0%, var(--indigo-600) 100%); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.3); }
        .text-white { color: var(--white); }
        .text-lg { font-size: 1.25rem; line-height: 1.75rem; }
        .font-semibold { font-weight: 600; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .w-full { width: 100%; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .mr-3 { margin-right: 0.75rem; }
        .text-violet-600 { color: var(--violet-600); }
        .font-medium { font-weight: 500; }
        .hover\:underline:hover { text-decoration: underline; }
        .ml-1 { margin-left: 0.25rem; }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .border-t { border-top-width: 1px; }
        .border-gray-100 { border-color: var(--gray-100); }
        .text-gray-500 { color: var(--gray-500); }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .mr-2 { margin-right: 0.5rem; }
        .inline-flex { display: inline-flex; }
        .items-center { align-items: center; }
        .hover\:text-violet-600:hover { color: var(--violet-600); }
        .font-medium { font-weight: 500; }
        .transition-colors { transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; }
        .mt-8 { margin-top: 2rem; }
        .text-gray-400 { color: var(--gray-400); }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        
        /* Selection styles */
        ::selection { background: var(--violet-200); }
        ::-moz-selection { background: var(--violet-200); }
        
        /* Responsive */
        @media (max-width: 768px) {
            .text-4xl { font-size: 1.875rem; }
            .mb-8 { margin-bottom: 1.5rem; }
            .max-w-4xl { max-width: 100%; }
            .grid-cols-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="w-full max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-2xl" style="background: linear-gradient(to bottom right, var(--violet-600), var(--indigo-600)); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <i class="fas fa-check-double text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800">System Check</h1>
            <p class="text-gray-600">Verifying server requirements for Rosetta CMS</p>
        </div>

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <div class="p-8">
                <!-- Summary -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Server Requirements</h2>
                    @if($preflight['all_passed'])
                        <p class="text-gray-600">Your server meets all the requirements for Rosetta CMS.</p>
                    @else
                        <p class="text-gray-600">Some requirements are not met. Fix the issues below before continuing.</p>
                    @endif
                </div>

                <!-- Checks -->
                <div class="grid grid-cols-1 gap-4 mb-8">
                    @foreach($preflight['checks'] as $check)
                        <div class="flex items-center p-4 rounded-xl" style="background-color: {{ $check['passed'] ? 'var(--green-100)' : 'var(--red-100)' }}; border-left: 4px solid {{ $check['passed'] ? 'var(--green-500)' : 'var(--red-500)' }};">
                            <i class="fas {{ $check['passed'] ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600' }} text-2xl mr-3 flex-shrink-0"></i>
                            <div>
                                <p class="text-lg font-medium" style="color: {{ $check['passed'] ? 'var(--green-800)' : 'var(--red-800)' }};">{{ $check['name'] }}</p>
                                <p class="text-sm mt-1" style="color: {{ $check['passed'] ? 'var(--green-700)' : 'var(--red-700)' }};">{{ $check['message'] }}</p>
                                @if(!empty($check['missing']))
                                    <p class="text-xs mt-1" style="color: var(--red-600);">Missing: {{ implode(', ', $check['missing']) }}</p>
                                @endif
                                @if(!empty($check['unwritable']))
                                    <p class="text-xs mt-1" style="color: var(--red-600);">Unwritable: {{ implode(', ', $check['unwritable']) }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    @if($preflight['all_passed'])
                        <a href="{{ route('install') }}" class="btn-primary text-white text-lg font-semibold py-4 px-8 rounded-xl w-full sm:w-auto shadow-lg flex items-center justify-center">
                            <i class="fas fa-arrow-right mr-3"></i>
                            Continue Setup
                        </a>
                    @else
                        <span class="text-gray-500 text-center sm:text-left flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Resolve the issues above to continue.
                        </span>
                    @endif
                    <a href="{{ route('install.check') }}" class="text-gray-700 hover:text-violet-600 font-medium transition-colors flex items-center justify-center sm:justify-start">
                        <i class="fas fa-redo mr-2"></i>
                        Refresh Check
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-100">
                <p class="text-gray-500 text-sm">
                    <i class="fas fa-terminal mr-2"></i>
                    Prefer the command line? Run <code>php artisan install</code> for a guided CLI setup.
                </p>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-8">
            <a href="{{ route('install') }}" class="inline-flex items-center text-gray-600 hover:text-violet-600 font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Installation
            </a>
        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-8 text-gray-400 text-sm">
            <p>Rosetta CMS &copy; {{ date('Y') }} &bull; Professional Content Management System</p>
        </div>
    </div>
</body>
</html>
