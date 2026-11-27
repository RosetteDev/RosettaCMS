@if($settings->enable_chat_widget ?? true)
<div id="chat-widget" class="chat-widget" aria-hidden="true">
    <button type="button" id="chat-widget-toggle" class="chat-widget-toggle" aria-expanded="false" aria-controls="chat-widget-panel" aria-label="Open support chat">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
        <span class="chat-widget-badge" id="chat-widget-badge" hidden>0</span>
    </button>

    <div id="chat-widget-panel" class="chat-widget-panel" role="dialog" aria-label="Support chat" hidden>
        <div class="chat-widget-header">
            <div>
                <strong>Support Chat</strong>
                <span class="chat-widget-status">Typically replies within a few minutes</span>
            </div>
            <button type="button" id="chat-widget-close" class="chat-widget-close" aria-label="Close support chat">&times;</button>
        </div>

        <div id="chat-widget-messages" class="chat-widget-messages" aria-live="polite">
            <div class="chat-widget-empty">Hi there! Send us a message and we'll get back to you.</div>
        </div>

        <form id="chat-widget-form" class="chat-widget-form">
            <input type="text" id="chat-widget-input" name="message" placeholder="Type a message…" maxlength="2000" autocomplete="off" required>
            <button type="submit" id="chat-widget-send" class="chat-widget-send" aria-label="Send message">
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
    </div>
</div>

<style>
    .chat-widget { position: fixed; bottom: 1.25rem; right: 1.25rem; z-index: 9999; font-family: inherit; }
    .chat-widget-toggle { width: 60px; height: 60px; border-radius: 50%; border: none; background-color: var(--primary-color, #3b82f6); color: #fff; font-size: 1.4rem; cursor: pointer; box-shadow: 0 6px 20px rgba(0,0,0,0.25); display: flex; align-items: center; justify-content: center; position: relative; }
    .chat-widget-toggle:hover { filter: brightness(1.1); }
    .chat-widget-badge { position: absolute; top: -4px; right: -4px; min-width: 22px; height: 22px; padding: 0 6px; border-radius: 999px; background-color: #ef4444; color: #fff; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .chat-widget-badge[hidden] { display: none; }
    .chat-widget-panel { position: absolute; bottom: 74px; right: 0; width: min(360px, calc(100vw - 2.5rem)); height: min(480px, calc(100vh - 8rem)); background: #fff; border-radius: 12px; box-shadow: 0 12px 40px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden; border: 1px solid rgba(0,0,0,0.08); }
    .chat-widget-panel[hidden] { display: none; }
    .chat-widget-header { display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background-color: var(--primary-color, #3b82f6); color: #fff; }
    .chat-widget-header strong { display: block; font-size: 0.95rem; }
    .chat-widget-status { display: block; font-size: 0.7rem; opacity: 0.85; margin-top: 0.15rem; }
    .chat-widget-close { background: transparent; border: none; color: #fff; font-size: 1.6rem; cursor: pointer; line-height: 1; padding: 0 0.25rem; }
    .chat-widget-messages { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 0.6rem; background-color: #f9fafb; }
    .chat-widget-empty { text-align: center; color: #6b7280; font-size: 0.85rem; margin: auto 0; padding: 0 0.5rem; }
    .chat-widget-msg { max-width: 85%; padding: 0.55rem 0.8rem; border-radius: 12px; font-size: 0.9rem; line-height: 1.45; white-space: pre-wrap; word-break: break-word; }
    .chat-widget-msg.visitor { align-self: flex-end; background-color: var(--primary-color, #3b82f6); color: #fff; border-bottom-right-radius: 4px; }
    .chat-widget-msg.admin { align-self: flex-start; background-color: #fff; color: #1f2937; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
    .chat-widget-form { display: flex; gap: 0.5rem; padding: 0.75rem; border-top: 1px solid #e5e7eb; background: #fff; }
    .chat-widget-form input { flex: 1; border: 1px solid #d1d5db; border-radius: 999px; padding: 0.6rem 0.9rem; font-size: 0.9rem; outline: none; }
    .chat-widget-form input:focus { border-color: var(--primary-color, #3b82f6); }
    .chat-widget-send { width: 42px; height: 42px; border-radius: 50%; border: none; background-color: var(--primary-color, #3b82f6); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .chat-widget-send:disabled { opacity: 0.5; cursor: not-allowed; }
    @media (max-width: 480px) {
        .chat-widget { bottom: 0.75rem; right: 0.75rem; }
        .chat-widget-panel { bottom: 66px; width: calc(100vw - 1.5rem); right: -0.25rem; }
    }
</style>

<script>
(function () {
    var root = document.getElementById('chat-widget');
    if (!root) return;

    var toggle = document.getElementById('chat-widget-toggle');
    var panel = document.getElementById('chat-widget-panel');
    var closeBtn = document.getElementById('chat-widget-close');
    var messagesEl = document.getElementById('chat-widget-messages');
    var form = document.getElementById('chat-widget-form');
    var input = document.getElementById('chat-widget-input');
    var sendBtn = document.getElementById('chat-widget-send');
    var badge = document.getElementById('chat-widget-badge');

    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';
    var lastId = 0;
    var pollTimer = null;
    var unread = 0;

    function appendMessage(msg) {
        var empty = messagesEl.querySelector('.chat-widget-empty');
        if (empty) empty.remove();

        var el = document.createElement('div');
        el.className = 'chat-widget-msg ' + (msg.sender === 'admin' ? 'admin' : 'visitor');
        el.textContent = msg.message;
        messagesEl.appendChild(el);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        if (msg.id > lastId) lastId = msg.id;
    }

    function updateBadge() {
        if (unread > 0) {
            badge.hidden = false;
            badge.textContent = unread > 99 ? '99+' : String(unread);
        } else {
            badge.hidden = true;
        }
    }

    function renderIncoming(messages) {
        messages.forEach(function (msg) {
            appendMessage(msg);
            if (panel.hidden) {
                unread++;
            }
        });
        updateBadge();
    }

    function poll() {
        fetch('{{ url('/chat/messages/poll') }}?since=' + lastId, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) {
                if (data && Array.isArray(data.messages)) {
                    renderIncoming(data.messages);
                }
            })
            .catch(function () {});
    }

    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(poll, 4000);
        poll();
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function openPanel() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        root.setAttribute('aria-hidden', 'false');
        unread = 0;
        updateBadge();
        startPolling();
        input.focus();
    }

    function closePanel() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        root.setAttribute('aria-hidden', 'true');
        stopPolling();
    }

    toggle.addEventListener('click', function () {
        if (panel.hidden) { openPanel(); } else { closePanel(); }
    });

    closeBtn.addEventListener('click', closePanel);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) closePanel();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var text = input.value.trim();
        if (!text) return;

        sendBtn.disabled = true;

        fetch('{{ url('/chat/messages') }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: text })
        })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) {
                if (data) {
                    appendMessage(data);
                    input.value = '';
                }
            })
            .catch(function () {})
            .finally(function () {
                sendBtn.disabled = false;
                input.focus();
            });
    });

    // Start polling immediately so badge shows while panel is closed.
    startPolling();
})();
</script>
@endif
