<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosetta CMS - Setup</title>
    
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
            --purple-100: #ede9fe;
            --purple-600: #9333ea;
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
        .max-w-2xl { max-width: 42rem; }
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
        .bg-gradient-to-r { background-image: linear-gradient(to right, var(--from), var(--to)); }
        .from-violet-700 { --from: var(--violet-700); }
        .to-indigo-700 { --to: var(--indigo-700); }
        .bg-clip-text { -webkit-background-clip: text; background-clip: text; }
        .text-transparent { color: transparent; }
        .text-gray-600 { color: var(--gray-600); }
        .bg-white { background: var(--white); }
        .rounded-3xl { border-radius: 1.5rem; }
        .border { border-width: 1px; }
        .border-gray-100 { border-color: var(--gray-100); }
        .p-8 { padding: 2rem; }
        .mb-8 { margin-bottom: 2rem; }
        .text-gray-800 { color: var(--gray-800); }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .w-10 { width: 2.5rem; }
        .h-10 { height: 2.5rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .text-blue-600 { color: var(--blue-600); }
        .mr-3 { margin-right: 0.75rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-blue-700 { color: var(--blue-700); }
        .mb-1 { margin-bottom: 0.25rem; }
        .font-medium { font-weight: 500; }
        .text-green-600 { color: var(--green-600); }
        .hover\\\\:underline:hover { text-decoration: underline; }
        .inline-block { display: inline-block; }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .text-red-500 { color: var(--red-500); }
        .mt-0\\\\.5 { margin-top: 0.125rem; }
        .flex { display: flex; }
        .mt-1 { margin-top: 0.25rem; }
        .text-red-700 { color: var(--red-700); }
        .p-4 { padding: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-green-500 { color: var(--green-500); }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }
        .mr-3 { margin-right: 0.75rem; }
        .text-green-800 { color: var(--green-800); }
        .mt-1 { margin-top: 0.25rem; }
        .pt-6 { padding-top: 1.5rem; }
        .border-t { border-top-width: 1px; }
        .border-gray-200 { border-color: var(--gray-200); }
        .grid { display: grid; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)); }
        .gap-5 { gap: 1.25rem; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .input-group { position: relative; }
        .input-group i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); }
        .input-field { transition: all 0.2s; }
        .input-field:focus { outline: none; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); border-color: var(--violet-500); background: var(--white); }
        .w-full { width: 100%; }
        .pl-10 { padding-left: 2.5rem; }
        .pr-4 { padding-right: 1rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .border-gray-300 { border-color: var(--gray-300); }
        .rounded-xl { border-radius: 0.75rem; }
        .bg-gray-50 { background: var(--gray-50); }
        .transition-colors { transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; }
        .cursor-pointer { cursor: pointer; }
        .bg-blue-100 { background: var(--blue-100); }
        .bg-purple-100 { background: var(--purple-100); }
        .bg-teal-100 { background: var(--teal-100); }
        .text-blue-600 { color: var(--blue-600); }
        .text-purple-600 { color: var(--purple-600); }
        .text-teal-600 { color: var(--teal-600); }
        .flex-shrink-0 { flex-shrink: 0; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .font-bold { font-weight: 700; }
        .w-8 { width: 2rem; }
        .h-8 { height: 2rem; }
        .rounded-full { border-radius: 9999px; }
        .bg-violet-600 { background: var(--violet-600); }
        .text-white { color: var(--white); }
        .space-x-4 > * + * { margin-left: 1rem; }
        .w-px { width: 1px; }
        .bg-gray-300 { background: var(--gray-300); }
        .text-gray-700 { color: var(--gray-700); }
        .text-blue-500 { color: var(--blue-500); }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .mt-0\\\\.5 { margin-top: 0.125rem; }
        .flex-shrink-0 { flex-shrink: 0; }
        .bg-blue-50 { background: var(--blue-50); }
        .border-l-4 { border-left-width: 4px; }
        .border-blue-500 { border-color: var(--blue-500); }
        .rounded-r-lg { border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
        .items-start { align-items: flex-start; }
        .mt-0\\\\.5 { margin-top: 0.125rem; }
        .mr-3 { margin-right: 0.75rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .font-medium { font-weight: 500; }
        .text-blue-700 { color: var(--blue-700); }
        .list-disc { list-style-type: disc; }
        .list-inside { list-style-position: inside; }
        .space-y-1 > * + * { margin-top: 0.25rem; }
        .mt-3 { margin-top: 0.75rem; }
        .text-gray-500 { color: var(--gray-500); }
        .font-medium { font-weight: 500; }
        .mb-2 { margin-bottom: 0.5rem; }
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
        .hover\\\\:underline:hover { text-decoration: underline; }
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
        .hover\\\\:text-violet-600:hover { color: var(--violet-600); }
        .font-medium { font-weight: 500; }
        .transition-colors { transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out; }
        .card { transition: transform 0.3s; }
        .card:hover { transform: translateY(-2px); }
        .text-gray-600 { color: var(--gray-600); }
        .hover\\\\:text-violet-600:hover { color: var(--violet-600); }
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
            .max-w-2xl { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-2xl" style="background: linear-gradient(to bottom right, var(--violet-600), var(--indigo-600)); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <i class="fas fa-pen-nib text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold bg-clip-text text-transparent" style="background: linear-gradient(to right, var(--violet-700), var(--indigo-700)); -webkit-background-clip: text; background-clip: text;">
                Welcome to Rosetta CMS
            </h1>
            <p class="text-gray-600">Complete the setup to get started</p>
        </div>

        <div class="card bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
            <!-- Progress (visual only) -->
            <div class="bg-gray-50 px-8 py-4 border-b border-gray-100">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-database"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Database</span>
                    </div>
                    <div class="w-px h-8 bg-gray-300"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Site</span>
                    </div>
                    <div class="w-px h-8 bg-gray-300"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Admin</span>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 m-6 flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-green-800 font-medium">{{ session('success') }}</p>
                        <a href="{{ route('login') }}" class="text-green-600 text-sm font-medium hover:underline mt-1 inline-block">
                            Go to Login <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 m-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <p class="text-red-800 font-medium">There was an issue</p>
                            <p class="text-red-700 text-sm mt-1">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('install.process') }}" class="p-8">
                @csrf

                <!-- Database Configuration -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                            <i class="fas fa-database text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Database Configuration</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-5 mb-6">
                        <!-- Database Driver -->
                        <div class="input-group">
                            <i class="fas fa-server"></i>
                            <select name="db_connection" id="db_connection" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors cursor-pointer">
                                <option value="sqlite" {{ old('db_connection', 'sqlite') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                                <option value="mysql" {{ old('db_connection') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                                <option value="pgsql" {{ old('db_connection') == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                                <option value="sqlsrv" {{ old('db_connection') == 'sqlsrv' ? 'selected' : '' }}>SQL Server</option>
                            </select>
                        </div>

                        <div id="db-non-sqlite-fields">
                            <div class="grid grid-cols-2 gap-5">
                                <!-- Host -->
                                <div class="input-group">
                                    <i class="fas fa-server"></i>
                                    <input type="text" name="db_host" id="db_host" value="{{ old('db_host', '127.0.0.1') }}" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="127.0.0.1">
                                </div>
                                <!-- Port -->
                                <div class="input-group">
                                    <i class="fas fa-port-hole"></i>
                                    <input type="number" name="db_port" id="db_port" value="{{ old('db_port', '3306') }}" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="3306">
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="input-group" style="margin-top: 1.25rem;">
                                <i class="fas fa-user"></i>
                                <input type="text" name="db_username" id="db_username" value="{{ old('db_username', config('database.connections.mysql.username', '')) }}" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="username">
                            </div>

                            <!-- Password -->
                            <div class="input-group" style="margin-top: 1.25rem;">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="db_password" id="db_password" value="{{ old('db_password', config('database.connections.mysql.password', '')) }}" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="Password (optional)">
                            </div>
                        </div>

                        <!-- Database Name / SQLite path (single field; placeholder follows the selected driver) -->
                        <div class="input-group">
                            <i class="fas fa-database"></i>
                            <input type="text" name="db_database" id="db_database" value="{{ old('db_database') }}" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="database/database.sqlite">
                        </div>
                        <p class="text-xs text-gray-500" id="db_database_hint">SQLite: path to the database file (created automatically).</p>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3 flex-shrink-0"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">Database Creation</p>
                                <p>SQLite needs no server — the database file is created automatically. For MySQL, Rosetta CMS can create the database if your user has CREATE DATABASE permission.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Site Configuration -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                            <i class="fas fa-cog text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Site Configuration</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <!-- Site URL -->
                        <div class="input-group">
                            <i class="fas fa-globe"></i>
                            <input type="url" name="app_url" id="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="https://example.com">
                        </div>

                        <!-- Site Name -->
                        <div class="input-group">
                            <i class="fas fa-tag"></i>
                            <input type="text" name="site_name" id="site_name" value="{{ old('site_name', 'Rosetta CMS') }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="Your Site Name">
                        </div>
                    </div>
                </div>

                <!-- Admin Account -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center mr-3">
                            <i class="fas fa-user-shield text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Admin Account</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <!-- Admin Email -->
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="admin@example.com">
                        </div>

                        <!-- Admin Password -->
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="admin_password" id="admin_password" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="Create a strong password">
                        </div>

                        <!-- Confirm Password -->
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:bg-white transition-colors" placeholder="Confirm your password">
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="mt-3 text-sm text-gray-500">
                        <p class="font-medium mb-2">Password must contain:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One number</li>
                        </ul>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit" class="btn-primary text-white text-lg font-semibold py-4 px-8 rounded-xl w-full shadow-lg flex items-center justify-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        Complete Setup
                    </button>
                </div>
            </form>

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
            <a href="{{ route('install.check') }}" class="inline-flex items-center text-gray-600 hover:text-violet-600 font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Check
            </a>
        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-8 text-gray-400 text-sm">
            <p>Rosetta CMS &copy; {{ date('Y') }} &bull; Professional Content Management System</p>
        </div>
    </div>
    <script>
        (function () {
            var select = document.getElementById('db_connection');
            var nonSqlite = document.getElementById('db-non-sqlite-fields');
            var dbName = document.getElementById('db_database');
            var hint = document.getElementById('db_database_hint');

            function toggle() {
                var isSqlite = select.value === 'sqlite';
                nonSqlite.style.display = isSqlite ? 'none' : '';
                // Disabled inputs are not submitted — keep the non-sqlite fields out of the form when sqlite is chosen.
                var fields = nonSqlite.querySelectorAll('input, select, textarea');
                fields.forEach(function (field) { field.disabled = isSqlite; });
                if (dbName) {
                    dbName.placeholder = isSqlite ? 'database/database.sqlite' : 'database_name';
                    dbName.required = !isSqlite;
                }
                if (hint) {
                    hint.textContent = isSqlite
                        ? 'SQLite: path to the database file (created automatically).'
                        : 'Database name. The database is created automatically if the user has CREATE DATABASE permission.';
                }
            }

            select.addEventListener('change', toggle);
            toggle();
        })();
    </script>
</body>
</html>