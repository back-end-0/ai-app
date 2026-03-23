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
    const { data } = await axios.post(`${API}/${conversationId}/messages`, { message });
    return data.data;
}

// ── Render helpers ────────────────────────────────────────
function renderConversationList(conversations) {
    conversationList.innerHTML = '';

    if (conversations.length === 0) {
        conversationList.innerHTML = '<p class="px-2 py-4 text-center text-sm text-gray-500">No conversations yet</p>';
        return;
    }

    conversations.forEach((conv) => {
        const isActive = conv.id === currentConversationId;
        const el = document.createElement('button');
        el.className = `flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition ${
            isActive
                ? 'bg-gray-800 text-white'
                : 'text-gray-400 hover:bg-gray-800/50 hover:text-gray-200'
        }`;
        el.innerHTML = `
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="truncate">${escapeHtml(conv.title)}</span>
        `;
        el.addEventListener('click', () => selectConversation(conv.id));
        conversationList.appendChild(el);
    });
}

function renderMessages(messages) {
    // Clear everything except empty state
    messagesContainer.innerHTML = '';

    if (!messages || messages.length === 0) {
        messagesContainer.innerHTML = '<p class="py-8 text-center text-sm text-gray-500">No messages yet. Send a message to start!</p>';
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
    wrapper.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;

    const bubble = document.createElement('div');
    bubble.className = `max-w-[75%] rounded-2xl px-4 py-3 text-sm leading-relaxed ${
        isUser
            ? 'bg-indigo-600 text-white'
            : 'bg-gray-800 text-gray-200'
    }`;

    // Simple line-break handling
    bubble.innerHTML = escapeHtml(content).replace(/\n/g, '<br>');

    const label = document.createElement('div');
    label.className = `mb-1 text-xs ${isUser ? 'text-right text-indigo-300' : 'text-gray-500'}`;
    label.textContent = isUser ? 'You' : 'AI';

    const container = document.createElement('div');
    container.className = isUser ? 'text-right' : '';
    container.appendChild(label);
    container.appendChild(bubble);
    wrapper.appendChild(container);

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
        await loadConversations(); // Refresh active state
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
    div.className = 'flex h-full flex-col items-center justify-center text-gray-500';
    div.innerHTML = `
        <svg class="mb-4 h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-lg">Start a new conversation</p>
        <p class="mt-1 text-sm">Click "New Chat" to begin</p>
    `;
    return div;
}

// ── Events ────────────────────────────────────────────────
btnNewChat.addEventListener('click', handleNewChat);
btnDeleteChat.addEventListener('click', handleDeleteChat);
messageForm.addEventListener('submit', handleSendMessage);

// Allow Enter to send, Shift+Enter for newline (if using textarea later)
messageInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        messageForm.dispatchEvent(new Event('submit'));
    }
});

// ── Init ──────────────────────────────────────────────────
loadConversations();

} // end guard
