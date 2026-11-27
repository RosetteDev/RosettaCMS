<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * List conversations grouped by visitor session.
     */
    public function index()
    {
        $conversations = ChatMessage::selectRaw('session_id, MAX(id) as last_id, MAX(created_at) as last_at, COUNT(*) as total')
            ->groupBy('session_id')
            ->orderByDesc('last_at')
            ->get()
            ->map(function ($row) {
                $lastMessage = ChatMessage::find($row->last_id);
                $unread = ChatMessage::where('session_id', $row->session_id)
                    ->where('sender', 'visitor')
                    ->whereNull('read_at')
                    ->count();

                return [
                    'session_id' => $row->session_id,
                    'last_message' => $lastMessage,
                    'total' => $row->total,
                    'unread' => $unread,
                    'last_at' => $row->last_at,
                ];
            });

        $unreadTotal = $conversations->sum('unread');

        return view('admin.chat.index', [
            'conversations' => $conversations,
            'unreadTotal' => $unreadTotal,
        ]);
    }

    /**
     * Show a single conversation thread and mark visitor messages as read.
     */
    public function show(Request $request, string $session)
    {
        $messages = ChatMessage::where('session_id', $session)
            ->orderBy('id')
            ->get();

        abort_unless($messages->isNotEmpty(), 404);

        ChatMessage::where('session_id', $session)
            ->where('sender', 'visitor')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.chat.show', [
            'session' => $session,
            'messages' => $messages,
        ]);
    }

    /**
     * Reply to a conversation as the admin.
     */
    public function reply(Request $request, string $session)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        abort_unless(
            ChatMessage::where('session_id', $session)->exists(),
            404
        );

        ChatMessage::create([
            'site_id' => ChatMessage::where('session_id', $session)->value('site_id'),
            'session_id' => $session,
            'sender' => 'admin',
            'message' => $validated['message'],
        ]);

        return back()->with('status', 'Reply sent.');
    }
}
