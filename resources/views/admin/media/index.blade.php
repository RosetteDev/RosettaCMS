@extends('admin.layouts.app')

@section('title', 'Media Library')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Media Library</h1>
        <a href="{{ route('admin.media.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
            Upload New Media
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($media as $item)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                @if(in_array($item->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml']))
                    <div class="h-48 overflow-hidden">
                        <img src="{{ Storage::url($item->file_path) }}" alt="{{ $item->alt_text ?: 'Media' }}" class="w-full h-full object-cover">
                    </div>
                @elseif(in_array($item->mime_type, ['application/pdf']))
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <svg class="w-16 h-16 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                            <path d="M14 2v6h6"/>
                        </svg>
                    </div>
                @else
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                            <path d="M14 2v6h6"/>
                            <path d="M9 13h6v2H9z"/>
                            <path d="M9 17h6v2H9z"/>
                        </svg>
                    </div>
                @endif
                
                <div class="p-4">
                    <h3 class="font-semibold text-gray-800 truncate" title="{{ $item->file_name }}">
                        {{ $item->file_name }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}
                    </p>
                    
                    @if($item->alt_text)
                        <p class="text-xs text-gray-500 mt-1">
                            Alt: {{ $item->alt_text }}
                        </p>
                    @endif
                    
                    <p class="text-xs text-gray-500 mt-1">
                        {{ number_format($item->file_size / 1024, 2) }} KB
                    </p>
                    
                    <div class="flex justify-between items-center mt-3">
                        <a href="{{ route('admin.media.show', $item) }}" class="text-blue-600 hover:underline text-sm">
                            View
                        </a>
                        <form action="{{ route('admin.media.destroy', $item) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-500">No media found. Upload some files!</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $media->links() }}
    </div>
</div>
@endsection
