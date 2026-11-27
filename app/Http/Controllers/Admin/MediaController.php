<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ImageOptimizer;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    use ImageOptimizer;

    /**
     * Display a listing of media.
     */
    public function index(Request $request)
    {
        $query = Media::with('user', 'site');

        // Filter by folder
        if ($request->has('folder') && $request->folder) {
            $query->where('folder', $request->folder);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('file_name', 'like', '%' . $request->search . '%')
                  ->orWhere('alt_text', 'like', '%' . $request->search . '%')
                  ->orWhere('caption', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by mime type (image, video, document)
        if ($request->has('type') && $request->type) {
            switch ($request->type) {
                case 'image':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'video':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
                case 'document':
                    $query->where(function($q) {
                        $q->where('mime_type', 'like', 'application/pdf')
                          ->orWhere('mime_type', 'like', 'text/%');
                    });
                    break;
            }
        }

        $media = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Get unique folders for filter dropdown
        $folders = Media::whereNotNull('folder')
            ->distinct()
            ->pluck('folder')
            ->sort()
            ->values()
            ->toArray();
        
        $folders[] = 'Uploads';
        
        $settings = SiteSetting::forSite();
        
        return view('admin.media.index', compact('media', 'folders', 'settings'));
    }

    /**
     * JSON list of images for the editor's media picker.
     */
    public function picker(Request $request)
    {
        $query = Media::query()->where('mime_type', 'like', 'image/%');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('file_name', 'like', "%{$search}%")
                    ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        $media = $query->orderByDesc('created_at')->limit(120)->get();

        return response()->json($media->map(fn (Media $item) => [
            'id' => $item->id,
            'name' => $item->file_name,
            'path' => $item->file_path,
            'url' => $item->url,
            'alt' => $item->alt_text,
        ])->values());
    }

    /**
     * Show the form for creating a new media.
     */
    public function create()
    {
        return view('admin.media.create');
    }

    /**
     * Store a newly created media.
     */
    public function store(Request $request)
    {
        $settings = SiteSetting::forSite();
        $maxUploadMb = (int) ($settings->max_upload_size ?? 10) ?: 10;

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . ($maxUploadMb * 1024),
                'mimes:jpeg,jpg,png,gif,pdf,mp4,mov,webm,webp,mp3,wav,ogg',
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption' => 'nullable|string',
            'folder' => 'nullable|string|max:255|regex:/^([A-Za-z0-9_\-]+\/)*[A-Za-z0-9_\-]*$/',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        // Images require alt text for accessibility and SEO.
        if ($request->hasFile('file')) {
            $mime = $request->file('file')->getMimeType();
            if (str_starts_with($mime, 'image/') && blank($validated['alt_text'] ?? null)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Alt text is required for images.'], 422);
                }

                return back()->withErrors(['alt_text' => 'Alt text is required for images.'])->withInput();
            }
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Validate MIME type
            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'video/mp4',
                'video/quicktime',
                'video/webm',
                'audio/mpeg',
                'audio/wav',
                'audio/ogg',
            ];
            
            $detectedMimeType = $file->getMimeType();
            
            if (!in_array($detectedMimeType, $allowedMimeTypes)) {
                return back()->withErrors(['file' => 'Invalid file type. Only images, PDFs, and videos are allowed.']);
            }
            
            // Validate file extension
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4', 'mov', 'webm', 'webp', 'mp3', 'wav', 'ogg'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $allowedExtensions)) {
                return back()->withErrors(['file' => 'Invalid file extension.']);
            }
            
            // Generate unique filename
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $originalName = preg_replace('/[^A-Za-z0-9_\-]/', '-', $originalName) ?: 'file';
            $filename = time() . '_' . Str::lower(Str::random(6)) . '_' . $originalName . '.' . $extension;
            
            // Build folder path
            $folder = trim($validated['folder'] ?? '', '/');
            if ($folder) {
                $folder = str_replace('\\', '/', $folder);
                $filepath = $folder . '/' . $filename;
            } else {
                // Default to uploads folder
                $filepath = 'uploads/' . $filename;
            }
            
            $storagePath = $file->storeAs('media', $filepath, 'public');
            
            // Optimize image if applicable
            if (str_starts_with($detectedMimeType, 'image/')) {
                $fullPath = storage_path('app/public/' . $storagePath);
                
                // Convert to WebP
                $webpPath = $this->convertToWebP($fullPath);
                
                // Generate responsive images
                $this->generateResponsiveImages($fullPath);
                
                // Optimize original
                $this->optimizeImage($fullPath);
            }
            
            $media = Media::create([
                'user_id' => auth()->id(),
                'site_id' => $request->site_id ?? 1,
                'file_name' => $filename,
                'file_path' => $storagePath,
                'folder' => $folder ?: null,
                'mime_type' => $detectedMimeType,
                'file_size' => $file->getSize(),
                'alt_text' => $validated['alt_text'] ?? null,
                'caption' => $validated['caption'] ?? null,
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Media uploaded successfully.']);
            }

            return redirect()->route('admin.media.index')
                ->with('success', 'Media uploaded successfully.');
        }

        return back()->withErrors(['file' => 'No file uploaded.']);
    }

    /**
     * Display the specified media.
     */
    public function show(Media $media)
    {
        return view('admin.media.show', compact('media'));
    }

    /**
     * Remove the specified media.
     */
    public function destroy(Media $media)
    {
        // Delete file from storage
        \Storage::disk('public')->delete($media->file_path);
        
        $media->delete();
        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted successfully.');
    }

    /**
     * Handle bulk operations on media
     */
    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:media,id',
            'action' => 'required|in:delete,move_to_folder,rename,set_alt_text',
            'folder' => 'nullable|string|max:255|regex:/^([A-Za-z0-9_\-]+\/)*[A-Za-z0-9_\-]*$/',
            'name_base' => 'nullable|string|max:120|regex:/^[A-Za-z0-9_\-]+$/',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $mediaItems = Media::whereIn('id', $validated['ids'])->get();

        switch ($validated['action']) {
            case 'delete':
                foreach ($mediaItems as $media) {
                    \Storage::disk('public')->delete($media->file_path);
                    $media->delete();
                }
                return response()->json(['success' => true, 'message' => count($mediaItems) . ' media items deleted.']);
            
            case 'move_to_folder':
                $folder = trim($validated['folder'] ?? '', '/');
                foreach ($mediaItems as $media) {
                    // Move the file in storage
                    $oldPath = $media->file_path;
                    $filename = basename($media->file_path);
                    $newPath = $folder ? $folder . '/' . $filename : 'uploads/' . $filename;

                    if (\Storage::disk('public')->exists($oldPath)) {
                        \Storage::disk('public')->move($oldPath, $newPath);
                    }

                    $media->folder = $folder ?: null;
                    $media->file_path = $newPath;
                    $media->save();
                }
                return response()->json(['success' => true, 'message' => count($mediaItems) . ' media items moved.']);

            case 'rename':
                $base = $validated['name_base'] ?? null;
                if (!$base) {
                    return response()->json(['success' => false, 'message' => 'A name base is required to rename.'], 422);
                }
                $index = 0;
                foreach ($mediaItems as $media) {
                    $index++;
                    $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                    $filename = $index === 1 && count($mediaItems) === 1
                        ? $base . '.' . $extension
                        : $base . '-' . $index . '.' . $extension;
                    $folder = trim((string) $media->folder, '/');
                    $newPath = ($folder ? $folder . '/' : 'uploads/') . $filename;
                    $oldPath = $media->file_path;

                    // Avoid overwriting an existing file.
                    $candidate = $newPath;
                    $suffix = 2;
                    while (\Storage::disk('public')->exists($candidate) && $candidate !== $oldPath) {
                        $candidate = ($folder ? $folder . '/' : 'uploads/') . $base . '-' . $suffix . '.' . $extension;
                        $suffix++;
                    }
                    $newPath = $candidate;
                    $filename = basename($newPath);

                    if (\Storage::disk('public')->exists($oldPath) && $oldPath !== $newPath) {
                        \Storage::disk('public')->move($oldPath, $newPath);
                    }

                    $media->file_name = $filename;
                    $media->file_path = $newPath;
                    $media->save();
                }
                return response()->json(['success' => true, 'message' => count($mediaItems) . ' media items renamed.']);

            case 'set_alt_text':
                $alt = trim((string) ($validated['alt_text'] ?? ''));
                if (!$alt) {
                    return response()->json(['success' => false, 'message' => 'Alt text is required.'], 422);
                }
                $updated = 0;
                foreach ($mediaItems as $media) {
                    if (!str_starts_with((string) $media->mime_type, 'image/')) {
                        continue; // Alt text only applies to images.
                    }
                    $media->alt_text = $alt;
                    $media->save();
                    $updated++;
                }
                if ($updated === 0) {
                    return response()->json(['success' => false, 'message' => 'No images selected — alt text only applies to images.'], 422);
                }
                return response()->json(['success' => true, 'message' => $updated . ' image(s) updated.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }

    /**
     * Replace the file behind a media item (keeps the same record/path).
     */
    public function replace(Request $request, Media $media)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:' . (10 * 1024), 'mimes:jpeg,jpg,png,gif,webp,pdf,mp4,mov,webm,mp3,wav,ogg'],
        ]);

        $file = $request->file('file');
        $detectedMime = $file->getMimeType();

        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'video/mp4', 'video/quicktime', 'video/webm',
            'audio/mpeg', 'audio/wav', 'audio/ogg',
        ];

        if (!in_array($detectedMime, $allowedMimeTypes, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid file type.'], 422);
        }

        $disk = \Storage::disk('public');

        // Move the old file aside so the new one can take its place.
        $oldPath = $media->file_path;
        $newExtension = strtolower($file->getClientOriginalExtension()) ?: pathinfo($oldPath, PATHINFO_EXTENSION);
        $newPath = $oldPath; // keep the same path/filename so references stay valid

        // Store the new file at the same path.
        $stream = fopen($file->getRealPath(), 'rb');
        $disk->put($newPath, $stream);
        fclose($stream);

        // Re-optimize images (original + webp + responsive variants).
        if (str_starts_with($detectedMime, 'image/')) {
            $fullPath = storage_path('app/public/' . $newPath);
            $this->convertToWebP($fullPath);
            $this->generateResponsiveImages($fullPath);
            $this->optimizeImage($fullPath);
        }

        $media->update([
            'file_size' => $file->getSize(),
            'mime_type' => $detectedMime,
            'meta' => array_merge((array) $media->meta, ['replaced_at' => now()->toIso8601String()]),
        ]);

        return response()->json(['success' => true, 'message' => 'File replaced.', 'size' => $file->getSize()]);
    }

    /**
     * Suggest an alt text derived from the file name.
     */
    public function generateAlt(Media $media)
    {
        if (!str_starts_with((string) $media->mime_type, 'image/')) {
            return response()->json(['success' => false, 'message' => 'Alt text only applies to images.'], 422);
        }

        $suggested = $this->suggestAltText($media);

        return response()->json(['success' => true, 'alt_text' => $suggested]);
    }

    /**
     * Derive a human-friendly alt text from the stored file name.
     */
    protected function suggestAltText(Media $media): string
    {
        $name = pathinfo((string) $media->file_name, PATHINFO_FILENAME);

        // Strip the generated "<timestamp>_<random>_" prefix.
        $name = preg_replace('/^\d{10,}_[A-Za-z0-9]{4,}_/', '', $name) ?? $name;

        $name = str_replace(['_', '-', '.'], ' ', $name);
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? '');

        if ($name === '') {
            $name = 'image';
        }

        $suggested = ucwords(strtolower($name));

        return mb_substr($suggested, 0, 255);
    }

    /**
     * List folders
     */
    public function folders(Request $request)
    {
        $query = Media::whereNotNull('folder');
        
        if ($request->has('search') && $request->search) {
            $query->where('folder', 'like', '%' . $request->search . '%');
        }
        
        $folders = $query->distinct()->pluck('folder')->sort()->values();
        
        return response()->json($folders);
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z0-9_\/-]+$/',
        ]);

        $folder = trim($validated['name'], '/');
        
        // Validate folder doesn't already exist in root
        $existing = Media::where('folder', $folder)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Folder already exists.'], 409);
        }
        
        return response()->json(['success' => true, 'message' => 'Folder created successfully.', 'folder' => $folder]);
    }

    /**
     * Delete a folder
     */
    public function deleteFolder(Request $request)
    {
        $validated = $request->validate([
            'folder' => 'required|string|max:255',
        ]);

        $folder = trim($validated['folder'], '/');
        
        // Find all media in this folder
        $mediaItems = Media::where('folder', $folder)->get();
        
        foreach ($mediaItems as $media) {
            \Storage::disk('public')->delete($media->file_path);
            $media->delete();
        }
        
        return response()->json(['success' => true, 'message' => 'Folder and all media deleted.']);
    }

    /**
     * Update media metadata (alt_text, caption)
     */
    public function updateMetadata(Request $request, Media $media)
    {
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
        ]);

        // Images require alt text — refuse to clear it.
        if (str_starts_with((string) $media->mime_type, 'image/')) {
            if (blank($validated['alt_text'] ?? null)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Alt text is required for images.',
                ], 422);
            }
        }

        $media->update($validated);
        
        return response()->json(['success' => true, 'data' => $media->only('alt_text', 'caption')]);
    }

    /**
     * Get media in a folder
     */
    public function getFolderMedia(Request $request, $folder = null)
    {
        $query = Media::whereNotNull('folder');
        
        if ($folder) {
            $query->where('folder', $folder);
        }
        
        $media = $query->paginate(20);
        
        return view('admin.media.partials.grid', compact('media'))->render();
    }
}
