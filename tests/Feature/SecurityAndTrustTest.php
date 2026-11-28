<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Site;
use App\Models\SiteIpRule;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityAndTrustTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Security Test Site',
            'slug' => 'security-test-site',
            'domain' => 'security.test',
        ]);

        $this->user = User::factory()->create([
            'email' => 'security-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Test admin',
            'permissions' => [],
        ]);
        $this->user->roles()->attach($adminRole);
    }

    protected function adminUser(): User
    {
        return $this->user;
    }

    protected function currentCode(string $secret): string
    {
        return \OTPHP\TOTP::create($secret)->at(time());
    }

    // --- Two-factor authentication -------------------------------------

    public function test_two_factor_setup_enable_then_confirm_enables_it(): void
    {
        $response = $this->actingAs($this->user)->post('/profile/2fa/enable');

        $response->assertRedirect(route('profile'));
        $secret = session('two_factor_setup_secret');
        $this->assertNotEmpty($secret);

        $code = $this->currentCode($secret);
        $response = $this->actingAs($this->user)->post('/profile/2fa/confirm', ['code' => $code]);

        $response->assertRedirect(route('profile'));

        $this->user->refresh();
        $this->assertTrue($this->user->hasTwoFactor());
        $this->assertSame($secret, $this->user->two_factor_secret);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.two_factor_enabled',
        ]);
    }

    public function test_two_factor_confirm_rejects_invalid_code_and_keeps_it_pending(): void
    {
        $this->actingAs($this->user)->post('/profile/2fa/enable');
        $secret = session('two_factor_setup_secret');
        $this->assertNotEmpty($secret);

        $response = $this->actingAs($this->user)->post('/profile/2fa/confirm', ['code' => '000000']);

        $response->assertRedirect(route('profile'));
        $this->user->refresh();
        $this->assertFalse($this->user->hasTwoFactor());
        $this->assertNotNull(session('two_factor_setup_secret'));
    }

    public function test_login_with_two_factor_enabled_redirects_to_challenge_and_verifies(): void
    {
        $secret = (new TwoFactorService())->generateSecret();
        $this->user->two_factor_secret = $secret;
        $this->user->two_factor_enabled_at = now();
        $this->user->save();

        $response = $this->post('/login', [
            'email' => 'security-admin@test.dev',
            'password' => 'correct-horse',
        ]);

        $response->assertRedirect(route('login.2fa'));

        // The pending user id lives in the session.
        $this->assertSame($this->user->id, session('two_factor_user_id'));

        $code = $this->currentCode($secret);
        $response = $this->post('/login/2fa', ['code' => $code]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.login',
        ]);
    }

    public function test_two_factor_challenge_rejects_a_wrong_code(): void
    {
        $secret = (new TwoFactorService())->generateSecret();
        $this->user->two_factor_secret = $secret;
        $this->user->two_factor_enabled_at = now();
        $this->user->save();

        $this->post('/login', [
            'email' => 'security-admin@test.dev',
            'password' => 'correct-horse',
        ]);

        $response = $this->post('/login/2fa', ['code' => '000000']);

        $response->assertRedirect();
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.two_factor_failed',
        ]);
    }

    public function test_disabling_two_factor_requires_password_and_clears_it(): void
    {
        $secret = (new TwoFactorService())->generateSecret();
        $this->user->two_factor_secret = $secret;
        $this->user->two_factor_enabled_at = now();
        $this->user->save();

        // Wrong password → refused.
        $response = $this->actingAs($this->user)->post('/profile/2fa/disable', ['password' => 'wrong-password']);
        $response->assertSessionHasErrors('password');
        $this->user->refresh();
        $this->assertTrue($this->user->hasTwoFactor());

        // Correct password → disabled.
        $response = $this->actingAs($this->user)->post('/profile/2fa/disable', ['password' => 'correct-horse']);
        $response->assertRedirect(route('profile'));

        $this->user->refresh();
        $this->assertFalse($this->user->hasTwoFactor());

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.two_factor_disabled',
        ]);
    }

    public function test_profile_page_renders_pending_setup_state_with_secret_and_uri(): void
    {
        $this->actingAs($this->user)->post('/profile/2fa/enable');

        $secret = session('two_factor_setup_secret');
        $this->assertNotEmpty($secret);

        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Two-Factor Authentication');
        $response->assertSee($secret);
        $response->assertSee('otpauth://totp/');
    }

    // --- Brute-force lockout -------------------------------------------

    public function test_five_failed_logins_lock_the_account_and_reject_even_correct_password(): void
    {
        $email = 'security-admin@test.dev';

        for ($i = 0; $i < 5; $i++) {
            $this->user->registerFailedLogin();
        }

        $this->user->refresh();
        $this->assertTrue($this->user->isLocked());

        // Correct password is rejected while locked, with the generic message.
        $response = $this->post('/login', ['email' => $email, 'password' => 'correct-horse']);
        $response->assertSessionHasErrors('email');
        $response->assertSessionHasErrors('email', 'The provided credentials do not match our records.');
        $this->assertGuest();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.locked',
        ]);
    }

    public function test_successful_login_resets_the_failed_login_counter(): void
    {
        $email = 'security-admin@test.dev';

        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        }

        $this->user->refresh();
        $this->assertSame(4, $this->user->failed_login_attempts);

        \Illuminate\Support\Facades\RateLimiter::clear('127.0.0.1');

        $this->post('/login', ['email' => $email, 'password' => 'correct-horse']);

        $this->user->refresh();
        $this->assertSame(0, $this->user->failed_login_attempts);
        $this->assertNull($this->user->locked_until);
    }

    public function test_lockout_expires_and_login_works_again(): void
    {
        $email = 'security-admin@test.dev';

        // Simulate an account that was locked moments ago.
        $this->user->registerFailedLogin();
        $this->user->registerFailedLogin();
        $this->user->registerFailedLogin();
        $this->user->registerFailedLogin();
        $this->user->registerFailedLogin();
        $this->user->locked_until = now()->subMinute();
        $this->user->save();

        $response = $this->post('/login', ['email' => $email, 'password' => 'correct-horse']);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    // --- Activity log ---------------------------------------------------

    public function test_login_and_logout_are_recorded_in_the_activity_log(): void
    {
        $email = 'security-admin@test.dev';

        $this->post('/login', ['email' => $email, 'password' => 'correct-horse']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.login',
        ]);

        $this->actingAs($this->user)->post('/logout');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'auth.logout',
        ]);
    }

    public function test_mutating_admin_requests_are_logged(): void
    {
        $this->actingAs($this->user)
            ->post('/admin/categories', ['name' => 'Logged Category', 'slug' => 'logged-category']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->user->id,
            'action' => 'admin.categories.store',
        ]);
    }

    public function test_activity_page_lists_entries_and_filters_by_user(): void
    {
        ActivityLog::record('auth.login', 'test login', 'user', $this->user->id, $this->user->id);

        $response = $this->actingAs($this->user)->get('/admin/activity');
        $response->assertOk();
        $response->assertSee('auth.login');

        $response = $this->actingAs($this->user)->get('/admin/activity?user_id=' . $this->user->id);
        $response->assertOk();
        $response->assertSee('auth.login');

        $response = $this->actingAs($this->user)->get('/admin/activity?user_id=99999');
        $response->assertOk();
        $response->assertDontSee('auth.login');
    }

    // --- IP rules -------------------------------------------------------

    public function test_block_rule_denies_matching_ip_and_allows_others(): void
    {
        SiteIpRule::create([
            'site_id' => $this->site->id,
            'type' => 'block',
            'cidr' => '203.0.113.0/24',
            'label' => 'bad actor',
        ]);

        // Blocked IP → 403.
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])->get('/');
        $response->assertStatus(403);

        // Other IP → allowed (200 or redirect, never 403).
        $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.5'])->get('/');
        $response->assertStatus(200);
    }

    public function test_allow_rules_only_apply_when_present_and_deny_non_matching_ips(): void
    {
        SiteIpRule::create([
            'site_id' => $this->site->id,
            'type' => 'allow',
            'cidr' => '198.51.100.0/24',
        ]);

        // Matching IP → allowed.
        $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.5'])->get('/');
        $response->assertStatus(200);

        // Non-matching IP → 403.
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])->get('/');
        $response->assertStatus(403);
    }

    public function test_login_routes_stay_reachable_even_when_ip_rules_block_the_ip(): void
    {
        SiteIpRule::create([
            'site_id' => $this->site->id,
            'type' => 'block',
            'cidr' => '203.0.113.0/24',
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.5'])->get('/login');
        $response->assertOk();
    }

    public function test_ip_rules_admin_page_creates_and_deletes_rules(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/ip-rules', [
            'type' => 'block',
            'cidr' => '203.0.113.0/24',
            'label' => 'test rule',
        ]);

        $response->assertRedirect(route('admin.ip-rules.index'));

        $rule = SiteIpRule::first();
        $this->assertNotNull($rule);
        $this->assertSame('203.0.113.0/24', $rule->cidr);
        $this->assertSame('test rule', $rule->label);

        $response = $this->actingAs($this->user)->delete('/admin/ip-rules/' . $rule->id);
        $response->assertRedirect(route('admin.ip-rules.index'));

        $this->assertSame(0, SiteIpRule::count());
    }

    public function test_ip_rules_reject_invalid_cidr(): void
    {
        $response = $this->actingAs($this->user)->post('/admin/ip-rules', [
            'type' => 'block',
            'cidr' => 'not-an-ip',
        ]);

        $response->assertSessionHasErrors('cidr');
        $this->assertSame(0, SiteIpRule::count());
    }

    // --- Site health ------------------------------------------------------

    public function test_health_page_renders_checks_for_admin(): void
    {
        $response = $this->actingAs($this->user)->get('/admin/health');

        $response->assertOk();
        $response->assertSee('Database connection');
        $response->assertSee('Security headers');
        $response->assertSee('PHP version');
    }

    public function test_health_page_is_forbidden_to_non_admins(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/health');

        $this->assertContains($response->status(), [403, 302]);
    }

    // --- POST throttles (audit #24) ---------------------------------------

    public function test_login_post_is_throttled_after_ten_attempts(): void
    {
        $email = 'security-admin@test.dev';

        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        }

        $response = $this->post('/login', ['email' => $email, 'password' => 'wrong-password']);
        $response->assertStatus(429);
    }

    public function test_ip_block_response_matches_other_blocked_paths(): void
    {
        // A blocked IP gets the same plain-text status/body as a role denial
        // (403, "Unauthorized action."), not a WAF-specific wording.
        $this->actingAs($this->user)->post('/admin/ip-rules', [
            'type' => 'block',
            'cidr' => '127.0.0.0/8',
            'label' => 'block test ip',
        ]);

        $response = $this->get('/');

        $response->assertStatus(403);
        $response->assertSee('Unauthorized action.');
        $response->assertDontSee('Access denied');
    }
}
