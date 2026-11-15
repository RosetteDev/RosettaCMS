@extends('admin.layouts.app')

@section('title', 'Upload Media')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Upload Media</h1>
        <a href="{{ route('admin.media.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            Back to Library
        </a>
    </div>

    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="file">
                    Select File
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('file') border-red-500 @enderror" 
                       id="file" 
                       type="file" 
                       name="file" 
                       required 
                       accept="image/jpeg,image/png,image/gif,image/svg+xml,application/pdfvideo/mp4,video/mov,video/webm">
                
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                
                <p class="text-sm text-gray-500 mt-2">
                    Accepted formats: JPEG, PNG, GIF, SVG, PDF, MP4, MOV, WebM
                </p>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="alt_text">
                    Alt Text (Optional)
                </label>
                <input class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       id="alt_text" 
                       type="text" 
                       name="alt_text" 
                       placeholder="Describe this image for accessibility">
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="caption">
                    Caption (Optional)
                </label>
                <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                          id="caption" 
                          name="caption" 
                          rows="3" 
                          placeholder="Add a caption..."></textarea>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="site_id">
                    Site (Optional)
                </label>
                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        id="site_id" 
                        name="site_id">
                    <option value="">Select a site</option>
                    @foreach(\App\Models\Site::all() as $site)
                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center justify-between">
                <a href="{{ route('admin.media.index') }}" class="text-gray-600 hover:text-gray-800">
                    Cancel
                </a>
                <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition" type="submit">
                    Upload Media
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
