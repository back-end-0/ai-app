<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 text-gray-100">

<div id="app" class="flex h-full">

    {{-- Sidebar --}}
    <aside id="sidebar" class="flex w-72 flex-col border-r border-gray-800 bg-gray-900">
        {{-- New Chat Button --}}
        <div class="p-3">
            <button
                id="btn-new-chat"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-500"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Chat
            </button>
        </div>

        {{-- Conversation List --}}
        <nav id="conversation-list" class="flex-1 space-y-1 overflow-y-auto px-3 pb-3">
            {{-- Populated by JS --}}
        </nav>
    </aside>

    {{-- Main Chat Area --}}
    <main class="flex flex-1 flex-col">

        {{-- Header --}}
        <header id="chat-header" class="flex items-center justify-between border-b border-gray-800 px-6 py-3">
            <h1 id="chat-title" class="text-lg font-semibold text-gray-200">Select a conversation</h1>
            <button
                id="btn-delete-chat"
                class="hidden rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-red-400"
                title="Delete conversation"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </header>

        {{-- Messages --}}
        <div id="messages" class="flex-1 overflow-y-auto px-6 py-4">
            <div id="empty-state" class="flex h-full flex-col items-center justify-center text-gray-500">
                <svg class="mb-4 h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-lg">Start a new conversation</p>
                <p class="mt-1 text-sm">Click "New Chat" to begin</p>
            </div>
        </div>

        {{-- Loading indicator --}}
        <div id="loading" class="hidden px-6 py-2">
            <div class="flex items-center gap-2 text-sm text-gray-400">
                <div class="flex gap-1">
                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-500" style="animation-delay: 0ms"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-500" style="animation-delay: 150ms"></span>
                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-500" style="animation-delay: 300ms"></span>
                </div>
                AI is thinking...
            </div>
        </div>

        {{-- Input --}}
        <div id="input-area" class="border-t border-gray-800 p-4">
            <form id="message-form" class="flex gap-3">
                <input
                    id="message-input"
                    type="text"
                    placeholder="Type a message..."
                    autocomplete="off"
                    disabled
                    class="flex-1 rounded-lg border border-gray-700 bg-gray-800 px-4 py-2.5 text-sm text-gray-100 placeholder-gray-500 outline-none transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 disabled:opacity-50"
                />
                <button
                    id="btn-send"
                    type="submit"
                    disabled
                    class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-500 disabled:opacity-50 disabled:hover:bg-indigo-600"
                >
                    Send
                </button>
            </form>
        </div>

    </main>
</div>

</body>
</html>
