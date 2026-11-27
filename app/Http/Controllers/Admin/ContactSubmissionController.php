<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\Site;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    /**
     * List submissions with status filter and search.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = ContactSubmission::query();

        if ($status !== 'all' && in_array($status, ['new', 'read', 'spam', 'handled'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $submissions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statistics = [
            'total' => ContactSubmission::count(),
            'new' => ContactSubmission::where('status', 'new')->count(),
            'read' => ContactSubmission::where('status', 'read')->count(),
            'spam' => ContactSubmission::where('status', 'spam')->count(),
            'handled' => ContactSubmission::where('status', 'handled')->count(),
        ];

        return view('admin.forms.index', compact('submissions', 'status', 'search', 'statistics'));
    }

    /**
     * Show a single submission.
     */
    public function show(ContactSubmission $submission)
    {
        if ($submission->status === 'new') {
            $submission->update(['status' => 'read']);
            $submission->refresh();
        }

        return view('admin.forms.show', compact('submission'));
    }

    /**
     * Change a submission's status.
     */
    public function updateStatus(Request $request, ContactSubmission $submission)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,spam,handled'],
        ]);

        $submission->update(['status' => $validated['status']]);

        return back()->with('status', 'Submission marked as ' . $validated['status'] . '.');
    }

    /**
     * Delete a submission.
     */
    public function destroy(ContactSubmission $submission)
    {
        $submission->delete();

        return back()->with('status', 'Submission deleted.');
    }
}
