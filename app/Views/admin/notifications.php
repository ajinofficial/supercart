<?php
$conversations = is_array($conversations ?? null) ? $conversations : [];
$theme = is_array($theme ?? null) ? $theme : [];
$websiteName = (string) ($theme['website_name'] ?? 'Ebolt');
$logoUrl = (string) ($theme['logo_url'] ?? '');
$themeColor = (string) ($theme['theme_color'] ?? '#0f6cad');
$endpointPrefix = trim((string) ($endpointPrefix ?? 'admin/notifications'), '/');
$backUrl = (string) ($backUrl ?? base_url('admin/dashboard'));
$currentSenderType = (string) ($currentSenderType ?? 'admin');
$pageTitle = (string) ($pageTitle ?? 'Notifications');
$isCustomerView = $currentSenderType === 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?> - <?= esc($websiteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --brand: <?= esc($themeColor) ?>;
            --brand-dark: color-mix(in srgb, var(--brand) 76%, #001b2b);
            --ink: #17212b;
            --muted: #667781;
            --line: #e1e7eb;
            --panel: #fff;
            --page: #e9edef;
            --chat-bg: #efeae2;
            --sent: #d9fdd3;
            --received: #fff;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            overflow: hidden;
            font-family: "Inter", sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, var(--brand) 0 118px, var(--page) 118px 100%);
        }
        button, input, textarea, select { font: inherit; }
        button { cursor: pointer; }
        .notify-shell {
            width: min(1500px, calc(100% - 34px));
            height: calc(100vh - 34px);
            margin: 17px auto;
            display: grid;
            grid-template-columns: minmax(310px, 390px) 1fr;
            overflow: hidden;
            border-radius: 14px;
            background: var(--panel);
            box-shadow: 0 14px 45px rgba(22, 38, 50, .22);
        }
        .conversation-panel {
            min-width: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--line);
            background: #fff;
        }
        .panel-head, .chat-head {
            min-height: 64px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f0f2f5;
        }
        .panel-head { justify-content: space-between; }
        .head-brand {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand-mark, .avatar {
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border-radius: 999px;
            color: #fff;
            font-weight: 800;
            background: var(--brand);
            overflow: hidden;
        }
        .brand-mark { width: 42px; height: 42px; }
        .brand-mark img, .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .head-copy { min-width: 0; }
        .head-copy strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .92rem; }
        .head-copy span { color: var(--muted); font-size: .74rem; }
        .icon-btn {
            width: 38px;
            height: 38px;
            display: inline-grid;
            place-items: center;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: #54656f;
        }
        .icon-btn:hover { background: #e2e6e9; }
        .icon-btn svg { width: 20px; height: 20px; }
        .search-wrap { padding: 8px 12px; border-bottom: 1px solid #f0f2f5; }
        .search-box {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 12px;
            border-radius: 9px;
            background: #f0f2f5;
        }
        .search-box svg { width: 17px; color: #667781; }
        .search-box input { width: 100%; border: 0; outline: 0; background: transparent; color: var(--ink); }
        .conversation-list { flex: 1; overflow-y: auto; }
        .conversation {
            width: 100%;
            display: grid;
            grid-template-columns: 50px minmax(0, 1fr);
            gap: 11px;
            padding: 11px 13px;
            border: 0;
            border-bottom: 1px solid #f0f2f5;
            background: #fff;
            text-align: left;
        }
        .conversation:hover, .conversation.active { background: #f0f2f5; }
        .avatar { width: 50px; height: 50px; background: linear-gradient(135deg, var(--brand), #5cc5a7); }
        .conversation-main { min-width: 0; align-self: center; }
        .conversation-top, .conversation-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .conversation-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; font-size: .94rem; }
        .conversation-time { flex: 0 0 auto; color: var(--muted); font-size: .7rem; }
        .conversation-preview { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--muted); font-size: .79rem; }
        .unread-badge {
            min-width: 20px; height: 20px; display: inline-grid; place-items: center;
            padding: 0 6px; border-radius: 999px; background: #25d366; color: #fff; font-size: .68rem; font-weight: 800;
        }
        .empty-list { padding: 40px 20px; text-align: center; color: var(--muted); font-size: .86rem; }
        .chat-panel { min-width: 0; display: flex; flex-direction: column; background: var(--chat-bg); }
        .chat-head { border-bottom: 1px solid #d8dfe3; }
        .chat-head .avatar { width: 42px; height: 42px; }
        .back-mobile { display: none; }
        .chat-head-spacer { flex: 1; }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 24px clamp(18px, 6vw, 80px);
            background-color: var(--chat-bg);
            background-image: radial-gradient(rgba(60, 76, 84, .055) 1px, transparent 1px);
            background-size: 18px 18px;
        }
        .empty-chat { height: 100%; display: grid; place-items: center; text-align: center; color: var(--muted); }
        .empty-chat svg { width: 58px; height: 58px; margin-bottom: 12px; color: var(--brand); }
        .message-row { display: flex; margin: 3px 0; }
        .message-row.sent { justify-content: flex-end; }
        .message-bubble {
            position: relative;
            max-width: min(72%, 720px);
            padding: 8px 10px 6px;
            border-radius: 8px;
            background: var(--received);
            box-shadow: 0 1px 1px rgba(11, 20, 26, .13);
        }
        .message-row.sent .message-bubble { background: var(--sent); }
        .message-sender { margin-bottom: 3px; color: var(--brand-dark); font-size: .72rem; font-weight: 700; }
        .message-text { white-space: pre-wrap; overflow-wrap: anywhere; font-size: .88rem; line-height: 1.42; }
        .message-meta { margin: 4px 0 0 16px; text-align: right; color: var(--muted); font-size: .63rem; }
        .chat-compose {
            min-height: 64px;
            display: flex;
            align-items: flex-end;
            gap: 9px;
            padding: 9px 12px;
            background: #f0f2f5;
        }
        .chat-compose textarea {
            flex: 1;
            min-height: 42px;
            max-height: 120px;
            resize: none;
            border: 0;
            outline: 0;
            border-radius: 9px;
            padding: 11px 13px;
            background: #fff;
        }
        .send-btn { background: var(--brand); color: #fff; }
        .send-btn:hover { background: var(--brand-dark); }
        .send-btn:disabled { opacity: .55; cursor: not-allowed; }
        .dialog-backdrop {
            position: fixed; inset: 0; z-index: 20; display: none; place-items: center;
            padding: 18px; background: rgba(11, 20, 26, .56);
        }
        .dialog-backdrop.open { display: grid; }
        .dialog {
            width: min(460px, 100%); border-radius: 14px; background: #fff; box-shadow: 0 18px 45px rgba(0,0,0,.3); overflow: hidden;
        }
        .dialog-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; background: var(--brand); color: #fff; }
        .dialog-head h2 { margin: 0; font-size: 1rem; }
        .dialog-body { display: grid; gap: 12px; padding: 16px; }
        .dialog-field { display: grid; gap: 5px; }
        .dialog-field label { font-size: .78rem; font-weight: 700; color: #425466; }
        .dialog-field input, .dialog-field select, .dialog-field textarea {
            width: 100%; border: 1px solid #d6dee3; border-radius: 9px; padding: 10px 11px; outline: 0;
        }
        .dialog-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 0 16px 16px; }
        .dialog-btn { border: 0; border-radius: 9px; padding: 9px 14px; font-weight: 700; }
        .dialog-btn.primary { background: var(--brand); color: #fff; }
        .dialog-btn.secondary { background: #e9edef; color: #3b4a54; }
        .toast {
            position: fixed; z-index: 30; left: 50%; bottom: 24px; transform: translateX(-50%) translateY(20px);
            padding: 10px 16px; border-radius: 9px; background: #17212b; color: #fff; font-size: .8rem;
            opacity: 0; pointer-events: none; transition: .2s ease;
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        @media (max-width: 760px) {
            body { background: var(--page); }
            .notify-shell { width: 100%; height: 100vh; margin: 0; border-radius: 0; grid-template-columns: 1fr; }
            .chat-panel { position: fixed; inset: 0; z-index: 10; transform: translateX(105%); transition: transform .22s ease; }
            .notify-shell.chat-open .chat-panel { transform: translateX(0); }
            .back-mobile { display: inline-grid; }
            .message-bubble { max-width: 88%; }
            .chat-messages { padding: 18px 12px; }
        }
    </style>
</head>
<body>
    <main class="notify-shell" id="notifyShell">
        <aside class="conversation-panel">
            <header class="panel-head">
                <div class="head-brand">
                    <div class="brand-mark">
                        <?php if ($logoUrl !== ''): ?>
                            <img src="<?= esc($logoUrl) ?>" alt="<?= esc($websiteName) ?>">
                        <?php else: ?>
                            <?= esc(mb_strtoupper(mb_substr($websiteName, 0, 1))) ?>
                        <?php endif; ?>
                    </div>
                    <div class="head-copy">
                        <strong><?= esc($pageTitle) ?></strong>
                        <span id="unreadSummary"><?= (int) $unreadCount ?> unread</span>
                    </div>
                </div>
                <div>
                    <button class="icon-btn" id="newConversationBtn" type="button" aria-label="New conversation"><i data-lucide="message-square-plus"></i></button>
                    <a class="icon-btn" href="<?= esc($backUrl) ?>" aria-label="Back"><i data-lucide="x"></i></a>
                </div>
            </header>
            <div class="search-wrap">
                <label class="search-box">
                    <i data-lucide="search"></i>
                    <input id="conversationSearch" type="search" placeholder="Search or start new chat">
                </label>
            </div>
            <div class="conversation-list" id="conversationList"></div>
        </aside>

        <section class="chat-panel">
            <header class="chat-head">
                <button class="icon-btn back-mobile" id="mobileBackBtn" type="button" aria-label="Back"><i data-lucide="arrow-left"></i></button>
                <div class="avatar" id="chatAvatar">N</div>
                <div class="head-copy">
                    <strong id="chatTitle">Select a notification</strong>
                    <span id="chatSubtitle">Choose a conversation to view messages</span>
                </div>
                <div class="chat-head-spacer"></div>
                <button class="icon-btn" id="refreshChatBtn" type="button" aria-label="Refresh"><i data-lucide="refresh-cw"></i></button>
            </header>
            <div class="chat-messages" id="chatMessages">
                <div class="empty-chat"><div><i data-lucide="message-circle"></i><h2>Notification Center</h2><p>Select a conversation to read and reply.</p></div></div>
            </div>
            <form class="chat-compose" id="messageForm">
                <textarea id="messageInput" rows="1" maxlength="2000" placeholder="Type a message" disabled></textarea>
                <button class="icon-btn send-btn" id="sendMessageBtn" type="submit" disabled aria-label="Send"><i data-lucide="send"></i></button>
            </form>
        </section>
    </main>

    <div class="dialog-backdrop" id="newConversationDialog">
        <form class="dialog" id="newConversationForm">
            <div class="dialog-head"><h2>New Conversation</h2><button class="icon-btn" id="closeDialogBtn" type="button" aria-label="Close"><i data-lucide="x"></i></button></div>
            <div class="dialog-body">
                <?php if (!$isCustomerView): ?>
                <div class="dialog-field"><label for="newParticipant">Participant</label><input id="newParticipant" name="participant" required maxlength="120" placeholder="Customer or staff name"></div>
                <?php endif; ?>
                <div class="dialog-field"><label for="newTitle">Conversation title</label><input id="newTitle" name="title" maxlength="120" placeholder="Optional"></div>
                <?php if (!$isCustomerView): ?>
                <div class="dialog-field"><label for="newType">Type</label><select id="newType" name="type"><option value="customer">Customer</option><option value="staff">Staff</option><option value="system">System</option></select></div>
                <?php endif; ?>
                <div class="dialog-field"><label for="newMessage">First message</label><textarea id="newMessage" name="message" rows="3" maxlength="2000" placeholder="<?= $isCustomerView ? 'How can we help?' : 'Optional message' ?>"<?= $isCustomerView ? ' required' : '' ?>></textarea></div>
            </div>
            <div class="dialog-actions"><button class="dialog-btn secondary" id="cancelDialogBtn" type="button">Cancel</button><button class="dialog-btn primary" type="submit">Create</button></div>
        </form>
    </div>
    <div class="toast" id="toast"></div>

    <script>
        (function () {
            const initialConversations = <?= json_encode($conversations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const urls = {
                conversations: <?= json_encode(base_url($endpointPrefix . '/conversations')) ?>,
                messages: <?= json_encode(base_url($endpointPrefix . '/messages')) ?>,
                create: <?= json_encode(base_url($endpointPrefix . '/create')) ?>,
                send: <?= json_encode(base_url($endpointPrefix . '/send')) ?>,
                read: <?= json_encode(base_url($endpointPrefix . '/read')) ?>
            };
            const currentSenderType = <?= json_encode($currentSenderType) ?>;
            const shell = document.getElementById('notifyShell');
            const list = document.getElementById('conversationList');
            const search = document.getElementById('conversationSearch');
            const messages = document.getElementById('chatMessages');
            const title = document.getElementById('chatTitle');
            const subtitle = document.getElementById('chatSubtitle');
            const avatar = document.getElementById('chatAvatar');
            const unreadSummary = document.getElementById('unreadSummary');
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            const sendButton = document.getElementById('sendMessageBtn');
            const dialog = document.getElementById('newConversationDialog');
            const newForm = document.getElementById('newConversationForm');
            const toast = document.getElementById('toast');
            let conversations = initialConversations;
            let activeConversation = null;
            let lastMessageId = 0;
            let polling = null;

            function escapeHtml(value) {
                const node = document.createElement('div');
                node.textContent = String(value || '');
                return node.innerHTML;
            }
            function request(url, options) {
                return fetch(url, Object.assign({ headers: { 'X-Requested-With': 'XMLHttpRequest' } }, options || {}))
                    .then(function (response) {
                        return response.json().then(function (payload) {
                            if (!response.ok || !payload.status) throw new Error(payload.message || 'Request failed.');
                            return payload;
                        });
                    });
            }
            function showToast(text) {
                toast.textContent = text;
                toast.classList.add('show');
                setTimeout(function () { toast.classList.remove('show'); }, 2300);
            }
            function updateUnread(count) {
                unreadSummary.textContent = String(Number(count || 0)) + ' unread';
            }
            function renderConversations() {
                const term = String(search.value || '').trim().toLowerCase();
                const filtered = conversations.filter(function (item) {
                    return !term || (item.title + ' ' + item.participant + ' ' + item.last_message).toLowerCase().includes(term);
                });
                if (!filtered.length) {
                    list.innerHTML = '<div class="empty-list">No conversations found.</div>';
                    return;
                }
                list.innerHTML = filtered.map(function (item) {
                    const active = activeConversation && Number(activeConversation.id) === Number(item.id);
                    return '<button class="conversation' + (active ? ' active' : '') + '" data-id="' + item.id + '">' +
                        '<span class="avatar">' + escapeHtml(item.initials) + '</span>' +
                        '<span class="conversation-main">' +
                            '<span class="conversation-top"><span class="conversation-name">' + escapeHtml(item.title) + '</span><span class="conversation-time">' + escapeHtml(item.time_label) + '</span></span>' +
                            '<span class="conversation-bottom"><span class="conversation-preview">' + escapeHtml(item.last_message) + '</span>' +
                            (Number(item.unread_count) > 0 ? '<span class="unread-badge">' + Number(item.unread_count) + '</span>' : '') +
                            '</span>' +
                        '</span>' +
                    '</button>';
                }).join('');
            }
            function renderMessage(item) {
                const sent = item.sender_type === currentSenderType;
                return '<div class="message-row ' + (sent ? 'sent' : 'received') + '" data-message-id="' + item.id + '">' +
                    '<div class="message-bubble">' +
                        (!sent ? '<div class="message-sender">' + escapeHtml(item.sender_name) + '</div>' : '') +
                        '<div class="message-text">' + escapeHtml(item.message) + '</div>' +
                        '<div class="message-meta">' + escapeHtml(item.time_label) + (sent ? ' &#10003;&#10003;' : '') + '</div>' +
                    '</div>' +
                '</div>';
            }
            function scrollToBottom() { messages.scrollTop = messages.scrollHeight; }
            function openConversation(id, incremental) {
                const after = incremental ? lastMessageId : 0;
                request(urls.messages + '/' + id + '?after_id=' + after)
                    .then(function (payload) {
                        activeConversation = payload.conversation;
                        title.textContent = activeConversation.title;
                        subtitle.textContent = activeConversation.type + ' - ' + activeConversation.participant;
                        avatar.textContent = activeConversation.initials;
                        messageInput.disabled = false;
                        sendButton.disabled = false;
                        if (!incremental) messages.innerHTML = '';
                        if (payload.messages.length) {
                            messages.insertAdjacentHTML('beforeend', payload.messages.map(renderMessage).join(''));
                            lastMessageId = Number(payload.messages[payload.messages.length - 1].id || lastMessageId);
                            scrollToBottom();
                        } else if (!incremental) {
                            messages.innerHTML = '<div class="empty-chat"><div><i data-lucide="message-circle"></i><p>No messages yet. Start the conversation.</p></div></div>';
                            lucide.createIcons();
                        }
                        shell.classList.add('chat-open');
                        renderConversations();
                        markRead(id);
                    })
                    .catch(function (error) { showToast(error.message); });
            }
            function markRead(id) {
                request(urls.read + '/' + id, { method: 'POST' })
                    .then(function (payload) {
                        conversations = conversations.map(function (item) {
                            return Number(item.id) === Number(id) ? Object.assign({}, item, { unread_count: 0 }) : item;
                        });
                        updateUnread(payload.unread_count);
                        renderConversations();
                    }).catch(function () {});
            }
            function refreshConversations() {
                request(urls.conversations)
                    .then(function (payload) {
                        conversations = payload.conversations || [];
                        updateUnread(payload.unread_count);
                        renderConversations();
                    }).catch(function () {});
            }
            function startPolling() {
                clearInterval(polling);
                polling = setInterval(function () {
                    refreshConversations();
                    if (activeConversation) openConversation(activeConversation.id, true);
                }, 8000);
            }
            list.addEventListener('click', function (event) {
                const item = event.target.closest('.conversation');
                if (!item) return;
                lastMessageId = 0;
                openConversation(Number(item.dataset.id), false);
            });
            search.addEventListener('input', renderConversations);
            document.getElementById('mobileBackBtn').addEventListener('click', function () { shell.classList.remove('chat-open'); });
            document.getElementById('refreshChatBtn').addEventListener('click', function () {
                refreshConversations();
                if (activeConversation) openConversation(activeConversation.id, false);
            });
            messageInput.addEventListener('input', function () {
                this.style.height = '42px';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
            messageInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    messageForm.requestSubmit();
                }
            });
            messageForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const value = messageInput.value.trim();
                if (!activeConversation || !value) return;
                sendButton.disabled = true;
                const body = new FormData();
                body.append('message', value);
                request(urls.send + '/' + activeConversation.id, { method: 'POST', body: body })
                    .then(function (payload) {
                        const empty = messages.querySelector('.empty-chat');
                        if (empty) messages.innerHTML = '';
                        messages.insertAdjacentHTML('beforeend', renderMessage(payload.message));
                        lastMessageId = Number(payload.message.id || lastMessageId);
                        messageInput.value = '';
                        messageInput.style.height = '42px';
                        scrollToBottom();
                        refreshConversations();
                    })
                    .catch(function (error) { showToast(error.message); })
                    .finally(function () { sendButton.disabled = false; messageInput.focus(); });
            });
            function closeDialog() { dialog.classList.remove('open'); newForm.reset(); }
            document.getElementById('newConversationBtn').addEventListener('click', function () {
                dialog.classList.add('open');
                const firstInput = dialog.querySelector('input, textarea');
                if (firstInput) firstInput.focus();
            });
            document.getElementById('closeDialogBtn').addEventListener('click', closeDialog);
            document.getElementById('cancelDialogBtn').addEventListener('click', closeDialog);
            dialog.addEventListener('click', function (event) { if (event.target === dialog) closeDialog(); });
            newForm.addEventListener('submit', function (event) {
                event.preventDefault();
                const body = new FormData(newForm);
                request(urls.create, { method: 'POST', body: body })
                    .then(function (payload) {
                        closeDialog();
                        refreshConversations();
                        lastMessageId = 0;
                        openConversation(payload.conversation.id, false);
                    })
                    .catch(function (error) { showToast(error.message); });
            });

            renderConversations();
            updateUnread(<?= (int) $unreadCount ?>);
            lucide.createIcons();
            startPolling();
            if (conversations.length && window.innerWidth > 760) openConversation(conversations[0].id, false);
        })();
    </script>
</body>
</html>
