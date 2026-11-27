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
