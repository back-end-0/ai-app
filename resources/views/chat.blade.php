<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat — AI App</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=noto-sans-arabic:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        [dir="rtl"] { font-family: 'Noto Sans Arabic', 'Inter', sans-serif; }

        /* Scrollbar */
        .scrollbar-thin::-webkit-scrollbar { width: 5px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { border-radius: 3px; }
        .theme-dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #2a2a3d; }
        .theme-light .scrollbar-thin::-webkit-scrollbar-thumb { background: #d1d5db; }

        /* Animations */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-8px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .msg-animate { animation: fadeInUp 0.3s ease-out; }
        .sidebar-item { animation: slideIn 0.2s ease-out both; }
        .scale-animate { animation: scaleIn 0.2s ease-out; }

        /* Typing dots */
        @keyframes bounce { 0%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-6px); } }
        .dot-1 { animation: bounce 1.2s infinite ease-in-out; }
        .dot-2 { animation: bounce 1.2s infinite ease-in-out 0.15s; }
        .dot-3 { animation: bounce 1.2s infinite ease-in-out 0.3s; }

        /* Theme tokens */
        .theme-dark {
            --bg-body: #0c0c14;
            --bg-sidebar: #111119;
            --bg-card: #16161f;
            --bg-input: #1a1a28;
            --bg-hover: rgba(255,255,255,0.04);
            --bg-active: rgba(255,255,255,0.08);
            --border: rgba(255,255,255,0.06);
            --border-hover: rgba(255,255,255,0.12);
            --text-primary: #f1f1f4;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --bubble-ai: #1a1a28;
            --bubble-ai-border: rgba(255,255,255,0.06);
        }
        .theme-light {
            --bg-body: #f7f7f8;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --bg-input: #f3f4f6;
            --bg-hover: rgba(0,0,0,0.03);
            --bg-active: rgba(0,0,0,0.06);
            --border: rgba(0,0,0,0.08);
            --border-hover: rgba(0,0,0,0.15);
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --bubble-ai: #f3f4f6;
            --bubble-ai-border: rgba(0,0,0,0.06);
        }

        /* Accent color schemes */
        .accent-indigo  { --accent: #6366f1; --accent-light: #818cf8; --accent-dim: rgba(99,102,241,0.12); --accent-glow: rgba(99,102,241,0.25); }
        .accent-violet  { --accent: #8b5cf6; --accent-light: #a78bfa; --accent-dim: rgba(139,92,246,0.12); --accent-glow: rgba(139,92,246,0.25); }
        .accent-rose    { --accent: #f43f5e; --accent-light: #fb7185; --accent-dim: rgba(244,63,94,0.12);  --accent-glow: rgba(244,63,94,0.25); }
        .accent-emerald { --accent: #10b981; --accent-light: #34d399; --accent-dim: rgba(16,185,129,0.12); --accent-glow: rgba(16,185,129,0.25); }
        .accent-amber   { --accent: #f59e0b; --accent-light: #fbbf24; --accent-dim: rgba(245,158,11,0.12); --accent-glow: rgba(245,158,11,0.25); }
        .accent-cyan    { --accent: #06b6d4; --accent-light: #22d3ee; --accent-dim: rgba(6,182,212,0.12);  --accent-glow: rgba(6,182,212,0.25); }

        /* Textarea auto-resize */
        #message-input { resize: none; max-height: 140px; }
    </style>
</head>
<body class="h-full theme-dark accent-indigo bg-[var(--bg-body)] text-[var(--text-primary)] transition-colors duration-300">

<div id="app" class="flex h-full">

    {{-- Sidebar --}}
    <aside id="sidebar" class="flex w-72 flex-col bg-[var(--bg-sidebar)] border-r border-[var(--border)] transition-colors duration-300">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--accent)] shadow-lg shadow-[var(--accent-glow)] transition-colors duration-300">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                </svg>
            </div>
            <div>
                <span class="text-sm font-bold tracking-tight" style="color: var(--text-primary)">AI Chat</span>
                <p class="text-[10px]" style="color: var(--text-muted)">Powered by AI</p>
            </div>
        </div>

        {{-- New Chat Button --}}
        <div class="px-3 pb-3">
            <button id="btn-new-chat"
                class="flex w-full items-center gap-2.5 rounded-xl border border-[var(--border)] bg-[var(--bg-hover)] px-4 py-2.5 text-[13px] font-medium transition-all duration-200 hover:border-[var(--border-hover)] hover:bg-[var(--bg-active)]"
                style="color: var(--text-secondary)">
                <svg class="h-4 w-4" style="color: var(--accent)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Chat
            </button>
        </div>

        {{-- Conversation List --}}
        <nav id="conversation-list" class="flex-1 space-y-0.5 overflow-y-auto scrollbar-thin px-3 pb-3">
        </nav>

        {{-- Footer: Theme + Color + Products --}}
        <div class="border-t border-[var(--border)] px-4 py-3 space-y-3">
            {{-- Color Accent Picker --}}
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-medium" style="color: var(--text-muted)">Accent</span>
                <div id="color-picker" class="flex items-center gap-1.5">
                    <button data-accent="indigo"  class="h-5 w-5 rounded-full bg-indigo-500  ring-2 ring-transparent transition-all hover:scale-110" title="Indigo"></button>
                    <button data-accent="violet"  class="h-5 w-5 rounded-full bg-violet-500  ring-2 ring-transparent transition-all hover:scale-110" title="Violet"></button>
                    <button data-accent="rose"    class="h-5 w-5 rounded-full bg-rose-500    ring-2 ring-transparent transition-all hover:scale-110" title="Rose"></button>
                    <button data-accent="emerald" class="h-5 w-5 rounded-full bg-emerald-500 ring-2 ring-transparent transition-all hover:scale-110" title="Emerald"></button>
                    <button data-accent="amber"   class="h-5 w-5 rounded-full bg-amber-500   ring-2 ring-transparent transition-all hover:scale-110" title="Amber"></button>
                    <button data-accent="cyan"    class="h-5 w-5 rounded-full bg-cyan-500    ring-2 ring-transparent transition-all hover:scale-110" title="Cyan"></button>
                </div>
            </div>

            {{-- Theme & Navigation --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <button id="btn-theme" class="flex h-7 w-7 items-center justify-center rounded-lg border border-[var(--border)] transition-all hover:border-[var(--border-hover)]" title="Toggle theme">
                        <svg id="icon-sun" class="h-3.5 w-3.5 text-amber-400 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                        </svg>
                        <svg id="icon-moon" class="h-3.5 w-3.5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                        </svg>
                    </button>
                </div>
                <a href="/products" class="flex items-center gap-1.5 rounded-lg border border-[var(--border)] px-2.5 py-1.5 text-[10px] font-medium transition-all hover:border-[var(--border-hover)]" style="color: var(--text-muted)">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                    Products
                </a>
            </div>
        </div>
    </aside>

    {{-- Main Chat Area --}}
    <main class="flex flex-1 flex-col bg-[var(--bg-body)] transition-colors duration-300">

        {{-- Header --}}
        <header id="chat-header" class="flex items-center justify-between border-b border-[var(--border)] bg-[var(--bg-body)]/80 backdrop-blur-sm px-6 py-3.5 transition-colors duration-300">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--accent-dim)] transition-colors duration-300">
                    <svg class="h-4 w-4" style="color: var(--accent)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                </div>
                <div>
                    <h1 id="chat-title" class="text-[13px] font-semibold" style="color: var(--text-primary)">Select a conversation</h1>
                    <p id="chat-status" class="text-[10px]" style="color: var(--text-muted)">Ready</p>
                </div>
            </div>
            <button id="btn-delete-chat"
                class="hidden flex items-center gap-1.5 rounded-lg border border-[var(--border)] px-2.5 py-1.5 text-[11px] font-medium text-red-400 transition-all hover:bg-red-500/10 hover:border-red-500/30"
                title="Delete conversation">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                </svg>
                Delete
            </button>
        </header>

        {{-- Messages --}}
        <div id="messages" class="flex-1 overflow-y-auto scrollbar-thin">
            <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
                <div id="empty-state" class="flex h-full min-h-[60vh] flex-col items-center justify-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[var(--accent-dim)] mb-6 scale-animate transition-colors duration-300">
                        <svg class="h-10 w-10" style="color: var(--accent)" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold mb-2" style="color: var(--text-primary)">Start a conversation</h2>
                    <p class="text-[13px] max-w-sm text-center leading-relaxed" style="color: var(--text-muted)">
                        Click <span class="font-medium" style="color: var(--accent)">New Chat</span> to begin. Ask about products, prices, or anything else.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <button class="suggestion-chip rounded-full border border-[var(--border)] px-3.5 py-1.5 text-[11px] font-medium transition-all hover:border-[var(--border-hover)] hover:bg-[var(--bg-hover)]" style="color: var(--text-secondary)">
                            What products do you have?
                        </button>
                        <button class="suggestion-chip rounded-full border border-[var(--border)] px-3.5 py-1.5 text-[11px] font-medium transition-all hover:border-[var(--border-hover)] hover:bg-[var(--bg-hover)]" style="color: var(--text-secondary)">
                            Compare prices
                        </button>
                        <button class="suggestion-chip rounded-full border border-[var(--border)] px-3.5 py-1.5 text-[11px] font-medium transition-all hover:border-[var(--border-hover)] hover:bg-[var(--bg-hover)]" style="color: var(--text-secondary)">
                            What's in stock?
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Loading indicator --}}
        <div id="loading" class="hidden">
            <div class="mx-auto max-w-3xl px-4 pb-4 sm:px-6">
                <div class="flex gap-3 msg-animate">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--accent-dim)] transition-colors duration-300">
                        <svg class="h-4 w-4" style="color: var(--accent)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-1.5 rounded-2xl bg-[var(--bubble-ai)] border border-[var(--bubble-ai-border)] px-4 py-3 transition-colors duration-300">
                        <span class="h-1.5 w-1.5 rounded-full dot-1" style="background: var(--accent)"></span>
                        <span class="h-1.5 w-1.5 rounded-full dot-2" style="background: var(--accent)"></span>
                        <span class="h-1.5 w-1.5 rounded-full dot-3" style="background: var(--accent)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div id="input-area" class="border-t border-[var(--border)] bg-[var(--bg-body)]/80 backdrop-blur-sm transition-colors duration-300">
            <div class="mx-auto max-w-3xl px-4 py-4 sm:px-6">
                <form id="message-form" class="relative">
                    <textarea id="message-input" rows="1"
                        placeholder="Type your message..."
                        autocomplete="off"
                        disabled
                        class="w-full rounded-2xl border border-[var(--border)] bg-[var(--bg-input)] px-4 py-3 pr-14 text-[13px] placeholder-[var(--text-muted)] outline-none transition-all duration-200 focus:border-[var(--accent)]/40 focus:ring-2 focus:ring-[var(--accent-glow)] disabled:opacity-40"
                        style="color: var(--text-primary)"
                    ></textarea>
                    <button id="btn-send" type="submit" disabled
                        class="absolute right-2.5 bottom-2.5 flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--accent)] text-white shadow-lg transition-all duration-200 hover:opacity-90 hover:shadow-[var(--accent-glow)] disabled:opacity-30 disabled:shadow-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18"/>
                        </svg>
                    </button>
                </form>
                <p class="mt-2 text-center text-[10px]" style="color: var(--text-muted)">AI can make mistakes. Verify important information.</p>
            </div>
        </div>

    </main>
</div>

</body>
</html>
