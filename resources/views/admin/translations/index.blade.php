@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Translations</h1>
            <p>Reusable translated strings shared across the site. Reference a key with <code>reusable_string('your.key')</code> anywhere in the theme.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if($languages->isEmpty())
        <div class="container mt-4">
            <div class="alert alert-info">
                No active languages yet. <a href="{{ route('admin.languages.index') }}">Add a language</a> to start building translations.
            </div>
        </div>
    @else
        {{-- Add form --}}
        <div class="container mt-4">
            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem;">
                <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-color); margin-bottom: 1rem;">Add or update a string</h2>
                <form method="POST" action="{{ route('admin.translations.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label small text-muted" for="key">Key</label>
                            <input type="text" name="key" id="key" class="form-control" required
                                   value="{{ old('key') }}" placeholder="e.g. nav.contact" maxlength="255">
                            <div class="form-text">Dot-separated, e.g. <code>nav.contact</code></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted" for="language_id">Language</label>
                            <select name="language_id" id="language_id" class="form-select" required>
                                @foreach($languages as $language)
                                    <option value="{{ $language->id }}" {{ old('language_id') == $language->id ? 'selected' : '' }}>
                                        {{ $language->flag }} {{ $language->name }} ({{ $language->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted" for="value">Value</label>
                            <input type="text" name="value" id="value" class="form-control" required
                                   value="{{ old('value') }}" placeholder="Translated text" maxlength="500">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save string</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Existing strings, grouped by language --}}
        @foreach($languages as $language)
            @php $rows = $byLanguage->get($language->id, collect()); @endphp
            <div class="container mt-4">
                <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-color); margin-bottom: 0.75rem;">
                    {{ $language->flag }} {{ $language->name }} <code style="font-size: 0.75rem; opacity: 0.6;">{{ $language->code }}</code>
                </h2>

                @if($rows->isEmpty())
                    <p class="text-muted small">No strings stored for this language yet.</p>
                @else
                    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
                        <div class="table-responsive"><table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Value</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $string)
                                    <tr>
                                        <td><code>{{ $string->key }}</code></td>
                                        <td>{{ $string->value }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.translations.destroy', $string) }}" class="d-inline"
                                                  onsubmit="return confirm('Delete this translation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
@endsection
