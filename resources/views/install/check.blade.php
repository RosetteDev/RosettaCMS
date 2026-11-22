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
            <!-- Progress -->
            <div class="bg-gray-50 px-8 py-4 border-b border-gray-100">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Requirements</span>
                    </div>
                    <div class="w-px h-8 bg-gray-300"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-500">Configuration</span>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <!-- Summary -->
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Server Requirements</h2>
                    <p class="text-gray-600">Your server meets all the requirements for Rosetta CMS</p>
                </div>

                <!-- Requirements Grid -->
                <div class="grid grid-cols-1 gap-4 mb-8">
                    <!-- PHP Version -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-brain text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">PHP Version</p>
                            <p class="text-sm text-green-700 mt-1">Required: 8.1+ | Installed: {{ $phpVersion }}</p>
                        </div>
                    </div>

                    <!-- MySQLi Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-database text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">MySQLi Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for database connectivity</p>
                        </div>
                    </div>

                    <!-- OpenSSL Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-lock text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">OpenSSL Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for encryption</p>
                        </div>
                    </div>

                    <!-- MBString Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-font text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">MBString Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for multibyte string handling</p>
                        </div>
                    </div>

                    <!-- GD/GD2 Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-image text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">GD/GD2 Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for image processing</p>
                        </div>
                    </div>

                    <!-- Fileinfo Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-file-alt text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">Fileinfo Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for file type detection</p>
                        </div>
                    </div>

                    <!-- Tokenizer Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-code text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">Tokenizer Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for Blade templating</p>
                        </div>
                    </div>

                    <!-- XML Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-code-branch text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">XML Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for various operations</p>
                        </div>
                    </div>

                    <!-- PDO Extension -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-project-diagram text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">PDO Extension</p>
                            <p class="text-sm text-green-700 mt-1">Required for database operations</p>
                        </div>
                    </div>

                    <!-- Composer -->
                    <div class="flex items-center p-4 bg-green-100 rounded-xl" style="border-left: 4px solid var(--green-500);">
                        <i class="fas fa-box text-green-600 text-2xl mr-3"></i>
                        <div>
                            <p class="text-lg font-medium text-green-800">Composer</p>
                            <p class="text-sm text-green-700 mt-1">Required for dependency management</p>
                        </div>
                    </div>
                </div>

                <!-- Optional Extensions -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Optional Extensions</h3>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-gray-700 text-sm mb-2">These extensions are not required but recommended:</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span class="text-sm">Redis (for caching and sessions)</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span class="text-sm">Sodium (for enhanced encryption)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Status -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Directory Permissions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center p-3 rounded-xl bg-green-100" style="border-left: 4px solid var(--green-500);">
                            <i class="fas fa-folder text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-green-800">storage/</p>
                                <p class="text-sm text-green-700">Writable</p>
                            </div>
                        </div>
                        <div class="flex items-center p-3 rounded-xl bg-green-100" style="border-left: 4px solid var(--green-500);">
                            <i class="fas fa-folder text-green-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-green-800">bootstrap/cache/</p>
                                <p class="text-sm text-green-700">Writable</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('install.form') }}" class="btn-primary text-white text-lg font-semibold py-4 px-8 rounded-xl w-full sm:w-auto shadow-lg flex items-center justify-center">
                        <i class="fas fa-arrow-right mr-3"></i>
                        Continue Setup
                    </a>
                    <a href="{{ route('install.refresh') }}" class="text-gray-700 hover:text-violet-600 font-medium transition-colors flex items-center justify-center sm:justify-start">
                        <i class="fas fa-redo mr-2"></i>
                        Refresh Check
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-4 text-center border-t border-gray-100">
                <p class="text-gray-500 text-sm">
                    <i class="fas fa-question-circle mr-2"></i>
                    Need help? Check the
                    <a href="https://rosettacms.com/docs" class="text-violet-600 font-medium hover:underline ml-1" target="_blank">
                        documentation <i class="fas fa-external-link-alt text-xs"></i>
                    </a>
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