<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #4b5563; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .msg-animate { animation: fadeIn 0.3s ease-out; }
        @keyframes pulse-dot { 0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; } 40% { transform: scale(1); opacity: 1; } }
        .dot-1 { animation: pulse-dot 1.4s infinite ease-in-out; }
        .dot-2 { animation: pulse-dot 1.4s infinite ease-in-out 0.2s; }
        .dot-3 { animation: pulse-dot 1.4s infinite ease-in-out 0.4s; }
    </style>
</head>
<body class="h-full bg-[#0f0f17] text-gray-100">

<div id="app" class="flex h-full">

    {{-- Sidebar --}}
    <aside id="sidebar" class="flex w-70 flex-col bg-[#16161f] border-r border-white/5">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                </svg>
            </div>
            <span class="text-base font-semibold text-white tracking-tight">AI Chatbot</span>
        </div>

        {{-- New Chat Button --}}
        <div class="px-3 pb-3">
            <button
                id="btn-new-chat"
                class="flex w-full items-center gap-2.5 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-gray-300 transition-all hover:bg-white/10 hover:text-white hover:border-white/20"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Chat
            </button>
        </div>

        {{-- Conversation List --}}
        <nav id="conversation-list" class="flex-1 space-y-1 overflow-y-auto scrollbar-thin px-3 pb-3">
            {{-- Populated by JS --}}
        </nav>

        {{-- Footer info --}}
        <div class="border-t border-white/5 px-5 py-3">
            <p class="text-[11px] text-gray-600">Powered by Laravel AI SDK</p>
        </div>
    </aside>

    {{-- Main Chat Area --}}
    <main class="flex flex-1 flex-col bg-[#0f0f17]">

        {{-- Header --}}
        <header id="chat-header" class="flex items-center justify-between border-b border-white/5 px-6 py-4">
            <h1 id="chat-title" class="text-base font-semibold text-gray-300">Select a conversation</h1>
            <button
                id="btn-delete-chat"
                class="hidden rounded-lg p-2 text-gray-500 transition-all hover:bg-red-500/10 hover:text-red-400"
                title="Delete conversation"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
            </button>
        </header>

        {{-- Messages --}}
        <div id="messages" class="flex-1 overflow-y-auto scrollbar-thin px-4 py-6 sm:px-8 lg:px-16">
            <div id="empty-state" class="flex h-full flex-col items-center justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-indigo-600/10 mb-6">
                    <svg class="h-10 w-10 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-gray-300 mb-2">Start a conversation</h2>
                <p class="text-sm text-gray-500 max-w-sm text-center">Click <span class="text-gray-400 font-medium">"New Chat"</span> to create a conversation, then send a message to get an AI response.</p>
            </div>
        </div>

        {{-- Loading indicator --}}
        <div id="loading" class="hidden px-4 py-3 sm:px-8 lg:px-16">
            <div class="flex items-center gap-3 msg-animate">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600/20">
                    <svg class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-1.5 rounded-2xl bg-[#1a1a2e] px-4 py-3">
                    <span class="h-2 w-2 rounded-full bg-indigo-400 dot-1"></span>
                    <span class="h-2 w-2 rounded-full bg-indigo-400 dot-2"></span>
                    <span class="h-2 w-2 rounded-full bg-indigo-400 dot-3"></span>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div id="input-area" class="border-t border-white/5 p-4 sm:px-8 lg:px-16">
            <form id="message-form" class="flex items-end gap-3">
                <div class="relative flex-1">
                    <input
                        id="message-input"
                        type="text"
                        placeholder="Send a message..."
                        autocomplete="off"
                        disabled
                        class="w-full rounded-xl border border-white/10 bg-[#1a1a2e] px-4 py-3 pr-4 text-sm text-gray-100 placeholder-gray-500 outline-none transition-all focus:border-indigo-500/50 focus:bg-[#1e1e32] focus:ring-1 focus:ring-indigo-500/30 disabled:opacity-40"
                    />
                </div>
                <button
                    id="btn-send"
                    type="submit"
                    disabled
                    class="flex h-11.5 items-center justify-center rounded-xl bg-indigo-600 px-5 text-sm font-medium text-white transition-all hover:bg-indigo-500 hover:shadow-lg hover:shadow-indigo-600/20 disabled:opacity-40 disabled:hover:bg-indigo-600 disabled:hover:shadow-none"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                </button>
            </form>
        </div>

    </main>
</div>

</body>
</html>
