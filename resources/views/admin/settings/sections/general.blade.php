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
