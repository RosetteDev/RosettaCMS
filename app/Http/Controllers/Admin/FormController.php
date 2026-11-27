<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    protected function site(): ?Site
    {
        return Site::first();
    }

    /**
     * List all forms for the site.
     */
    public function index()
    {
        $site = $this->site();

        $forms = $site
            ? Form::where('site_id', $site->id)
                ->withCount('submissions')
                ->orderBy('name')
                ->get()
            : collect();

        $stats = [
            'total_forms' => $forms->count(),
            'enabled_forms' => $forms->where('enabled', true)->count(),
            'total_submissions' => FormSubmission::whereIn('form_id', $forms->pluck('id'))->count(),
            'new_submissions' => FormSubmission::whereIn('form_id', $forms->pluck('id'))->where('status', 'new')->count(),
        ];

        return view('admin.forms.builder', compact('forms', 'stats', 'site'));
    }

    public function create()
    {
        return view('admin.forms.form', [
            'form' => null,
            'fieldTypes' => Form::FIELD_TYPES,
            'optionTypes' => Form::OPTION_FIELD_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        $site = $this->site();
        abort_unless($site, 500, 'No site configured.');

        $form = Form::create([
            'site_id' => $site->id,
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'fields' => $validated['fields'],
            'settings' => $validated['settings'],
            'enabled' => $validated['enabled'],
        ]);

        return redirect()->route('admin.forms-builder.index')
            ->with('status', "Form '{$form->name}' created.");
    }

    public function edit(Form $form)
    {
        abort_unless($this->owns($form), 404);

        return view('admin.forms.form', [
            'form' => $form,
            'fieldTypes' => Form::FIELD_TYPES,
            'optionTypes' => Form::OPTION_FIELD_TYPES,
        ]);
    }

    public function update(Request $request, Form $form)
    {
        abort_unless($this->owns($form), 404);

        $validated = $this->validateForm($request, $form->id);

        $form->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'fields' => $validated['fields'],
            'settings' => $validated['settings'],
            'enabled' => $validated['enabled'],
        ]);

        return redirect()->route('admin.forms-builder.index')
            ->with('status', "Form '{$form->name}' updated.");
    }

    public function destroy(Form $form)
    {
        abort_unless($this->owns($form), 404);

        FormSubmission::where('form_id', $form->id)->delete();
        $name = $form->name;
        $form->delete();

        return redirect()->route('admin.forms-builder.index')
            ->with('status', "Form '{$name}' deleted.");
    }

    /**
     * Submissions inbox for a single form.
     */
    public function submissions(Request $request, Form $form)
    {
        abort_unless($this->owns($form), 404);

        $status = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        $query = FormSubmission::where('form_id', $form->id);

        if ($status !== 'all' && in_array($status, ['new', 'read', 'spam', 'handled'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->whereRaw('data LIKE ?', ['%' . $search . '%'])
                ->orWhere('spam_reason', 'like', "%{$search}%");
        }

        $submissions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $statistics = [
            'total' => FormSubmission::where('form_id', $form->id)->count(),
            'new' => FormSubmission::where('form_id', $form->id)->where('status', 'new')->count(),
            'spam' => FormSubmission::where('form_id', $form->id)->where('status', 'spam')->count(),
            'handled' => FormSubmission::where('form_id', $form->id)->where('status', 'handled')->count(),
        ];

        return view('admin.forms.submissions', compact('form', 'submissions', 'status', 'search', 'statistics'));
    }

    /**
     * Show a single submission.
     */
    public function submission(Form $form, FormSubmission $submission)
    {
        abort_unless($this->owns($form) && $submission->form_id === $form->id, 404);

        if ($submission->status === 'new') {
            $submission->update(['status' => 'read']);
            $submission->refresh();
        }

        return view('admin.forms.submission', compact('form', 'submission'));
    }

    public function updateSubmissionStatus(Request $request, Form $form, FormSubmission $submission)
    {
        abort_unless($this->owns($form) && $submission->form_id === $form->id, 404);

        $validated = $request->validate([
            'status' => 'required|in:new,read,spam,handled',
        ]);

        $submission->update(['status' => $validated['status']]);

        return back()->with('status', 'Submission status updated.');
    }

    public function destroySubmission(Form $form, FormSubmission $submission)
    {
        abort_unless($this->owns($form) && $submission->form_id === $form->id, 404);

        $submission->delete();

        return back()->with('status', 'Submission deleted.');
    }

    /**
     * Validate and normalize a form payload.
     */
    protected function validateForm(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'enabled' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'array', 'max:50'],
            'fields.*.name' => ['nullable', 'string', 'max:60'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:' . implode(',', array_keys(Form::FIELD_TYPES))],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.help_text' => ['nullable', 'string', 'max:500'],
            'fields.*.options' => ['nullable', 'string', 'max:2000'],
            'settings.submit_label' => ['nullable', 'string', 'max:100'],
            'settings.success_message' => ['nullable', 'string', 'max:500'],
            'settings.mail_to' => ['nullable', 'email', 'max:255'],
            'settings.spam_protection' => ['nullable', 'boolean'],
            'settings.confirm_message' => ['nullable', 'string', 'max:500'],
        ]);

        $site = $this->site();

        $slug = ($validated['slug'] ?? null) ?: Str::slug($validated['name']);

        if ($slug === '') {
            $slug = 'form-' . Str::lower(Str::random(8));
        }

        $slugQuery = Form::where('slug', $slug);
        if ($site) {
            $slugQuery->where('site_id', $site->id);
        }
        if ($ignoreId) {
            $slugQuery->where('id', '!=', $ignoreId);
        }
        abort_if($slugQuery->exists(), 422, 'A form with this slug already exists.');

        // Normalize + derive names.
        $fields = [];
        foreach ($validated['fields'] ?? [] as $field) {
            $name = trim((string) ($field['name'] ?? ''));
            if ($name === '') {
                $name = Str::slug($field['label'], '_');
            }
            $name = strtolower($name);

            // Re-validate derived name.
            if (!preg_match('/^[a-z][a-z0-9_]{0,49}$/', $name)) {
                $name = 'field_' . Str::lower(Str::random(8));
            }

            $fields[] = array_merge($field, ['name' => $name]);
        }

        $normalizedFields = Form::normalizeFields($fields);

        abort_if(count($normalizedFields) === 0, 422, 'At least one valid field is required.');

        // Ensure unique field names.
        $seen = [];
        foreach ($normalizedFields as &$field) {
            $base = $field['name'];
            $i = 2;
            while (in_array($field['name'], $seen, true)) {
                $field['name'] = $base . '_' . $i++;
            }
            $seen[] = $field['name'];
        }
        unset($field);

        $settings = Form::normalizeSettings($validated['settings'] ?? []);

        return [
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'enabled' => $request->boolean('enabled', true),
            'fields' => $normalizedFields,
            'settings' => $settings,
        ];
    }

    /**
     * Ensure the form belongs to the current site.
     */
    protected function owns(Form $form): bool
    {
        $site = $this->site();

        return $site && $form->site_id === $site->id;
    }
}
