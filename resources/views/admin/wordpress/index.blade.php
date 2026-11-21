@extends('layouts.admin')

@section('title', 'WordPress Compatibility')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">WordPress Compatibility</h1>
            <p class="text-gray-600 mt-2">Import/export WordPress content and migrate from existing WordPress sites.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Export Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Export Content</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Post Types</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="post_types[]" value="post" checked class="rounded text-indigo-600">
                                <span class="ml-2">Posts</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="post_types[]" value="page" checked class="rounded text-indigo-600">
                                <span class="ml-2">Pages</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="post_types[]" value="attachment" class="rounded text-indigo-600">
                                <span class="ml-2">Attachments</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Export Options</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="include_comments" class="rounded text-indigo-600">
                                <span class="ml-2">Include Comments</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="include_metadata" class="rounded text-indigo-600">
                                <span class="ml-2">Include Metadata</span>
                            </label>
                        </div>
                    </div>

                    <button onclick="exportContent()" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition">
                        Export WordPress Content
                    </button>
                    <button onclick="exportAll()" class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 transition">
                        Export All Content
                    </button>
                </div>

                @if($exportedFiles)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-medium mb-2">Previous Exports</h3>
                    <div class="space-y-2">
                        @foreach($exportedFiles as $file)
                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                            <span class="text-sm">{{ $file['name'] }}</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs text-gray-500">{{ round($file['size'] / 1024, 2) }} KB</span>
                                <button onclick="deleteExport('{{ $file['name'] }}')" class="text-red-600 hover:text-red-800">
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Import Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Import Content</h2>
                
                <form action="{{ route('admin.wordpress.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">WXR File</label>
                            <input type="file" name="wxr_file" accept=".xml" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-2 text-xs text-gray-500">Maximum file size: 10MB</p>
                        </div>

                        <div class="border rounded p-4 bg-gray-50">
                            <p class="text-sm text-gray-600">
                                <strong>Import Notes:</strong>
                            </p>
                            <ul class="text-sm text-gray-600 mt-2 list-disc list-inside">
                                <li>Authors will be created if they don't exist</li>
                                <li>Categories and tags will be imported</li>
                                <li>Existing posts with the same ID will be updated</li>
                                <li>Comments will be imported if included</li>
                            </ul>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition">
                            Import WordPress Content
                        </button>
                    </div>
                </form>

                @if($importedFiles)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-medium mb-2">Uploaded Files</h3>
                    <div class="space-y-2">
                        @foreach($importedFiles as $file)
                        <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                            <span class="text-sm">{{ $file['name'] }}</span>
                            <span class="text-xs text-gray-500">{{ round($file['size'] / 1024, 2) }} KB</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Migration Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Migrate from WordPress</h2>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Connect to an existing WordPress database to migrate content.</p>
                    
                    <div class="bg-gray-50 p-3 rounded mb-3">
                        <p class="text-sm">
                            <strong>Status:</strong> 
                            @if($canConnect)
                                <span class="text-green-600">Connected</span>
                            @else
                                <span class="text-red-600">Not Connected</span>
                            @endif
                        </p>
                        @if($dbStats)
                        <p class="text-sm mt-1">
                            <strong>Database Stats:</strong><br>
                            Users: {{ $dbStats['users'] ?? 0 }}<br>
                            Posts: {{ $dbStats['posts'] ?? 0 }}<br>
                            Terms: {{ $dbStats['terms'] ?? 0 }}<br>
                            Comments: {{ $dbStats['comments'] ?? 0 }}
                        </p>
                        @endif
                    </div>

                    <button onclick="migrateData()" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition {{ !$canConnect ? 'opacity-50 cursor-not-allowed' : '' }}">
                        Migrate WordPress Data
                    </button>
                </div>

                <div class="border-t pt-4">
                    <h3 class="font-medium mb-3">Migration Options</h3>
                    
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="steps[]" value="users" checked class="rounded text-indigo-600">
                            <span class="ml-2">Users</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="steps[]" value="terms" checked class="rounded text-indigo-600">
                            <span class="ml-2">Categories & Tags</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="steps[]" value="posts" checked class="rounded text-indigo-600">
                            <span class="ml-2">Posts & Pages</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="steps[]" value="comments" checked class="rounded text-indigo-600">
                            <span class="ml-2">Comments</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportContent() {
    const postTypes = Array.from(document.querySelectorAll('input[name="post_types[]"]:checked'))
        .map(cb => cb.value);
    
    const params = new URLSearchParams();
    postTypes.forEach(type => params.append('post_types[]', type));
    
    window.location.href = '{{ route("admin.wordpress.export") }}?' + params.toString();
}

function exportAll() {
    window.location.href = '{{ route("admin.wordpress.export-all") }}';
}

function deleteExport(filename) {
    if (confirm('Are you sure you want to delete this export?')) {
        fetch('{{ route("admin.wordpress.export.delete") }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ filename: filename })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function migrateData() {
    const steps = Array.from(document.querySelectorAll('input[name="steps[]"]:checked'))
        .map(cb => cb.value);
    
    if (steps.length === 0) {
        alert('Please select at least one migration step.');
        return;
    }

    fetch('{{ route("admin.wordpress.migrate.run") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ steps: steps })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Migration completed successfully!');
            console.log(data.results);
        }
    })
    .catch(error => {
        alert('Migration failed: ' + error.message);
    });
}
</script>
@endsection
