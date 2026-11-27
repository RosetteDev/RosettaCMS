<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Role;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::create([
            'name' => 'Chat Test Site',
            'slug' => 'chat-test-site',
            'domain' => 'chat.test',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin',
            'permissions' => ['dashboard' => true, 'posts' => true],
        ]);

        $this->admin = User::factory()->create([
            'email' => 'chat-admin@test.dev',
            'password' => bcrypt('correct-horse'),
        ]);
        $this->admin->roles()->attach($adminRole);
    }

    protected function createVisitorMessage(string $sessionId, string $message = 'Hello from visitor', ?string $siteId = null): ChatMessage
    {
        return ChatMessage::create([
            'site_id' => $siteId ?? $this->site->id,
            'session_id' => $sessionId,
            'sender' => 'visitor',
            'message' => $message,
        ]);
    }

    public function test_public_chat_message_endpoint_stores_visitor_message(): void
    {
        $response = $this->postJson('/chat/messages', ['message' => 'Hello from visitor']);

        $response->assertOk();
        $response->assertJson(['sender' => 'visitor', 'message' => 'Hello from visitor']);
        $this->assertDatabaseHas('chat_messages', ['sender' => 'visitor', 'message' => 'Hello from visitor']);
    }

    public function test_public_chat_message_requires_a_message(): void
    {
        $response = $this->postJson('/chat/messages', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['message']);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_public_chat_message_404s_when_widget_disabled(): void
    {
        $this->site->settings()->updateOrCreate(
            ['site_id' => $this->site->id],
            ['enable_chat_widget' => false]
        );

        $response = $this->postJson('/chat/messages', ['message' => 'Hi']);

        $response->assertNotFound();
    }

    public function test_public_chat_poll_returns_only_admin_replies_after_since(): void
    {
        $session = 'session-abc-123';
        $visitor = $this->createVisitorMessage($session, 'Visitor question');
        $admin = ChatMessage::create([
            'site_id' => $this->site->id,
            'session_id' => $session,
            'sender' => 'admin',
            'message' => 'Admin reply',
        ]);

        $response = $this->withSession(['chat_session_id' => $session])->getJson('/chat/messages/poll?since=0');

        $response->assertOk();
        $response->assertJsonCount(1, 'messages');
        $response->assertJson(['messages' => [['id' => $admin->id, 'sender' => 'admin', 'message' => 'Admin reply']]]);

        $response = $this->withSession(['chat_session_id' => $session])->getJson('/chat/messages/poll?since=' . $admin->id);

        $response->assertOk();
        $response->assertJsonCount(0, 'messages');
    }

    public function test_widget_renders_on_public_pages_when_enabled(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('chat-widget-toggle', false);
    }

    public function test_widget_not_rendered_when_disabled(): void
    {
        $this->site->settings()->updateOrCreate(
            ['site_id' => $this->site->id],
            ['enable_chat_widget' => false]
        );

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('chat-widget-toggle', false);
    }

    public function test_admin_chat_index_lists_conversations_with_unread_count(): void
    {
        $this->createVisitorMessage('session-aaa', 'First visitor message');
        $this->createVisitorMessage('session-aaa', 'Second visitor message');

        $response = $this->actingAs($this->admin)->get('/admin/chat');

        $response->assertOk();
        $response->assertSee('Chat Conversations');
        $response->assertSee('session-aaa');
        $response->assertSee('2 unread');
    }

    public function test_admin_chat_show_marks_visitor_messages_read(): void
    {
        $message = $this->createVisitorMessage('session-bbb', 'Please help');

        $response = $this->actingAs($this->admin)->get('/admin/chat/session-bbb');

        $response->assertOk();
        $response->assertSee('Please help');

        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_admin_chat_show_404s_for_unknown_session(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/chat/nonexistent-session');

        $response->assertNotFound();
    }

    public function test_admin_chat_reply_creates_admin_message(): void
    {
        $this->createVisitorMessage('session-ccc', 'Help please');

        $response = $this->actingAs($this->admin)->post('/admin/chat/session-ccc/reply', [
            'message' => 'Sure, what do you need?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', [
            'session_id' => 'session-ccc',
            'sender' => 'admin',
            'message' => 'Sure, what do you need?',
        ]);
    }

    public function test_admin_chat_reply_rejects_empty_message(): void
    {
        $this->createVisitorMessage('session-ddd', 'Help please');

        $response = $this->actingAs($this->admin)->post('/admin/chat/session-ddd/reply', [
            'message' => '',
        ]);

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseMissing('chat_messages', [
            'session_id' => 'session-ddd',
            'sender' => 'admin',
        ]);
    }

    public function test_admin_chat_reply_404s_for_unknown_session(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/chat/nonexistent-session/reply', [
            'message' => 'Reply',
        ]);

        $response->assertNotFound();
    }
}
