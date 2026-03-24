/**
 * Chat UI — Modern chat interface with theme & accent color support.
 */

if (!document.getElementById('conversation-list')) {
    // Not on the chat page
} else {

const API = '/api/conversations';

let currentConversationId = null;
let isSending = false;

// ── DOM refs ──────────────────────────────────────────────
const body = document.body;
const conversationList = document.getElementById('conversation-list');
const messagesContainer = document.getElementById('messages');
const emptyState = document.getElementById('empty-state');
const chatTitle = document.getElementById('chat-title');
const chatStatus = document.getElementById('chat-status');
const loading = document.getElementById('loading');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');
const btnSend = document.getElementById('btn-send');
const btnNewChat = document.getElementById('btn-new-chat');
const btnDeleteChat = document.getElementById('btn-delete-chat');
const btnTheme = document.getElementById('btn-theme');
const iconSun = document.getElementById('icon-sun');
const iconMoon = document.getElementById('icon-moon');
const colorPicker = document.getElementById('color-picker');

// ── Theme & Accent ────────────────────────────────────────
function isDark() {
    return body.classList.contains('theme-dark');
}

function applyTheme(theme) {
    body.classList.remove('theme-dark', 'theme-light');
    body.classList.add(theme);
    iconSun.classList.toggle('hidden', theme === 'theme-dark');
    iconMoon.classList.toggle('hidden', theme === 'theme-light');
    localStorage.setItem('chat-theme', theme);
}

function applyAccent(accent) {
    body.className = body.className.replace(/accent-\w+/g, '');
    body.classList.add(`accent-${accent}`);
    localStorage.setItem('chat-accent', accent);
    updateColorPickerRing(accent);
}

function updateColorPickerRing(accent) {
    colorPicker.querySelectorAll('button').forEach(btn => {
        const isActive = btn.dataset.accent === accent;
        btn.classList.toggle('ring-white/60', isActive && isDark());
        btn.classList.toggle('ring-gray-800/60', isActive && !isDark());
        btn.classList.toggle('ring-transparent', !isActive);
        btn.classList.toggle('scale-110', isActive);
    });
}

// Init theme
const savedTheme = localStorage.getItem('chat-theme') || 'theme-dark';
applyTheme(savedTheme);

const savedAccent = localStorage.getItem('chat-accent') || 'indigo';
applyAccent(savedAccent);

btnTheme.addEventListener('click', () => {
    applyTheme(isDark() ? 'theme-light' : 'theme-dark');
    updateColorPickerRing(localStorage.getItem('chat-accent') || 'indigo');
});

colorPicker.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-accent]');
    if (btn) {
        applyAccent(btn.dataset.accent);
    }
});

// ── Textarea auto-resize ──────────────────────────────────
messageInput.addEventListener('input', () => {
    messageInput.style.height = 'auto';
    messageInput.style.height = Math.min(messageInput.scrollHeight, 140) + 'px';
});

// ── API helpers ───────────────────────────────────────────
async function fetchConversations() {
    const { data } = await axios.get(API);
    return data.data;
}

async function createConversation(title = null) {
    const { data } = await axios.post(API, title ? { title } : {});
    return data.data;
}

async function fetchConversation(id) {
    const { data } = await axios.get(`${API}/${id}`);
    return data.data;
}

async function deleteConversation(id) {
    await axios.delete(`${API}/${id}`);
}

async function sendMessage(conversationId, message) {
    const { data } = await axios.post(`${API}/${conversationId}/messages`, { message });
    return data.data;
}

// ── Render helpers ────────────────────────────────────────
function renderConversationList(conversations) {
    conversationList.innerHTML = '';

    if (conversations.length === 0) {
        conversationList.innerHTML = `<p class="px-2 py-8 text-center text-[11px]" style="color: var(--text-muted)">No conversations yet</p>`;
        return;
    }

    conversations.forEach((conv, i) => {
        const isActive = conv.id === currentConversationId;
        const el = document.createElement('button');
        el.className = `sidebar-item group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-[13px] transition-all duration-150 ${
            isActive
                ? 'bg-[var(--bg-active)] font-medium'
                : 'hover:bg-(--bg-hover)'
        }`;
        el.style.animationDelay = `${i * 30}ms`;
        el.style.color = isActive ? 'var(--text-primary)' : 'var(--text-secondary)';

        const iconColor = isActive ? 'var(--accent)' : 'var(--text-muted)';
        el.innerHTML = `
            <svg class="h-4 w-4 shrink-0 transition-colors" style="color: ${iconColor}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
            </svg>
            <span class="truncate">${escapeHtml(conv.title)}</span>
        `;
        el.addEventListener('click', () => selectConversation(conv.id));
        conversationList.appendChild(el);
    });
}

function renderMessages(messages) {
    const wrapper = messagesContainer.querySelector('.mx-auto');
    wrapper.innerHTML = '';

    if (!messages || messages.length === 0) {
        wrapper.innerHTML = `
            <div class="flex h-full min-h-[60vh] items-center justify-center">
                <p class="text-[13px]" style="color: var(--text-muted)">No messages yet. Send a message to start!</p>
            </div>`;
        return;
    }

    messages.forEach((msg, i) => {
        appendMessageBubble(msg.role, msg.content, i * 50);
    });

    scrollToBottom();
}

function appendMessageBubble(role, content, delay = 0) {
    const isUser = role === 'user';
    const wrapper = messagesContainer.querySelector('.mx-auto');

    const row = document.createElement('div');
    row.className = `flex gap-3 mb-5 msg-animate ${isUser ? 'flex-row-reverse' : ''}`;
    if (delay) {
        row.style.animationDelay = `${delay}ms`;
    }

    // Avatar
    const avatar = document.createElement('div');
    avatar.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition-colors duration-300';

    if (isUser) {
        avatar.style.background = 'var(--accent)';
        avatar.innerHTML = `<svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
        </svg>`;
    } else {
        avatar.style.background = 'var(--accent-dim)';
        avatar.innerHTML = `<svg class="h-4 w-4" style="color: var(--accent)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
        </svg>`;
    }

    // Bubble wrapper (for hover copy button)
    const bubbleWrap = document.createElement('div');
    bubbleWrap.className = `group/msg relative max-w-[75%] ${isUser ? 'flex flex-col items-end' : ''}`;

    const bubble = document.createElement('div');
    bubble.className = 'rounded-2xl px-4 py-3 text-[13px] leading-relaxed transition-colors duration-300';

    if (isUser) {
        bubble.style.background = 'var(--accent)';
        bubble.style.color = '#ffffff';
        bubble.classList.add('rounded-tr-md');
    } else {
        bubble.style.background = 'var(--bubble-ai)';
        bubble.style.borderColor = 'var(--bubble-ai-border)';
        bubble.style.color = 'var(--text-primary)';
        bubble.classList.add('rounded-tl-md', 'border');
    }

    bubble.innerHTML = formatMessage(content);

    // Copy button
    const copyBtn = document.createElement('button');
    copyBtn.className = `absolute ${isUser ? 'left-0 -translate-x-full pr-1.5' : 'right-0 translate-x-full pl-1.5'} top-1/2 -translate-y-1/2 opacity-0 group-hover/msg:opacity-100 transition-opacity duration-150`;
    copyBtn.title = 'Copy';
    copyBtn.innerHTML = `
        <span class="flex h-7 w-7 items-center justify-center rounded-lg border border-(--border) transition-all hover:bg-(--bg-hover)" style="color: var(--text-muted)">
            <svg class="h-3.5 w-3.5 copy-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/>
            </svg>
            <svg class="h-3.5 w-3.5 check-icon hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
        </span>`;
    copyBtn.addEventListener('click', () => copyMessage(copyBtn, content));

    bubbleWrap.appendChild(bubble);
    bubbleWrap.appendChild(copyBtn);

    row.appendChild(avatar);
    row.appendChild(bubbleWrap);
    wrapper.appendChild(row);
}

function formatMessage(content) {
    let html = escapeHtml(content);
    // Bold: **text**
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    // Inline code: `code`
    html = html.replace(/`([^`]+)`/g, '<code class="rounded px-1.5 py-0.5 text-[12px]" style="background: var(--bg-hover)">$1</code>');
    // Line breaks
    html = html.replace(/\n/g, '<br>');
    return html;
}

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function copyMessage(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        const copyIcon = btn.querySelector('.copy-icon');
        const checkIcon = btn.querySelector('.check-icon');
        copyIcon.classList.add('hidden');
        checkIcon.classList.remove('hidden');
        setTimeout(() => {
            copyIcon.classList.remove('hidden');
            checkIcon.classList.add('hidden');
        }, 1500);
    });
}

function setStatus(text) {
    chatStatus.textContent = text;
}

function setLoading(show) {
    isSending = show;
    loading.classList.toggle('hidden', !show);
    btnSend.disabled = show;
    messageInput.disabled = show;
    if (show) {
        setStatus('Thinking...');
    } else {
        setStatus('Ready');
        messageInput.focus();
        messageInput.style.height = 'auto';
    }
}

function enableInput() {
    messageInput.disabled = false;
    btnSend.disabled = false;
    messageInput.focus();
}

// ── Actions ───────────────────────────────────────────────
async function loadConversations() {
    try {
        const conversations = await fetchConversations();
        renderConversationList(conversations);
    } catch (err) {
        console.error('Failed to load conversations:', err);
    }
}

async function selectConversation(id) {
    currentConversationId = id;
    emptyState?.remove();

    try {
        const conv = await fetchConversation(id);
        chatTitle.textContent = conv.title;
        btnDeleteChat.classList.remove('hidden');
        renderMessages(conv.messages);
        enableInput();
        setStatus(`${conv.messages?.length || 0} messages`);
        await loadConversations();
    } catch (err) {
        console.error('Failed to load conversation:', err);
    }
}

async function handleNewChat() {
    try {
        const conv = await createConversation();
        await loadConversations();
        await selectConversation(conv.id);
    } catch (err) {
        console.error('Failed to create conversation:', err);
    }
}

async function handleDeleteChat() {
    if (!currentConversationId) return;
    if (!confirm('Delete this conversation?')) return;

    try {
        await deleteConversation(currentConversationId);
        currentConversationId = null;
        chatTitle.textContent = 'Select a conversation';
        btnDeleteChat.classList.add('hidden');
        setStatus('Ready');

        const wrapper = messagesContainer.querySelector('.mx-auto');
        wrapper.innerHTML = '';
        wrapper.appendChild(createEmptyState());

        messageInput.disabled = true;
        btnSend.disabled = true;
        await loadConversations();
    } catch (err) {
        console.error('Failed to delete conversation:', err);
    }
}

async function handleSendMessage(e) {
    e.preventDefault();
    const text = messageInput.value.trim();
    if (!text || !currentConversationId || isSending) return;

    messageInput.value = '';
    messageInput.style.height = 'auto';
    appendMessageBubble('user', text);
    scrollToBottom();
    setLoading(true);

    try {
        const result = await sendMessage(currentConversationId, text);
        appendMessageBubble('assistant', result.response);
        scrollToBottom();
    } catch (err) {
        const errorMsg = err.response?.data?.message || 'Failed to get response. Please try again.';
        appendMessageBubble('assistant', `Error: ${errorMsg}`);
        scrollToBottom();
        console.error('Failed to send message:', err);
    } finally {
        setLoading(false);
    }
}

function createEmptyState() {
    const div = document.createElement('div');
    div.id = 'empty-state';
    div.className = 'flex h-full min-h-[60vh] flex-col items-center justify-center';
    div.innerHTML = `
        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-(--accent-dim) mb-6 scale-animate transition-colors duration-300">
            <svg class="h-10 w-10" style="color: var(--accent)" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold mb-2" style="color: var(--text-primary)">Start a conversation</h2>
        <p class="text-[13px] max-w-sm text-center leading-relaxed" style="color: var(--text-muted)">
            Click <span class="font-medium" style="color: var(--accent)">New Chat</span> to begin. Ask about products, prices, or anything else.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-2">
            <button class="suggestion-chip rounded-full border border-(--border) px-3.5 py-1.5 text-[11px] font-medium transition-all hover:border-(--border-hover) hover:bg-(--bg-hover)" style="color: var(--text-secondary)">
                What products do you have?
            </button>
            <button class="suggestion-chip rounded-full border border-(--border) px-3.5 py-1.5 text-[11px] font-medium transition-all hover:border-(--border-hover) hover:bg-(--bg-hover)" style="color: var(--text-secondary)">
                Compare prices
            </button>
            <button class="suggestion-chip rounded-full border border-(--border) px-3.5 py-1.5 text-[11px] font-medium transition-all hover:border-(--border-hover) hover:bg-(--bg-hover)" style="color: var(--text-secondary)">
                What's in stock?
            </button>
        </div>
    `;
    return div;
}

// ── Suggestion chips ──────────────────────────────────────
document.addEventListener('click', (e) => {
    const chip = e.target.closest('.suggestion-chip');
    if (chip && currentConversationId && !isSending) {
        messageInput.value = chip.textContent.trim();
        messageForm.dispatchEvent(new Event('submit'));
    }
});

// ── Events ────────────────────────────────────────────────
btnNewChat.addEventListener('click', handleNewChat);
btnDeleteChat.addEventListener('click', handleDeleteChat);
messageForm.addEventListener('submit', handleSendMessage);

messageInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        messageForm.dispatchEvent(new Event('submit'));
    }
});

// ── Init ──────────────────────────────────────────────────
loadConversations();

} // end guard
