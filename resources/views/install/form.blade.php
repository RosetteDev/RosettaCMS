<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosetta CMS - Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .section-divider {
            position: relative;
            height: 1px;
            background: linear-gradient(90deg, transparent, theme('colors.gray.200'), transparent);
            margin: 2rem 0;
        }
        .section-divider::before {
            content: attr(data-text);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 1rem;
            color: theme('colors.gray.500');
            font-size: 0.875rem;
            font-weight: 500;
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
    <div class="w-full max-w-2xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 shadow-2xl">
                <i class="fas fa-pen-nib text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-violet-700 to-indigo-700 bg-clip-text text-transparent mb-2">
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
                    <div class="w-8 h-px bg-gray-300"></div>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Site</span>
                    </div>
                    <div class="w-8 h-px bg-gray-300"></div>
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
                            <select name="db_connection" id="db_connection" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors cursor-pointer">
                                <option value="mysql" {{ old('db_connection', 'mysql') == 'mysql' ? 'selected' : '' }}>MySQL</option>
                                <option value="pgsql" {{ old('db_connection') == 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
                                <option value="sqlite" {{ old('db_connection') == 'sqlite' ? 'selected' : '' }}>SQLite</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <!-- Host -->
                            <div class="input-group">
                                <i class="fas fa-server"></i>
                                <input type="text" name="db_host" id="db_host" value="{{ old('db_host', '127.0.0.1') }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="127.0.0.1">
                            </div>
                            <!-- Port -->
                            <div class="input-group">
                                <i class="fas fa-port-hole"></i>
                                <input type="number" name="db_port" id="db_port" value="{{ old('db_port', '3306') }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="3306">
                            </div>
                        </div>

                        <!-- Database Name -->
                        <div class="input-group">
                            <i class="fas fa-database"></i>
                            <input type="text" name="db_database" id="db_database" value="{{ old('db_database', config('database.connections.mysql.database', '')) }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="database_name">
                        </div>

                        <!-- Username -->
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="db_username" id="db_username" value="{{ old('db_username', config('database.connections.mysql.username', '')) }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="username">
                        </div>

                        <!-- Password -->
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="db_password" id="db_password" value="{{ old('db_password', config('database.connections.mysql.password', '')) }}" class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="Password (optional)">
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3 flex-shrink-0"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-medium mb-1">Database Creation</p>
                                <p>If your MySQL user has CREATE DATABASE permission, Rosetta CMS can create the database automatically.</p>
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
                            <input type="url" name="app_url" id="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="https://example.com">
                        </div>

                        <!-- Site Name -->
                        <div class="input-group">
                            <i class="fas fa-tag"></i>
                            <input type="text" name="site_name" id="site_name" value="{{ old('site_name', 'Rosetta CMS') }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="Your Site Name">
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
                            <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="admin@example.com">
                        </div>

                        <!-- Admin Password -->
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="admin_password" id="admin_password" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="Create a strong password">
                        </div>

                        <!-- Confirm Password -->
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required class="input-field w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50 focus:bg-white transition-colors" placeholder="Confirm your password">
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
</body>
</html>
