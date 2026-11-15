@extends('admin.layouts.app')

@section('title', 'Edit Site')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Site</h1>
        <a href="{{ route('admin.sites.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            Back to Sites
        </a>
    </div>

    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <form action="{{ route('admin.sites.update', $site) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Site Name *
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                       id="name" 
                       type="text" 
                       name="name" 
                       value="{{ old('name', $site->name) }}" 
                       required 
                       placeholder="My Website">
                
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="domain">
                    Domain (Optional)
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('domain') border-red-500 @enderror" 
                       id="domain" 
                       type="text" 
                       name="domain" 
                       value="{{ old('domain', $site->domain) }}" 
                       placeholder="example.com">
                
                @error('domain')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                
                <p class="text-sm text-gray-500 mt-1">
                    Leave empty for the default site. Enter just the domain without http:// or https://
                </p>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description (Optional)
                </label>
                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                          id="description" 
                          name="description" 
                          rows="3">{{ old('description', $site->description) }}</textarea>
            </div>
            
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.sites.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition" type="submit">
                    Update Site
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
