<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;

class MediaController extends Controller
{
    /**
     * Display a listing of media.
     */
    public function index()
    {
        $media = Media::with('user', 'site')->paginate(20);
        return view('admin.media.index', compact('media'));
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
            'site_id' => 'nullable|exists:sites,id',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filepath = $file->storeAs('media', $filename, 'public');
            
            $media = Media::create([
                'user_id' => auth()->id(),
                'site_id' => $request->site_id ?? 1,
                'file_name' => $filename,
                'file_path' => $filepath,
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
}
