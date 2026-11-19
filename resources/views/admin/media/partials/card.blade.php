@php
    $isImage = Str::startsWith($media->mime_type, 'image/');
    $isVideo = Str::startsWith($media->mime_type, 'video/');
    $isPdf = $media->mime_type === 'application/pdf';
    $fileUrl = $media->url;
@endphp

<div class="media-card" id="card-{{ $media->id }}" data-id="{{ $media->id }}">
    <input type="checkbox" class="select-checkbox" id="select-{{ $media->id }}" onchange="toggleSelect({{ $media->id }}, this)">
    <label for="select-{{ $media->id }}" class="select-checkbox-label" title="Select"></label>
    
    <div class="media-preview">
        @if($isImage)
            <img src="{{ $fileUrl }}" alt="{{ $media->alt_text ?: 'Media' }}" loading="lazy">
        @elseif($isVideo)
            <video src="{{ $fileUrl }}" controls class="w-full h-full object-cover">
                Your browser does not support the video tag.
            </video>
        @elseif($isPdf)
            <div class="file-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                    <path d="M14 2v6h6"/>
                </svg>
            </div>
        @else
            <div class="file-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                    <path d="M14 2v6h6"/>
                    <path d="M9 13h6v2H9z"/>
                    <path d="M9 17h6v2H9z"/>
                </svg>
            </div>
        @endif
    </div>
    
    <div class="media-content">
        <h3 class="media-name" title="{{ $media->file_name }}">
            <a href="{{ route('admin.media.show', $media) }}">
                {{ $media->file_name }}
            </a>
        </h3>
        
        @if($media->folder)
            <div class="folder-tag">
                <a href="{{ route('admin.media.index', ['folder' => $media->folder]) }}" class="folder-link" data-folder="{{ $media->folder }}">
                    {{ $media->folder }}
                </a>
            </div>
        @endif
        
        <div class="media-info">
            <span>{{ \Carbon\Carbon::parse($media->created_at)->format('M d, Y') }}</span>
            <span>•</span>
            <span>{{ number_format($media->file_size / 1024, 2) }} KB</span>
            <span>•</span>
            <span>{{ Str::limit($media->mime_type, 30) }}</span>
        </div>
        
        <div class="flex items-center gap-2 mt-2">
            <span class="text-xs text-gray-500 font-medium">Alt:</span>
            <span class="text-xs text-gray-600 flex-1 truncate" title="{{ $media->alt_text ?: 'No alt text' }}">
                {{ $media->alt_text ?: 'No alt text' }}
            </span>
        </div>
        
        @if($media->caption)
            <div class="mt-2">
                <p class="text-xs text-gray-500 font-medium mb-1">Caption:</p>
                <p class="text-xs text-gray-600 line-clamp-2" title="{{ $media->caption }}">
                    {{ $media->caption }}
                </p>
            </div>
        @endif
        
        <div class="flex justify-end items-center gap-2 mt-4 pt-3 border-t border-gray-100">
            <a href="{{ route('admin.media.show', $media) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                View
            </a>
            <a href="{{ $media->url }}" target="_blank" class="text-gray-500 hover:text-blue-600 transition-colors" title="Download">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <form action="{{ route('admin.media.destroy', $media) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-gray-500 hover:text-red-600 transition-colors" onclick="return confirm('Are you sure you want to delete this item?')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
