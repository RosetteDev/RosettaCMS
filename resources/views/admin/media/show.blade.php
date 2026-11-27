@extends('admin.layouts.app')

@section('title', 'Media Detail')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Media Detail</h1>
            <p class="text-gray-600 mt-1">File: {{ $media->file_name }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.media.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                Back to Library
            </a>
            <a href="{{ $media->url }}" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                View File
            </a>
            <button onclick="document.getElementById('replace-file-input').click()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                Replace file
            </button>
            <button onclick="deleteMedia()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                Delete
            </button>
            <input type="file" id="replace-file-input" class="hidden" accept="image/*,video/*,application/pdf,audio/*" onchange="replaceFile(this.files[0])">
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-8">
            <div class="flex flex-col md:flex-row gap-8">
                <!-- Media Preview -->
                <div class="w-full md:w-1/2">
                    @php
                        $isImage = Str::startsWith($media->mime_type, 'image/');
                        $isVideo = Str::startsWith($media->mime_type, 'video/');
                        $isAudio = Str::startsWith($media->mime_type, 'audio/');
                        $isPdf = $media->mime_type === 'application/pdf';
                    @endphp
                    
                    @if($isImage)
                        <div class="bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: 'Media' }}" class="w-full h-auto max-h-[500px] object-contain">
                        </div>
                    @elseif($isVideo)
                        <div class="bg-gray-100 rounded-lg overflow-hidden">
                            <video src="{{ $media->url }}" controls class="w-full h-auto max-h-[500px]">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    @elseif($isAudio)
                        <div class="bg-gray-100 rounded-lg overflow-hidden p-8 flex flex-col items-center gap-4">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 6L3 9v13l6-3m0-13v13"/>
                            </svg>
                            <audio src="{{ $media->url }}" controls preload="none" class="w-full max-w-md">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                    @elseif($isPdf)
                        <div class="bg-white rounded-lg p-8 flex items-center justify-center min-h-[400px]">
                            <div class="text-center">
                                <svg class="w-24 h-24 mx-auto text-red-500 mb-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                    <path d="M14 2v6h6"/>
                                </svg>
                                <p class="text-gray-600 font-medium">PDF Document</p>
                                <p class="text-gray-500 text-sm mt-1">{{ number_format($media->file_size / 1024, 2) }} KB</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-white rounded-lg p-8 flex items-center justify-center min-h-[400px]">
                            <div class="text-center">
                                <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                    <path d="M14 2v6h6"/>
                                    <path d="M9 13h6v2H9z"/>
                                    <path d="M9 17h6v2H9z"/>
                                </svg>
                                <p class="text-gray-600 font-medium">File</p>
                                <p class="text-gray-500 text-sm mt-1">{{ $media->mime_type }}</p>
                                <p class="text-gray-500 text-sm">{{ number_format($media->file_size / 1024, 2) }} KB</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($media->folder)
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-sm text-gray-500">Folder:</span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                {{ $media->folder }}
                            </span>
                        </div>
                    @endif
                </div>
                
                <!-- Media Details -->
                <div class="w-full md:w-1/2 space-y-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Details</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">File Name</label>
                                <p class="mt-1 text-gray-800 font-mono text-sm">{{ $media->file_name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">File Path</label>
                                <p class="mt-1 text-gray-800 font-mono text-sm">{{ $media->file_path }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">MIME Type</label>
                                <p class="mt-1 text-gray-800 font-mono text-sm">{{ $media->mime_type }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">File Size</label>
                                <p class="mt-1 text-gray-800">
                                    {{ number_format($media->file_size / 1024, 2) }} KB ({{ $media->file_size }} bytes)
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Uploaded By</label>
                                <p class="mt-1 text-gray-800">{{ $media->user->name ?? 'Unknown' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Site</label>
                                <p class="mt-1 text-gray-800">{{ $media->site->name ?? 'Unknown' }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Date Uploaded</label>
                                <p class="mt-1 text-gray-800">{{ \Carbon\Carbon::parse($media->created_at)->format('M d, Y H:i') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Last Updated</label>
                                <p class="mt-1 text-gray-800">{{ \Carbon\Carbon::parse($media->updated_at)->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Accessibility & Metadata</h2>

                        <form id="metadata-form" class="space-y-4">
                            <div>
                                <label for="alt-text-input" class="block text-sm font-medium text-gray-600">Alt Text</label>
                                <div class="flex gap-2 mt-1">
                                    <input type="text" id="alt-text-input" maxlength="255"
                                           value="{{ $media->alt_text }}"
                                           class="flex-1 border border-gray-300 rounded px-3 py-2 text-gray-800"
                                           placeholder="Describe the image…">
                                    @if(Str::startsWith($media->mime_type, 'image/'))
                                        <button type="button" onclick="generateAlt()" class="bg-gray-100 text-gray-700 px-3 py-2 rounded border border-gray-300 text-sm hover:bg-gray-200">
                                            Generate
                                        </button>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Required for images. Alt text describes the image for screen readers and SEO.</p>
                            </div>

                            <div>
                                <label for="caption-input" class="block text-sm font-medium text-gray-600">Caption</label>
                                <textarea id="caption-input" rows="2"
                                          class="mt-1 w-full border border-gray-300 rounded px-3 py-2 text-gray-800"
                                          placeholder="Optional caption…">{{ $media->caption }}</textarea>
                            </div>

                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                Save metadata
                            </button>
                            <p id="metadata-status" class="text-sm text-gray-600"></p>
                        </form>

                        @if($media->meta)
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-600">Meta Data</label>
                                <div class="mt-1 bg-gray-50 p-3 rounded text-sm font-mono">
                                    <pre class="whitespace-pre-wrap">{{ json_encode($media->meta, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function replaceFile(file) {
    if (!file) return;
    if (!confirm('Replace the file for "{{ $media->file_name }}"? The old file will be overwritten.')) return;

    const formData = new FormData();
    formData.append('file', file);

    fetch("{{ route('admin.media.replace', $media) }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error replacing file');
    });
}

function generateAlt() {
    fetch("{{ route('admin.media.generateAlt', $media) }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.alt_text) {
            document.getElementById('alt-text-input').value = data.alt_text;
        } else {
            alert('Error: ' + (data.message || 'Could not generate alt text'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating alt text');
    });
}

document.getElementById('metadata-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('alt_text', document.getElementById('alt-text-input').value);
    formData.append('caption', document.getElementById('caption-input').value);

    const status = document.getElementById('metadata-status');
    status.textContent = 'Saving…';

    fetch("{{ route('admin.media.updateMetadata', $media) }}", {
        method: 'PUT',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            status.textContent = 'Saved.';
            status.className = 'text-sm text-green-600';
        } else {
            status.textContent = 'Error: ' + (data.message || 'Unknown error');
            status.className = 'text-sm text-red-600';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        status.textContent = 'Error saving metadata';
        status.className = 'text-sm text-red-600';
    });
});

function deleteMedia() {
    if (confirm('Are you sure you want to delete this media? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch("{{ route('admin.media.destroy', $media) }}", {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = "{{ route('admin.media.index') }}";
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting media');
        });
    }
}
</script>
@endsection
