<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosetta CMS - System Check</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        .btn-primary {
            transition: all 0.3s;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.3);
        }
        .check-card {
            transition: transform 0.3s;
        }
        .check-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-violet-50 via-blue-50 to-teal-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 shadow-2xl">
                <i class="fas fa-check-circle text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-violet-700 to-indigo-700 bg-clip-text text-transparent mb-2">
                System Check
            </h1>
            <p class="text-gray-600">Verifying your server meets all requirements</p>
        </div>

        <!-- Error Message -->
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 flex items-center check-card">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <div>
                    <p class="text-red-800 font-medium">Installation Error</p>
                    <p class="text-red-700 text-sm mt-1">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <div class="check-card bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100">
            <!-- Progress (visual only) -->
            <div class="bg-gray-50 px-8 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-violet-600 text-white flex items-center justify-center text-sm font-bold">
                            <i class="fas fa-robot"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Requirements</span>
                    </div>
                    @if($checks['all_passed'])
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            All passed
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            Issues found
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-8">
                <!-- PHP Version -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mr-3">
                            <i class="fas fa-server text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">PHP Version <span class="text-red-500 text-sm">*</span></h2>
                    </div>

                    <div class="bg-white border-2 border-gray-200 rounded-xl p-4 flex items-center justify-between hover:border-violet-200 transition-colors {{ $checks['php_version']['passed'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $checks['php_version']['name'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $checks['php_version']['message'] }}</p>
                        </div>
                        <div class="text-center">
                            @if($checks['php_version']['passed'])
                                <i class="fas fa-check-circle text-2xl text-green-500"></i>
                            @else
                                <i class="fas fa-times-circle text-2xl text-red-500"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- PHP Extensions -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                            <i class="fas fa-plug text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">PHP Extensions <span class="text-red-500 text-sm">*</span></h2>
                    </div>

                    @foreach($checks['extensions']['extensions'] as $extension)
                        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between mb-3 hover:border-violet-200 transition-colors {{ $extension['passed'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $extension['name'] }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $extension['message'] }}</p>
                            </div>
                            <div class="text-center">
                                @if($extension['passed'])
                                    <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                @else
                                    <i class="fas fa-times-circle text-2xl text-red-500"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Directory Permissions -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center mr-3">
                            <i class="fas fa-folder text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Directory Permissions <span class="text-red-500 text-sm">*</span></h2>
                    </div>

                    @foreach($checks['directories']['directories'] as $dir)
                        <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between mb-3 hover:border-violet-200 transition-colors {{ $dir['passed'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $dir['name'] }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $dir['message'] }}</p>
                            </div>
                            <div class="text-center">
                                @if($dir['passed'])
                                    <i class="fas fa-check-circle text-2xl text-green-500"></i>
                                @else
                                    <i class="fas fa-times-circle text-2xl text-red-500"></i>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Dependencies -->
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                            <i class="fas fa-box text-lg"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Dependencies</h2>
                    </div>

                    <div class="bg-white border-2 border-gray-200 rounded-xl p-4 flex items-center justify-between hover:border-violet-200 transition-colors {{ $checks['dependencies']['passed'] ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50' }}">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $checks['dependencies']['name'] }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $checks['dependencies']['message'] }}</p>
                        </div>
                        <div class="text-center">
                            @if($checks['dependencies']['passed'])
                                <i class="fas fa-check-circle text-2xl text-green-500"></i>
                            @else
                                <i class="fas fa-times-circle text-2xl text-red-500"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="text-center mb-8">
                        @if($checks['all_passed'])
                            <div class="inline-flex items-center px-6 py-3 rounded-full bg-green-100 text-green-800 font-semibold mb-4">
                                <i class="fas fa-check-circle mr-2 text-lg"></i>
                                All system requirements met!
                            </div>
                            <p class="text-gray-600 mb-6">Your server is ready for Rosetta CMS installation.</p>
                            <a href="{{ route('install') }}" class="btn-primary text-white text-lg font-semibold py-4 px-8 rounded-xl shadow-lg inline-flex items-center">
                                <i class="fas fa-play-circle mr-2"></i>
                                Start Installation
                            </a>
                        @else
                            <div class="inline-flex items-center px-6 py-3 rounded-full bg-red-100 text-red-800 font-semibold mb-4">
                                <i class="fas fa-exclamation-circle mr-2 text-lg"></i>
                                Some requirements not met
                            </div>
                            <p class="text-gray-600 mb-6">Please fix the highlighted issues above before continuing.</p>
                            <button class="btn-primary text-white text-lg font-semibold py-4 px-8 rounded-xl shadow-lg inline-flex items-center cursor-not-allowed opacity-50" disabled>
                                <i class="fas fa-lock mr-2"></i>
                                Fix Issues First
                            </button>
                        @endif
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-500">
                            <span class="text-red-500 font-bold">*</span> Required - Installation cannot proceed without meeting these requirements
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Branding -->
        <div class="text-center mt-8 text-gray-400 text-sm">
            <p>Rosetta CMS &copy; {{ date('Y') }} &bull; Professional Content Management System</p>
        </div>
    </div>
</body>
</html>
