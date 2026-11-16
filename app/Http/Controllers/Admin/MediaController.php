<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
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

        return view('admin.media.index', compact('media', 'folders'));
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
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpeg,png,gif,svg,pdf,mp4,mov,webm',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'folder' => 'nullable|string|max:255',
            'site_id' => 'nullable|exists:sites,id',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            
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
            
            $media = Media::create([
                'user_id' => auth()->id(),
                'site_id' => $request->site_id ?? 1,
                'file_name' => $filename,
                'file_path' => $storagePath,
                'folder' => $folder ?: null,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'alt_text' => $validated['alt_text'],
                'caption' => $validated['caption'],
            ]);

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
            'action' => 'required|in:delete,move_to_folder',
            'folder' => 'nullable|string|max:255',
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
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
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
