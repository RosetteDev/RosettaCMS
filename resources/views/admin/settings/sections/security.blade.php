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
