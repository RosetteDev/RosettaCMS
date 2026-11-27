@extends('admin.layouts.app')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.forms-builder.index') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> All forms</a>
        <h1 class="h3 mb-1">{{ $form ? 'Edit form' : 'New form' }}</h1>
        <p class="text-muted mb-0">{{ $form ? 'Update fields, settings and availability for this form.' : 'Create a form to collect data from your visitors.' }}</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $form ? route('admin.forms-builder.update', $form) : route('admin.forms-builder.store') }}" id="form-builder">
        @csrf
        @if($form)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent"><h2 class="h5 mb-0">General</h2></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Form name <span style="color:#dc2626;">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $form->name ?? '') }}" required maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label for="slug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug', $form->slug ?? '') }}" maxlength="100" placeholder="auto from name" pattern="[a-z0-9]+(-[a-z0-9]+)*">
                                <div class="form-text">Used in the public URL. Lowercase letters, numbers and hyphens. Leave blank to derive from the name.</div>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2" maxlength="2000">{{ old('description', $form->description ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $form->enabled ?? true))>
                                    <span>Enabled — visitors can see and submit this form</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Fields</h2>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-field"><i class="fas fa-plus"></i> Add field</button>
                    </div>
                    <div class="card-body" id="field-rows">
                        @php
                            $oldFields = old('fields', $form->fields ?? []);
                            $oldIndex = 0;
                        @endphp
                        @foreach($oldFields as $index => $field)
                            @php
                                $fieldType = $field['type'] ?? 'text';
                                $hasOptions = in_array($fieldType, $optionTypes, true);
                            @endphp
                            <div class="field-row border rounded p-3 mb-3" data-type="{{ $fieldType }}">
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <label class="form-label small">Label <span style="color:#dc2626;">*</span></label>
                                        <input type="text" class="form-control" name="fields[{{ $oldIndex }}][label]" value="{{ $field['label'] ?? '' }}" required maxlength="255">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Type</label>
                                        <select class="form-select field-type" name="fields[{{ $oldIndex }}][type]">
                                            @foreach($fieldTypes as $key => $label)
                                                <option value="{{ $key }}" @selected($fieldType === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end pb-2">
                                        <label class="form-label small d-flex align-items-center gap-2">
                                            <input type="checkbox" name="fields[{{ $oldIndex }}][required]" value="1" @checked(!empty($field['required']))> Required
                                        </label>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end justify-content-end pb-1">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-field" title="Remove field"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label small">Name</label>
                                        <input type="text" class="form-control" name="fields[{{ $oldIndex }}][name]" value="{{ $field['name'] ?? '' }}" placeholder="auto from label" maxlength="60">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Placeholder</label>
                                        <input type="text" class="form-control" name="fields[{{ $oldIndex }}][placeholder]" value="{{ $field['placeholder'] ?? '' }}" maxlength="255">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Help text</label>
                                        <input type="text" class="form-control" name="fields[{{ $oldIndex }}][help_text]" value="{{ $field['help_text'] ?? '' }}" maxlength="500">
                                    </div>
                                </div>
                                <div class="row g-2 mt-1 field-options-wrap" @if(!$hasOptions) style="display:none;" @endif>
                                    <div class="col-12">
                                        <label class="form-label small">Options — one per line</label>
                                        <textarea class="form-control" name="fields[{{ $oldIndex }}][options]" rows="3" maxlength="2000" placeholder="Option A&#10;Option B&#10;Option C">{{ is_array($field['options'] ?? null) ? implode("\n", $field['options']) : ($field['options'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            @php $oldIndex++; @endphp
                        @endforeach
                        @if(count($oldFields) === 0)
                            <p class="text-muted small mb-0" id="no-fields-hint">No fields yet. Add a field to start building the form.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent"><h2 class="h5 mb-0">Settings</h2></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="submit_label" class="form-label">Submit label</label>
                            <input type="text" class="form-control" id="submit_label" name="settings[submit_label]" value="{{ old('settings.submit_label', $form ? $form->setting('submit_label', 'Send') : 'Send') }}" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="success_message" class="form-label">Success message</label>
                            <textarea class="form-control" id="success_message" name="settings[success_message]" rows="2" maxlength="500">{{ old('settings.success_message', $form ? $form->setting('success_message', 'Thanks! Your message has been sent.') : 'Thanks! Your message has been sent.') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="mail_to" class="form-label">Notification email</label>
                            <input type="email" class="form-control" id="mail_to" name="settings[mail_to]" value="{{ old('settings.mail_to', $form ? ($form->setting('mail_to') ?? '') : '') }}" maxlength="255" placeholder="Leave blank to use the site admin email">
                        </div>
                        <div class="mb-3">
                            <label class="d-flex align-items-center gap-2">
                                <input type="checkbox" name="settings[spam_protection]" value="1" @checked(old('settings.spam_protection', $form ? $form->setting('spam_protection', true) : true))>
                                <span>Spam protection (honeypot + heuristics)</span>
                            </label>
                        </div>
                        <div class="mb-0">
                            <label for="confirm_message" class="form-label">Confirmation note <span class="text-muted small">(optional)</span></label>
                            <input type="text" class="form-control" id="confirm_message" name="settings[confirm_message]" value="{{ old('settings.confirm_message', $form ? ($form->setting('confirm_message') ?? '') : '') }}" maxlength="500" placeholder="e.g. We reply within one business day">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent"><h2 class="h5 mb-0">Preview</h2></div>
                    <div class="card-body">
                        @if($form)
                            <p class="text-muted small mb-2">Public URL:</p>
                            <code class="d-block mb-3">{{ url('/forms/' . $form->slug) }}</code>
                            <a href="{{ route('form.show', $form->slug) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt"></i> Open public form</a>
                        @else
                            <p class="text-muted small mb-0">Save the form first to preview it publicly.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">{{ $form ? 'Save changes' : 'Create form' }}</button>
            <a href="{{ route('admin.forms-builder.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>

    <template id="field-row-template">
        <div class="field-row border rounded p-3 mb-3">
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label small">Label <span style="color:#dc2626;">*</span></label>
                    <input type="text" class="form-control" name="fields[__INDEX__][label]" required maxlength="255">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Type</label>
                    <select class="form-select field-type" name="fields[__INDEX__][type]">
                        @foreach($fieldTypes as $key => $label)
                            <option value="{{ $key }}" @if($key === 'text') selected @endif>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end pb-2">
                    <label class="form-label small d-flex align-items-center gap-2">
                        <input type="checkbox" name="fields[__INDEX__][required]" value="1"> Required
                    </label>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end pb-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-field" title="Remove field"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-4">
                    <label class="form-label small">Name</label>
                    <input type="text" class="form-control" name="fields[__INDEX__][name]" placeholder="auto from label" maxlength="60">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Placeholder</label>
                    <input type="text" class="form-control" name="fields[__INDEX__][placeholder]" maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Help text</label>
                    <input type="text" class="form-control" name="fields[__INDEX__][help_text]" maxlength="500">
                </div>
            </div>
            <div class="row g-2 mt-1 field-options-wrap" style="display:none;">
                <div class="col-12">
                    <label class="form-label small">Options — one per line</label>
                    <textarea class="form-control" name="fields[__INDEX__][options]" rows="3" maxlength="2000" placeholder="Option A&#10;Option B&#10;Option C"></textarea>
                </div>
            </div>
        </div>
    </template>

    <script>
        (function () {
            const optionTypes = @json($optionTypes);
            const rows = document.getElementById('field-rows');
            const addBtn = document.getElementById('add-field');
            const template = document.getElementById('field-row-template');
            let counter = rows.querySelectorAll('.field-row').length;

            const showHint = () => {
                const hint = document.getElementById('no-fields-hint');
                if (hint) hint.remove();
            };

            const syncOptions = (row) => {
                const select = row.querySelector('.field-type');
                const wrap = row.querySelector('.field-options-wrap');
                if (!select || !wrap) return;
                const show = optionTypes.includes(select.value);
                wrap.style.display = show ? '' : 'none';
                row.dataset.type = select.value;
            };

            rows.querySelectorAll('.field-row').forEach(syncOptions);

            rows.addEventListener('change', (e) => {
                if (e.target.classList.contains('field-type')) {
                    syncOptions(e.target.closest('.field-row'));
                }
            });

            rows.addEventListener('click', (e) => {
                const btn = e.target.closest('.remove-field');
                if (btn) {
                    const row = btn.closest('.field-row');
                    if (row) row.remove();
                    return;
                }
            });

            addBtn.addEventListener('click', () => {
                showHint();
                const index = counter++;
                const html = template.innerHTML.replaceAll('__INDEX__', index);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                const row = wrapper.firstElementChild;
                rows.appendChild(row);
                syncOptions(row);
                const label = row.querySelector('input[name$="[label]"]');
                if (label) label.focus();
            });

            // Keep input name indexes unique after removals (cosmetic only).
            rows.addEventListener('change', () => {});
        })();
    </script>
@endsection
