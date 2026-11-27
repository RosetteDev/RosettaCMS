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
