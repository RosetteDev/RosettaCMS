@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>{{ $form->name }}</h1>
            @if($form->description)
                <p>{{ $form->description }}</p>
            @endif
        </div>
    </div>

    <!-- Form Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 680px; margin: 0 auto;">
                @if(session('status'))
                    <div role="status" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                        {{ session('status') }}
                    </div>
                @endif
                @if(session('error'))
                    <div role="alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div role="alert" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                        <strong>Please check the form:</strong>
                        <ul style="margin:0.5rem 0 0 1.25rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="contact-form" action="{{ route('form.submit', $form->slug) }}" method="POST" novalidate>
                    @csrf
                    <div style="position:absolute;left:-9999px;" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    @foreach($form->fields ?? [] as $field)
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            @if($field['type'] === 'checkbox')
                                <label style="display:flex;align-items:flex-start;gap:0.6rem;cursor:pointer;font-weight:normal;">
                                    <input type="checkbox" name="{{ $field['name'] }}" value="1" @if($field['required']) required @endif @checked(old($field['name'])) style="margin-top:0.3rem;width:18px;height:18px;flex-shrink:0;">
                                    <span>
                                        {{ $field['label'] }}@if($field['required']) <span style="color:#dc2626;">*</span>@endif
                                        @if($field['help_text'])
                                            <span style="display:block;font-size:0.85rem;color:var(--text-muted,#6b7280);margin-top:0.25rem;">{{ $field['help_text'] }}</span>
                                        @endif
                                    </span>
                                </label>
                            @elseif($field['type'] === 'radio')
                                <fieldset style="border:0;padding:0;margin:0;">
                                    <legend style="font-weight:600;margin-bottom:0.5rem;padding:0;">{{ $field['label'] }}@if($field['required']) <span style="color:#dc2626;">*</span>@endif</legend>
                                    @foreach($field['options'] ?? [] as $option)
                                        <label style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.4rem;cursor:pointer;font-weight:normal;">
                                            <input type="radio" name="{{ $field['name'] }}" value="{{ $option }}" @if($field['required']) required @endif @checked(old($field['name']) === $option)>
                                            <span>{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </fieldset>
                            @else
                                <label for="{{ $field['name'] }}">{{ $field['label'] }}@if($field['required']) <span style="color:#dc2626;">*</span>@endif</label>
                                @if($field['type'] === 'textarea')
                                    <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="5" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">{{ old($field['name']) }}</textarea>
                                @elseif($field['type'] === 'select')
                                    <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" @if($field['required']) required @endif>
                                        <option value="">{{ $field['placeholder'] ?: 'Select…' }}</option>
                                        @foreach($field['options'] ?? [] as $option)
                                            <option value="{{ $option }}" @selected(old($field['name']) === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field['type'] === 'number')
                                    <input type="number" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">
                                @elseif($field['type'] === 'date')
                                    <input type="date" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" @if($field['required']) required @endif>
                                @elseif($field['type'] === 'email')
                                    <input type="email" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">
                                @elseif($field['type'] === 'phone')
                                    <input type="tel" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">
                                @else
                                    <input type="text" id="{{ $field['name'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">
                                @endif
                                @if($field['help_text'])
                                    <span style="display:block;font-size:0.85rem;color:var(--text-muted,#6b7280);margin-top:0.35rem;">{{ $field['help_text'] }}</span>
                                @endif
                                @if($errors->first($field['name']))
                                    <span style="display:block;font-size:0.85rem;color:#dc2626;margin-top:0.35rem;">{{ $errors->first($field['name']) }}</span>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem 1.5rem; font-size: 1rem;">{{ $form->setting('submit_label', 'Send') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
