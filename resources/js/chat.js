/**
 * Chat UI — communicates with /api/conversations endpoints.
 */

// Only run on /chat page
if (!document.getElementById('conversation-list')) {
    // Not on the chat page, skip initialization
} else {

const API = '/api/conversations';

let currentConversationId = null;
let isSending = false;

// ── DOM refs ──────────────────────────────────────────────
const conversationList = document.getElementById('conversation-list');
const messagesContainer = document.getElementById('messages');
const emptyState = document.getElementById('empty-state');
const chatTitle = document.getElementById('chat-title');
const loading = document.getElementById('loading');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');
const btnSend = document.getElementById('btn-send');
const btnNewChat = document.getElementById('btn-new-chat');
const btnDeleteChat = document.getElementById('btn-delete-chat');

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
    const { data } = await axios.post(`${API}/${conversationId}/
        -`, { message });
    return data.data;
}

// ── Render helpers ────────────────────────────────────────
function renderConversationList(conversations) {
    conversationList.innerHTML = '';

    if (conversations.length === 0) {
        conversationList.innerHTML = '<p class="px-2 py-6 text-center text-xs text-gray-600">No conversations yet</p>';
        return;
    }

    conversations.forEach((conv) => {
        const isActive = conv.id === currentConversationId;
        const el = document.createElement('button');
        el.className = `group flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-left text-sm transition-all ${
            isActive
                ? 'bg-white/10 text-white'
                : 'text-gray-400 hover:bg-white/5 hover:text-gray-200'
        }`;
        el.innerHTML = `
            <svg class="h-4 w-4 shrink-0 ${isActive ? 'text-indigo-400' : 'text-gray-600 group-hover:text-gray-400'}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
            </svg>
            <span class="truncate">${escapeHtml(conv.title)}</span>
        `;
        el.addEventListener('click', () => selectConversation(conv.id));
        conversationList.appendChild(el);
    });
}

function renderMessages(messages) {
    messagesContainer.innerHTML = '';

    if (!messages || messages.length === 0) {
        messagesContainer.innerHTML = `
            <div class="flex h-full items-center justify-center">
                <p class="text-sm text-gray-600">No messages yet. Send a message to start!</p>
            </div>`;
        return;
    }

    messages.forEach((msg) => {
        appendMessageBubble(msg.role, msg.content);
    });

    scrollToBottom();
}

function appendMessageBubble(role, content) {
    const isUser = role === 'user';

    const wrapper = document.createElement('div');
    wrapper.className = `flex gap-3 mb-6 msg-animate ${isUser ? 'flex-row-reverse' : ''}`;

    // Avatar
    const avatar = document.createElement('div');
    if (isUser) {
        avatar.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-semibold text-white';
        avatar.textContent = 'You';
    } else {
        avatar.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600/20';
        avatar.innerHTML = '<svg class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>';
    }

    // Bubble
    const bubble = document.createElement('div');
    bubble.className = `max-w-[70%] rounded-2xl px-4 py-3 text-sm leading-relaxed ${
        isUser
            ? 'bg-indigo-600 text-white rounded-tr-md'
            : 'bg-[#1a1a2e] text-gray-200 rounded-tl-md border border-white/5'
    }`;
    bubble.innerHTML = escapeHtml(content).replace(/\n/g, '<br>');

    wrapper.appendChild(avatar);
    wrapper.appendChild(bubble);
    messagesContainer.appendChild(wrapper);
}

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function setLoading(show) {
    isSending = show;
    loading.classList.toggle('hidden', !show);
    btnSend.disabled = show;
    messageInput.disabled = show;
    if (!show) {
        messageInput.focus();
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
        messagesContainer.innerHTML = '';
        messagesContainer.appendChild(createEmptyState());
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
    div.className = 'flex h-full flex-col items-center justify-center';
    div.innerHTML = `
        <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-600/10 mb-6">
            <svg class="h-10 w-10 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-300 mb-2">Start a conversation</h2>
        <p class="text-sm text-gray-500 max-w-sm text-center">Click <span class="text-gray-400 font-medium">"New Chat"</span> to create a conversation, then send a message to get an AI response.</p>
    `;
    return div;
}

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
