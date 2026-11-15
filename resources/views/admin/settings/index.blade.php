@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            @method('PUT')
            
            <h2 class="text-xl font-bold text-gray-800 mb-4">Site Information</h2>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="site_name">
                    Site Name *
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('site_name') border-red-500 @enderror" 
                       id="site_name" 
                       type="text" 
                       name="site_name" 
                       value="{{ old('site_name', $settings->site_name ?? '') }}" 
                       required 
                       placeholder="Rosetta CMS">
                
                @error('site_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="site_description">
                    Site Description
                </label>
                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('site_description') border-red-500 @enderror" 
                          id="site_description" 
                          name="site_description" 
                          rows="3" 
                          placeholder="A modern, flexible content management system...">{{ old('site_description', $settings->site_description ?? '') }}</textarea>
                
                @error('site_description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="admin_email">
                    Admin Email *
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('admin_email') border-red-500 @enderror" 
                       id="admin_email" 
                       type="email" 
                       name="admin_email" 
                       value="{{ old('admin_email', $settings->admin_email ?? '') }}" 
                       required 
                       placeholder="admin@rosettacms.com">
                
                @error('admin_email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Display Settings</h2>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="posts_per_page">
                    Posts Per Page
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('posts_per_page') border-red-500 @enderror" 
                       id="posts_per_page" 
                       type="number" 
                       name="posts_per_page" 
                       value="{{ old('posts_per_page', $settings->posts_per_page ?? 10) }}" 
                       min="1" 
                       max="100">
                
                @error('posts_per_page')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="theme">
                    Theme
                </label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('theme') border-red-500 @enderror" 
                        id="theme" 
                        name="theme">
                    <option value="rosetta" {{ (old('theme', $settings->theme ?? 'rosetta') === 'rosetta') ? 'selected' : '' }}>Rosetta (Default)</option>
                </select>
                
                @error('theme')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition" type="submit">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
