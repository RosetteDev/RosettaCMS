@extends('admin.layouts.app')

@section('title', 'Settings')

@push('styles')
<style>
    .settings-layout { display: flex; gap: 2rem; }
    .settings-nav { width: 250px; flex-shrink: 0; }

    @media (max-width: 991px) {
        .settings-container { padding: 1rem !important; }
        .settings-layout { flex-direction: column; gap: 1rem; }
        .settings-nav { width: 100%; flex-shrink: 1; }
        .settings-nav nav { flex-direction: row !important; overflow-x: auto; -webkit-overflow-scrolling: touch; gap: 0.5rem !important; padding-bottom: 0.5rem; }
        .settings-nav a { white-space: nowrap; }
    }
</style>
@endpush

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Settings</h1>
            <p>Configure your site's global settings</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="container settings-container" style="max-width: 1200px; padding: 2rem;">
        <div class="settings-layout">
            <!-- Sidebar Navigation -->
            <div class="settings-nav">
                <div style="background-color: var(--light-color); border-radius: 8px; padding: 1rem;">
                    <h3 style="font-size: 0.85rem; font-weight: bold; color: var(--text-color); text-transform: uppercase; margin-bottom: 1rem;">Settings Navigation</h3>
                    <nav style="display: flex; flex-direction: column; gap: 0.25rem;">
                        <a href="#site-info" class="settings-nav-item settings-nav-active" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: white; background-color: var(--primary-color);">Site Information</a>
                        <a href="#layout" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Layout Options</a>
                        <a href="#home-page" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Home Page</a>
                        <a href="#comments" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Comments</a>
                        <a href="#spam" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Spam Protection</a>
                        <a href="#custom-form-fields" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Form Fields</a>
                        <a href="#display" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Display</a>
                        <a href="#general" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">General</a>
                        <a href="#ecommerce" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">E-commerce</a>
                        <a href="#permalink" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Permalinks</a>
                        <a href="#seo" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">SEO</a>
                        <a href="#site-code" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Site Code</a>
                        <a href="#security" class="settings-nav-item" style="padding: 0.75rem 1rem; border-radius: 6px; text-decoration: none; font-size: 0.9rem; color: var(--text-color); background-color: transparent;">Security</a>
                    </nav>
                </div>
            </div>

            <!-- Settings Form -->
            <div style="flex: 1;">
                <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf

                        <div id="site-info">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Site Information</h2>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="site_name">
                                    Site Name *
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="site_name" 
                                       type="text" 
                                       name="site_name" 
                                       value="{{ old('site_name', $settings->site_title ?? ($site->name ?? '')) }}" 
                                       required 
                                       placeholder="Rosetta CMS">

                                @error('site_name')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="site_description">
                                    Site Description
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; min-height: 100px;" 
                                          id="site_description" 
                                          name="site_description"
                                          placeholder="A modern, flexible content management system...">{{ old('site_description', $settings->site_description ?? '') }}</textarea>

                                @error('site_description')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($translationLanguages->isNotEmpty())
                                <div style="margin-bottom: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                                    <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-color); margin-bottom: 1rem;">Translated site name &amp; description</h3>
                                    @foreach($translationLanguages as $lang)
                                        @php
                                            $translation = $existingTranslations->get($lang->id);
                                            $meta = is_array($translation?->meta) ? $translation->meta : [];
                                        @endphp
                                        <div style="margin-bottom: 1.25rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                            <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-color); margin-bottom: 0.75rem;">{{ $lang->flag }} {{ $lang->name }} <code style="font-size: 0.75rem; color: var(--text-color); opacity: 0.6;">{{ $lang->code }}</code></h4>
                                            <div style="margin-bottom: 0.75rem;">
                                                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: var(--text-color); margin-bottom: 0.25rem;" for="translation-title-{{ $lang->id }}">Site Name</label>
                                                <input style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem;"
                                                       id="translation-title-{{ $lang->id }}"
                                                       type="text"
                                                       name="translations[{{ $lang->id }}][title]"
                                                       maxlength="255"
                                                       value="{{ old('translations.' . $lang->id . '.title', $meta['title'] ?? '') }}">
                                            </div>
                                            <div>
                                                <label style="display: block; font-size: 0.85rem; font-weight: 500; color: var(--text-color); margin-bottom: 0.25rem;" for="translation-description-{{ $lang->id }}">Site Description</label>
                                                <textarea style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; min-height: 60px;"
                                                          id="translation-description-{{ $lang->id }}"
                                                          name="translations[{{ $lang->id }}][description]"
                                                          rows="2">{{ old('translations.' . $lang->id . '.description', $meta['description'] ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="admin_email">
                                    Admin Email *
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="admin_email" 
                                       type="email" 
                                       name="admin_email" 
                                       value="{{ old('admin_email', $settings->admin_email ?? '') }}" 
                                       required 
                                       placeholder="admin@rosettacms.com">

                                @error('admin_email')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div id="comments" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Comments Settings</h2>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="disable_comments" value="0">
                                    <input type="checkbox" name="disable_comments" value="1" {{ old('disable_comments', $settings->disable_comments ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <span style="color: var(--text-color);">Disable comments on all posts</span>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_comment_approval" value="0">
                                    <input type="checkbox" name="enable_comment_approval" value="1" {{ old('enable_comment_approval', $settings->enable_comment_approval ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Require manual approval for guest comments</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Comments from registered users will auto-publish.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="spam" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Spam Protection</h2>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_akismet" value="0">
                                    <input type="checkbox" name="enable_akismet" value="1" {{ old('enable_akismet', $settings->enable_akismet ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <span style="color: var(--text-color);">Enable Akismet spam filtering</span>
                                </label>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="akismet_api_key">
                                    Akismet API Key
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="akismet_api_key" 
                                       type="text" 
                                       name="akismet_api_key" 
                                       value="{{ old('akismet_api_key', $settings->akismet_api_key ?? '') }}" 
                                       placeholder="Enter your Akismet API key">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Get your API key from <a href="https://akismet.com" target="_blank" style="color: var(--primary-color); text-decoration: none;">akismet.com</a></p>

                                @error('akismet_api_key')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="spam_keywords">
                                    Blocked Keywords (comma-separated)
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; min-height: 100px;" 
                                          id="spam_keywords" 
                                          name="spam_keywords"
                                          placeholder="viagra, cialis, lotto">{{ old('spam_keywords', $settings->spam_keywords ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Comments containing these keywords will be marked as spam.</p>

                                @error('spam_keywords')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="rate_limit_enabled" value="0">
                                    <input type="checkbox" name="rate_limit_enabled" value="1" {{ old('rate_limit_enabled', $settings->rate_limit_enabled ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <span style="color: var(--text-color);">Enable comment rate limiting</span>
                                </label>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="rate_limit_per_minute">
                                    Comments per minute limit
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="rate_limit_per_minute" 
                                       type="number" 
                                       name="rate_limit_per_minute" 
                                       value="{{ old('rate_limit_per_minute', $settings->rate_limit_per_minute ?? 3) }}" 
                                       min="1" 
                                       max="100">

                                @error('rate_limit_per_minute')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="captcha_enabled" value="0">
                                    <input type="checkbox" name="captcha_enabled" value="1" {{ old('captcha_enabled', $settings->captcha_enabled ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <span style="color: var(--text-color);">Enable CAPTCHA for guest comments</span>
                                </label>
                            </div>
                        </div>

                        <div id="custom-form-fields" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 0.5rem;">Custom Form Fields</h2>
                            <p style="font-size: 0.9rem; color: var(--text-color); margin-bottom: 1rem;">Extra fields rendered on the public contact form and stored with each submission. Names must be slugs (letters, numbers, underscores).</p>

                            @if($errors->has('custom_form_fields'))
                                <div style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 6px; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.875rem;">{{ $errors->first('custom_form_fields') }}</div>
                            @endif

                            <div id="custom-form-fields-list" style="display: grid; gap: 0.75rem; margin-bottom: 1rem;">
                                @foreach(($settings->custom_form_fields ?? []) as $field)
                                    <div class="cff-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; align-items: end; background-color: rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 0.75rem;">
                                        <div>
                                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Name</label>
                                            <input type="text" class="cff-name form-control form-control-sm" value="{{ $field['name'] ?? '' }}" placeholder="field_name" required>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Label</label>
                                            <input type="text" class="cff-label form-control form-control-sm" value="{{ $field['label'] ?? '' }}" placeholder="Field label">
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Type</label>
                                            <select class="cff-type form-select form-select-sm">
                                                <option value="text" {{ ($field['type'] ?? 'text') === 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="email" {{ ($field['type'] ?? '') === 'email' ? 'selected' : '' }}>Email</option>
                                                <option value="textarea" {{ ($field['type'] ?? '') === 'textarea' ? 'selected' : '' }}>Textarea</option>
                                                <option value="number" {{ ($field['type'] ?? '') === 'number' ? 'selected' : '' }}>Number</option>
                                            </select>
                                        </div>
                                        <button type="button" class="cff-remove btn btn-danger btn-sm" title="Remove field" style="padding: 0.4rem 0.7rem;">✕</button>
                                        <div style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; align-items: end;">
                                            <div>
                                                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Placeholder (optional)</label>
                                                <input type="text" class="cff-placeholder form-control form-control-sm" value="{{ $field['placeholder'] ?? '' }}" placeholder="Placeholder text">
                                            </div>
                                            <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: var(--text-color); padding-bottom: 0.35rem;">
                                                <input type="checkbox" class="cff-required" value="1" {{ !empty($field['required']) ? 'checked' : '' }}> Required
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" id="cff-add" class="btn btn-outline-secondary btn-sm">+ Add field</button>
                            <input type="hidden" name="custom_form_fields" id="custom_form_fields_json" value="{{ old('custom_form_fields', json_encode($settings->custom_form_fields ?? [])) }}">
                        </div>

                        <div id="display" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Display Settings</h2>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="posts_per_page">
                                    Posts Per Page
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="posts_per_page" 
                                       type="number" 
                                       name="posts_per_page" 
                                       value="{{ old('posts_per_page', $settings->post_per_page ?? 10) }}" 
                                       min="1" 
                                       max="100">

                                @error('posts_per_page')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="theme">
                                    Theme
                                </label>
                                <select style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                        id="theme" 
                                        name="theme">
                                    <option value="rosetta" {{ (old('theme', $site->theme ?? 'rosetta') === 'rosetta') ? 'selected' : '' }}>Rosetta (Default)</option>
                                    <option value="modern" {{ (old('theme', $site->theme ?? 'rosetta') === 'modern') ? 'selected' : '' }}>Modern</option>
                                    <option value="elegant" {{ (old('theme', $site->theme ?? 'rosetta') === 'elegant') ? 'selected' : '' }}>Elegant</option>
                                </select>

                                @error('theme')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Choose your site's theme. You can customize colors and fonts in the Layout Options section below.</p>
                            </div>
                        </div>

                        <div id="theme-options" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Theme Customization</h2>
                            
                            <!-- Font Family -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="font_family">
                                    Font Family
                                </label>
                                <select style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                        id="font_family" 
                                        name="font_family">
                                    <option value="system-ui, sans-serif" {{ (old('font_family', $settings->font_family ?? 'system-ui, sans-serif') === 'system-ui, sans-serif') ? 'selected' : '' }}>System Sans Serif (Default)</option>
                                    <option value="Georgia, 'Times New Roman', serif" {{ (old('font_family', $settings->font_family ?? 'system-ui, sans-serif') === 'Georgia, \'Times New Roman\', serif') ? 'selected' : '' }}>Georgia / Times New Roman</option>
                                    <option value="Inter, system-ui, sans-serif" {{ (old('font_family', $settings->font_family ?? 'system-ui, sans-serif') === 'Inter, system-ui, sans-serif') ? 'selected' : '' }}>Inter (Modern)</option>
                                    <option value="Arial, Helvetica, sans-serif" {{ (old('font_family', $settings->font_family ?? 'system-ui, sans-serif') === 'Arial, Helvetica, sans-serif') ? 'selected' : '' }}>Arial (Clean)</option>
                                    <option value="'Courier New', Courier, monospace" {{ (old('font_family', $settings->font_family ?? 'system-ui, sans-serif') === '\'Courier New\', Courier, monospace') ? 'selected' : '' }}>Courier New (Monospace)</option>
                                </select>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Select the font family for your site's text.</p>
                            </div>
                            
                            <!-- Container Width -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="container_width">
                                    Container Width
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="container_width" 
                                       type="text" 
                                       name="container_width" 
                                       value="{{ old('container_width', $settings->container_width ?? '1200px') }}" 
                                       placeholder="1200px">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Maximum width for your site's content container.</p>
                            </div>

                            <!-- Typography tokens -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="body_font_size">
                                    Body Font Size
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="body_font_size" 
                                       type="text" 
                                       name="body_font_size" 
                                       value="{{ old('body_font_size', $settings->body_font_size ?? '16px') }}" 
                                       placeholder="16px">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Base font size for body text (e.g. 16px, 1rem).</p>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="line_height">
                                    Line Height
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="line_height" 
                                       type="text" 
                                       name="line_height" 
                                       value="{{ old('line_height', $settings->line_height ?? '1.6') }}" 
                                       placeholder="1.6">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Body line height as a unitless multiplier (e.g. 1.6).</p>
                            </div>

                            <!-- Spacing tokens -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="block_gap">
                                    Block Gap
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="block_gap" 
                                       type="text" 
                                       name="block_gap" 
                                       value="{{ old('block_gap', $settings->block_gap ?? '2rem') }}" 
                                       placeholder="2rem">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Vertical space between blocks (e.g. 2rem, 32px).</p>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="section_padding">
                                    Section Padding
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="section_padding" 
                                       type="text" 
                                       name="section_padding" 
                                       value="{{ old('section_padding', $settings->section_padding ?? '3rem') }}" 
                                       placeholder="3rem">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Vertical padding for sections (e.g. 3rem).</p>
                            </div>
                        </div>

                        <div id="layout" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Layout Options</h2>
                            
                            <!-- Logo Settings -->
                            <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-color); margin-bottom: 1rem;">Logo</h3>
                                
                                <div style="margin-bottom: 1rem;">
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="logo_url">
                                        Logo Image URL
                                    </label>
                                    <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                           id="logo_url" 
                                           type="text" 
                                           name="logo_url" 
                                           value="{{ old('logo_url', $settings->logo_url ?? '') }}" 
                                           placeholder="https://example.com/logo.png">
                                    <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Enter the URL to your logo image. Leave empty to use text logo.</p>
                                </div>
                                
                                <div style="margin-bottom: 1rem;">
                                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="logo_text">
                                        Logo Text
                                    </label>
                                    <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                           id="logo_text" 
                                           type="text" 
                                           name="logo_text" 
                                           value="{{ old('logo_text', $settings->logo_text ?? '') }}" 
                                           placeholder="My Website">
                                    <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Text to display as logo if no image is provided.</p>
                                </div>
                            </div>
                            
                            <!-- Favicon/App Icon -->
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="favicon_url">
                                    Favicon / App Icon URL
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="favicon_url" 
                                       type="text" 
                                       name="favicon_url" 
                                       value="{{ old('favicon_url', $settings->favicon_url ?? '') }}" 
                                       placeholder="https://example.com/favicon.ico">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Enter the URL to your favicon (16x16 or 32x32 PNG, ICO, or SVG).</p>
                            </div>
                            
                            <!-- Color Palette -->
                            <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-color); margin-bottom: 1rem;">Color Palette</h3>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                                    <div>
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="primary_color">
                                            Primary Color
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?? '#3b82f6') }}" style="width: 50px; height: 40px; border: none; cursor: pointer;">
                                            <input style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                                   type="text" 
                                                   name="primary_color" 
                                                   value="{{ old('primary_color', $settings->primary_color ?? '#3b82f6') }}">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="secondary_color">
                                            Secondary Color
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color ?? '#6b7280') }}" style="width: 50px; height: 40px; border: none; cursor: pointer;">
                                            <input style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                                   type="text" 
                                                   name="secondary_color" 
                                                   value="{{ old('secondary_color', $settings->secondary_color ?? '#6b7280') }}">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="accent_color">
                                            Accent Color
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="color" name="accent_color" value="{{ old('accent_color', $settings->accent_color ?? '#10b981') }}" style="width: 50px; height: 40px; border: none; cursor: pointer;">
                                            <input style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                                   type="text" 
                                                   name="accent_color" 
                                                   value="{{ old('accent_color', $settings->accent_color ?? '#10b981') }}">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="text_color">
                                            Text Color
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="color" name="text_color" value="{{ old('text_color', $settings->text_color ?? '#1f2937') }}" style="width: 50px; height: 40px; border: none; cursor: pointer;">
                                            <input style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                                   type="text" 
                                                   name="text_color" 
                                                   value="{{ old('text_color', $settings->text_color ?? '#1f2937') }}">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="bg_color">
                                            Background Color
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="color" name="bg_color" value="{{ old('bg_color', $settings->bg_color ?? '#ffffff') }}" style="width: 50px; height: 40px; border: none; cursor: pointer;">
                                            <input style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                                   type="text" 
                                                   name="bg_color" 
                                                   value="{{ old('bg_color', $settings->bg_color ?? '#ffffff') }}">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="border_color">
                                            Border Color
                                        </label>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <input type="color" name="border_color" value="{{ old('border_color', $settings->border_color ?? '#e5e7eb') }}" style="width: 50px; height: 40px; border: none; cursor: pointer;">
                                            <input style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                                   type="text" 
                                                   name="border_color" 
                                                   value="{{ old('border_color', $settings->border_color ?? '#e5e7eb') }}">
                                        </div>
                                    </div>
                                </div>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 1rem;">
                                    These colors will be used throughout the site to customize the look and feel.
                                </p>
                            </div>
                        </div>

                        <div id="home-page" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">Home Page</h2>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="home_post_id">
                                    Select Homepage
                                </label>
                                <select style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                        id="home_post_id" 
                                        name="home_post_id">
                                    <option value="">-- Select a page to display as homepage --</option>
                                    @foreach($pages as $page)
                                        <option value="{{ $page->id }}" {{ old('home_post_id', $site->home_post_id ?? '') == $page->id ? 'selected' : '' }}>
                                            {{ $page->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">
                                    Choose a published page to display as your site's homepage. If no page is selected, the default welcome page will be displayed.
                                </p>
                            </div>
                        </div>

                        <div id="general" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">General Settings</h2>
                        </div>

                        <div id="ecommerce" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 1.5rem;">E-commerce</h2>
                            <p style="font-size: 0.875rem; color: var(--text-color); margin-bottom: 1rem;">Tax applies to the subtotal minus discounts at checkout. Payment capture is manual/invoice-based in the foundation.</p>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; max-width: 300px;">
                                <label for="tax_rate" style="font-weight: 500; color: var(--text-color);">Tax rate (%)</label>
                                <input type="number" name="tax_rate" id="tax_rate" step="0.01" min="0" max="100"
                                       value="{{ old('tax_rate', $settings->tax_rate ?? 0) }}"
                                       style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;">
                            </div>
                        </div>

                        <div id="permalink" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">Permalink Options</h3>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.75rem; font-weight: 500; color: var(--text-color);">Permalink Structure</label>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                    <label style="padding: 1rem; border: 2px solid {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'post_name' ? 'var(--primary-color)' : 'var(--border-color)' }}; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                                        <input type="radio" name="permalink_structure" value="post_name" {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'post_name' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                                        <div>
                                            <span style="font-weight: 600; color: var(--text-color); display: block;">Post name</span>
                                            <span style="font-size: 0.875rem; color: var(--text-color);">https://example.com/sample-post/</span>
                                        </div>
                                    </label>

                                    <label style="padding: 1rem; border: 2px solid {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'date_and_name' ? 'var(--primary-color)' : 'var(--border-color)' }}; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                                        <input type="radio" name="permalink_structure" value="date_and_name" {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'date_and_name' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                                        <div>
                                            <span style="font-weight: 600; color: var(--text-color); display: block;">Date and name</span>
                                            <span style="font-size: 0.875rem; color: var(--text-color);">https://example.com/2024/01/01/sample-post/</span>
                                        </div>
                                    </label>

                                    <label style="padding: 1rem; border: 2px solid {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'numeric' ? 'var(--primary-color)' : 'var(--border-color)' }}; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                                        <input type="radio" name="permalink_structure" value="numeric" {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'numeric' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                                        <div>
                                            <span style="font-weight: 600; color: var(--text-color); display: block;">Numeric</span>
                                            <span style="font-size: 0.875rem; color: var(--text-color);">https://example.com/123/</span>
                                        </div>
                                    </label>

                                    <label style="padding: 1rem; border: 2px solid {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'custom' ? 'var(--primary-color)' : 'var(--border-color)' }}; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 1rem;">
                                        <input type="radio" name="permalink_structure" value="custom" {{ old('permalink_structure', $settings->permalink_structure ?? 'post_name') == 'custom' ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                                        <div style="flex: 1;">
                                            <span style="font-weight: 600; color: var(--text-color); display: block;">Custom structure</span>
                                            <input type="text" name="custom_permalink" value="{{ old('custom_permalink', $settings->custom_permalink ?? '/%postname%/') }}" style="margin-top: 0.5rem; width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.875rem;">
                                            <span style="font-size: 0.875rem; color: var(--text-color); display: block; margin-top: 0.25rem;">
                                                Tags: <code>%postname%</code>, <code>%year%</code>, <code>%monthnum%</code>, <code>%day%</code>, <code>%category%</code>, <code>%author%</code>
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="seo" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">SEO Settings</h3>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="seo_title_suffix">
                                    SEO Title Suffix
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="seo_title_suffix" 
                                       type="text" 
                                       name="seo_title_suffix" 
                                       value="{{ old('seo_title_suffix', $settings->seo_title_suffix ?? ' | Rosetta CMS') }}" 
                                       placeholder="| Site Name">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">This will be appended to all page titles. Use %s as a placeholder for the page title.</p>

                                @error('seo_title_suffix')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="seo_title_template">
                                    Title Template
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;"
                                       id="seo_title_template"
                                       type="text"
                                       name="seo_title_template"
                                       value="{{ old('seo_title_template', $settings->seo_title_template ?? '') }}"
                                       placeholder="%title% | %site%">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">
                                    Use <code>%title%</code> and <code>%site%</code> as placeholders. Leave empty to use the suffix below.
                                </p>
                                @error('seo_title_template')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="og_image">
                                    Default Open Graph Image
                                </label>
                                <div class="input-group">
                                    <input style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;"
                                           id="og_image"
                                           type="url"
                                           name="og_image"
                                           value="{{ old('og_image', $settings->og_image ?? '') }}"
                                           placeholder="https://example.com/og.png">
                                    <button type="button" class="btn btn-outline-secondary" id="og-image-choose"
                                            data-bs-toggle="modal" data-bs-target="#ogImagePickerModal">Choose</button>
                                </div>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Pick from the library or paste a URL. Used when a page/post does not set its own image. Falls back to the site logo.</p>
                                @error('og_image')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="twitter_handle">
                                    Twitter Handle
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;"
                                       id="twitter_handle"
                                       type="text"
                                       name="twitter_handle"
                                       value="{{ old('twitter_handle', $settings->twitter_handle ?? '') }}"
                                       placeholder="@yourhandle">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Emitted as <code>twitter:site</code>.</p>
                                @error('twitter_handle')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="seo_default_keywords">
                                    Default SEO Keywords
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; min-height: 100px;" 
                                          id="seo_default_keywords" 
                                          name="seo_default_keywords"
                                          placeholder="keyword1, keyword2, keyword3">{{ old('seo_default_keywords', $settings->seo_default_keywords ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Comma-separated list of default keywords for your site.</p>

                                @error('seo_default_keywords')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="verification_google">
                                    Search Console Verification
                                </label>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem;">
                                    <div>
                                        <input style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;"
                                               id="verification_google" name="verification_google" type="text"
                                               value="{{ old('verification_google', $settings->verification_meta['google'] ?? '') }}"
                                               placeholder="Google">
                                        @error('verification_google')<p style="color: var(--error-color); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <input style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;"
                                               id="verification_bing" name="verification_bing" type="text"
                                               value="{{ old('verification_bing', $settings->verification_meta['bing'] ?? '') }}"
                                               placeholder="Bing">
                                        @error('verification_bing')<p style="color: var(--error-color); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <input style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;"
                                               id="verification_pinterest" name="verification_pinterest" type="text"
                                               value="{{ old('verification_pinterest', $settings->verification_meta['pinterest'] ?? '') }}"
                                               placeholder="Pinterest">
                                        @error('verification_pinterest')<p style="color: var(--error-color); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <input style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px;"
                                               id="verification_yandex" name="verification_yandex" type="text"
                                               value="{{ old('verification_yandex', $settings->verification_meta['yandex'] ?? '') }}"
                                               placeholder="Yandex">
                                        @error('verification_yandex')<p style="color: var(--error-color); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Emitted as <code>*-site-verification</code> meta tags.</p>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="robots_allow">
                                    robots.txt — Allow rules
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; min-height: 90px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;"
                                          id="robots_allow"
                                          name="robots_allow"
                                          placeholder="/blog
/pages/about">{{ old('robots_allow', $settings->robots_allow ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">One path per line. These are emitted as <code>Allow:</code> rules.</p>
                                @error('robots_allow')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="robots_disallow">
                                    robots.txt — Disallow rules
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; min-height: 90px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;"
                                          id="robots_disallow"
                                          name="robots_disallow"
                                          placeholder="/private/
/search">{{ old('robots_disallow', $settings->robots_disallow ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">One path per line. Appended after the default disallows (<code>/admin</code>, <code>/login</code>, <code>/search</code>).</p>
                                @error('robots_disallow')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_xml_sitemap" value="0">
                                    <input type="checkbox" name="enable_xml_sitemap" value="1" {{ old('enable_xml_sitemap', $settings->enable_xml_sitemap ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Enable XML Sitemap</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Automatically generate and serve an XML sitemap at <code>/sitemap.xml</code>.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="site-code" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">Site Code &amp; Maintenance</h3>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="analytics_code">
                                    Analytics / Tracking Code
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; min-height: 110px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;"
                                          id="analytics_code"
                                          name="analytics_code"
                                          placeholder="&lt;script async src=&quot;https://www.googletagmanager.com/gtag/js?id=...&quot;&gt;&lt;/script&gt;">{{ old('analytics_code', $settings->analytics_code ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Pasted into the public site <code>&lt;head&gt;</code>. Only administrators can edit this.</p>
                                @error('analytics_code')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="custom_css">
                                    Custom CSS
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; min-height: 140px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;"
                                          id="custom_css"
                                          name="custom_css"
                                          placeholder=".site-title { letter-spacing: -0.02em; }">{{ old('custom_css', $settings->custom_css ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Appended after the active theme stylesheet.</p>
                                @error('custom_css')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="custom_js">
                                    Custom JavaScript
                                </label>
                                <textarea style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; min-height: 140px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;"
                                          id="custom_js"
                                          name="custom_js"
                                          placeholder="document.addEventListener('DOMContentLoaded', () => { /* ... */ });">{{ old('custom_js', $settings->custom_js ?? '') }}</textarea>
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Printed before the closing <code>&lt;/body&gt;</code> tag.</p>
                                @error('custom_js')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(245, 158, 11, 0.12); border-left: 4px solid var(--warning-color); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="maintenance_mode" value="0">
                                    <input type="checkbox" name="maintenance_mode" value="1" {{ old('maintenance_mode', $settings->maintenance_mode ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color); font-weight: 500;">Maintenance Mode</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Shows a 503 maintenance page to visitors. Signed-in administrators and editors can still browse the site and <code>/admin</code>.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="security" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">Security Settings</h3>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(220, 38, 38, 0.1); border-left: 4px solid var(--error-color); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_debug_mode" value="0">
                                    <input type="checkbox" name="enable_debug_mode" value="1" {{ old('enable_debug_mode', $settings->enable_debug_mode ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px; accent-color: var(--error-color);">
                                    <div>
                                        <span style="color: var(--text-color); font-weight: 500;">Enable Debug Mode</span>
                                        <p style="font-size: 0.875rem; color: var(--error-color); margin-top: 0.25rem;">⚠️ Only enable on development environments. Displays detailed error messages.</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="disable_file_editor" value="0">
                                    <input type="checkbox" name="disable_file_editor" value="1" {{ old('disable_file_editor', $settings->disable_file_editor ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Disable File Editor</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Disable the built-in theme and plugin file editor for security.</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);" for="max_upload_size">
                                    Maximum Upload Size (MB)
                                </label>
                                <input style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;" 
                                       id="max_upload_size" 
                                       type="number" 
                                       name="max_upload_size" 
                                       value="{{ old('max_upload_size', $settings->max_upload_size ?? 10) }}" 
                                       min="1" 
                                       max="1000">
                                <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.5rem;">Maximum file size for uploads in megabytes.</p>

                                @error('max_upload_size')
                                    <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                                @enderror
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_user_registration" value="0">
                                    <input type="checkbox" name="enable_user_registration" value="1" {{ old('enable_user_registration', $settings->enable_user_registration ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Enable User Registration</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Allow new users to register through the registration form.</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_welcome_email" value="0">
                                    <input type="checkbox" name="enable_welcome_email" value="1" {{ old('enable_welcome_email', $settings->enable_welcome_email ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Send Welcome E-mail</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Send a transactional welcome email to newly registered users.</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_social_login_google" value="0">
                                    <input type="checkbox" name="enable_social_login_google" value="1" {{ old('enable_social_login_google', $settings->enable_social_login_google ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Google Sign-In</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Allow users to sign in with a Google account (requires GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET).</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_social_login_github" value="0">
                                    <input type="checkbox" name="enable_social_login_github" value="1" {{ old('enable_social_login_github', $settings->enable_social_login_github ?? 0) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">GitHub Sign-In</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Allow users to sign in with a GitHub account (requires GITHUB_CLIENT_ID / GITHUB_CLIENT_SECRET).</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_chat_widget" value="0">
                                    <input type="checkbox" name="enable_chat_widget" value="1" {{ old('enable_chat_widget', $settings->enable_chat_widget ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Public Chat Widget</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Show the floating support chat button on the public site.</p>
                                    </div>
                                </label>
                            </div>

                            <div style="margin-bottom: 1rem; padding: 1rem; background-color: rgba(0,0,0,0.02); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="hidden" name="enable_booking_form" value="0">
                                    <input type="checkbox" name="enable_booking_form" value="1" {{ old('enable_booking_form', $settings->enable_booking_form ?? 1) ? 'checked' : '' }} style="margin-right: 0.75rem; width: 18px; height: 18px;">
                                    <div>
                                        <span style="color: var(--text-color);">Public Booking Form</span>
                                        <p style="font-size: 0.875rem; color: var(--text-color); margin-top: 0.25rem;">Show the public booking form at <code>/book</code> for appointment requests.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <a href="{{ route('dashboard') }}" style="color: var(--text-color); text-decoration: none;">Cancel</a>
                            <button type="submit" style="background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-size: 1rem; font-weight: 500; cursor: pointer;">
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ogImagePickerModal" tabindex="-1" aria-labelledby="ogImagePickerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ogImagePickerLabel">Choose an OG image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="search" id="og-image-picker-search" class="form-control mb-3" placeholder="Search images by name or alt text">
                    <div id="og-image-picker-grid" class="row g-3">
                        <div class="col-12 text-muted small">Loading images…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ url('/admin/media') }}" class="btn btn-link me-auto">Manage media</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // OG image picker
        const ogPickerModal = document.getElementById('ogImagePickerModal');
        const ogPickerGrid = document.getElementById('og-image-picker-grid');
        const ogPickerSearch = document.getElementById('og-image-picker-search');
        const ogImageInput = document.getElementById('og_image');
        let ogPickerLoaded = false;
        let ogPickerTimer = null;

        const renderOgPicker = (items) => {
            if (!ogPickerGrid) return;

            if (!items.length) {
                ogPickerGrid.innerHTML = '<div class="col-12 text-muted small">No images found. <a href="{{ url('/admin/media') }}">Upload one</a>.</div>';
                return;
            }

            ogPickerGrid.innerHTML = items.map((item) => `
                <div class="col-6 col-md-4">
                    <button type="button" class="btn p-0 border rounded w-100 og-image-pick"
                            data-url="${item.url.replace(/"/g, '&quot;')}"
                            data-path="${item.path.replace(/"/g, '&quot;')}"
                            data-alt="${(item.alt || '').replace(/"/g, '&quot;')}">
                        <img src="${item.url}" alt="${(item.alt || item.name).replace(/"/g, '&quot;')}"
                             class="img-fluid rounded-top" style="height:110px;width:100%;object-fit:cover;">
                        <span class="d-block small text-truncate px-2 py-1">${item.name}</span>
                    </button>
                </div>
            `).join('');
        };

        const loadOgPicker = (search = '') => {
            if (!ogPickerGrid) return;

            ogPickerGrid.innerHTML = '<div class="col-12 text-muted small">Loading images…</div>';

            fetch(`{{ route('admin.media.picker') }}?search=${encodeURIComponent(search)}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then((response) => response.ok ? response.json() : [])
                .then(renderOgPicker)
                .catch(() => renderOgPicker([]));
        };

        if (ogPickerModal) {
            ogPickerModal.addEventListener('show.bs.modal', () => {
                if (!ogPickerLoaded) {
                    ogPickerLoaded = true;
                    loadOgPicker();
                }
            });
        }

        if (ogPickerSearch) {
            ogPickerSearch.addEventListener('input', () => {
                clearTimeout(ogPickerTimer);
                ogPickerTimer = setTimeout(() => loadOgPicker(ogPickerSearch.value.trim()), 250);
            });
        }

        if (ogPickerGrid) {
            ogPickerGrid.addEventListener('click', (event) => {
                const button = event.target.closest('.og-image-pick');
                if (!button) return;

                if (ogImageInput) {
                    // Store the full URL — OG tags must be absolute.
                    ogImageInput.value = button.dataset.url || button.dataset.path || '';
                }

                if (window.bootstrap && ogPickerModal) {
                    bootstrap.Modal.getOrCreateInstance(ogPickerModal).hide();
                }
            });
        }

        // Custom form fields editor
        const cffList = document.getElementById('custom-form-fields-list');
        const cffJson = document.getElementById('custom_form_fields_json');
        const cffForm = document.querySelector('form[action="{{ route('admin.settings.update') }}"]');

        const cffRowTemplate = (name = '', label = '', type = 'text', placeholder = '', required = false) => `
            <div class="cff-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; align-items: end; background-color: rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 0.75rem;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Name</label>
                    <input type="text" class="cff-name form-control form-control-sm" value="${name.replace(/"/g, '&quot;')}" placeholder="field_name" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Label</label>
                    <input type="text" class="cff-label form-control form-control-sm" value="${label.replace(/"/g, '&quot;')}" placeholder="Field label">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Type</label>
                    <select class="cff-type form-select form-select-sm">
                        <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                        <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                        <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                    </select>
                </div>
                <button type="button" class="cff-remove btn btn-danger btn-sm" title="Remove field" style="padding: 0.4rem 0.7rem;">✕</button>
                <div style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; align-items: end;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Placeholder (optional)</label>
                        <input type="text" class="cff-placeholder form-control form-control-sm" value="${placeholder.replace(/"/g, '&quot;')}" placeholder="Placeholder text">
                    </div>
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: var(--text-color); padding-bottom: 0.35rem;">
                        <input type="checkbox" class="cff-required" value="1" ${required ? 'checked' : ''}> Required
                    </label>
                </div>
            </div>`;

        const serializeCff = () => {
            const fields = [];
            document.querySelectorAll('#custom-form-fields-list .cff-row').forEach((row) => {
                const name = row.querySelector('.cff-name').value.trim();
                if (!name) return;
                fields.push({
                    name: name,
                    label: row.querySelector('.cff-label').value.trim() || name,
                    type: row.querySelector('.cff-type').value,
                    placeholder: row.querySelector('.cff-placeholder').value.trim(),
                    required: row.querySelector('.cff-required').checked,
                });
            });
            if (cffJson) cffJson.value = JSON.stringify(fields);
        };

        if (cffList) {
            cffList.addEventListener('click', (event) => {
                const removeBtn = event.target.closest('.cff-remove');
                if (removeBtn) {
                    removeBtn.closest('.cff-row').remove();
                }
            });
        }

        const cffAdd = document.getElementById('cff-add');
        if (cffAdd && cffList) {
            cffAdd.addEventListener('click', () => {
                cffList.insertAdjacentHTML('beforeend', cffRowTemplate());
                const newRow = cffList.lastElementChild;
                const input = newRow.querySelector('.cff-name');
                if (input) input.focus();
            });
        }

        if (cffForm) {
            cffForm.addEventListener('submit', serializeCff);
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('.settings-nav-item').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Update active nav item
                    document.querySelectorAll('.settings-nav-item').forEach(item => {
                        item.style.backgroundColor = 'transparent';
                        item.style.color = 'var(--text-color)';
                    });
                    
                    this.style.backgroundColor = 'var(--primary-color)';
                    this.style.color = 'white';
                }
            });
        });
    </script>
    @endpush
@endsection
