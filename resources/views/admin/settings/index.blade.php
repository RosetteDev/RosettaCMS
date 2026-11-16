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
            
            <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Comments Settings</h2>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="disable_comments" value="0">
                    <input type="checkbox" name="disable_comments" value="1" {{ old('disable_comments', $settings->disable_comments ?? 0) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Disable comments on all posts</span>
                </label>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="enable_comment_approval" value="0">
                    <input type="checkbox" name="enable_comment_approval" value="1" {{ old('enable_comment_approval', $settings->enable_comment_approval ?? 1) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Require manual approval for guest comments</span>
                </label>
                <p class="text-sm text-gray-600 mt-1">Comments from registered users will auto-publish.</p>
            </div>
            
            <h2 class="text-xl font-bold text-gray-800 mb-4 mt-6">Spam Protection</h2>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="enable_akismet" value="0">
                    <input type="checkbox" name="enable_akismet" value="1" {{ old('enable_akismet', $settings->enable_akismet ?? 0) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Enable Akismet spam filtering</span>
                </label>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="akismet_api_key">
                    Akismet API Key
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('akismet_api_key') border-red-500 @enderror" 
                       id="akismet_api_key" 
                       type="text" 
                       name="akismet_api_key" 
                       value="{{ old('akismet_api_key', $settings->akismet_api_key ?? '') }}" 
                       placeholder="Enter your Akismet API key">
                <p class="text-sm text-gray-600 mt-1">Get your API key from <a href="https://akismet.com" target="_blank" class="text-blue-600 hover:underline">akismet.com</a></p>
                
                @error('akismet_api_key')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="spam_keywords">
                    Blocked Keywords (comma-separated)
                </label>
                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('spam_keywords') border-red-500 @enderror" 
                          id="spam_keywords" 
                          name="spam_keywords" 
                          rows="3" 
                          placeholder="viagra, cialis, lotto">{{ old('spam_keywords', $settings->spam_keywords ?? '') }}</textarea>
                <p class="text-sm text-gray-600 mt-1">Comments containing these keywords will be marked as spam.</p>
                
                @error('spam_keywords')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="rate_limit_enabled" value="0">
                    <input type="checkbox" name="rate_limit_enabled" value="1" {{ old('rate_limit_enabled', $settings->rate_limit_enabled ?? 1) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Enable comment rate limiting</span>
                </label>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="rate_limit_per_minute">
                    Comments per minute limit
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rate_limit_per_minute') border-red-500 @enderror" 
                       id="rate_limit_per_minute" 
                       type="number" 
                       name="rate_limit_per_minute" 
                       value="{{ old('rate_limit_per_minute', $settings->rate_limit_per_minute ?? 3) }}" 
                       min="1" 
                       max="100">
                <p class="text-sm text-gray-600 mt-1">Maximum comments a user can post per minute.</p>
                
                @error('rate_limit_per_minute')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="captcha_enabled" value="0">
                    <input type="checkbox" name="captcha_enabled" value="1" {{ old('captcha_enabled', $settings->captcha_enabled ?? 0) ? 'checked' : '' }} class="mr-2">
                    <span class="text-gray-700">Enable CAPTCHA for guest comments</span>
                </label>
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
