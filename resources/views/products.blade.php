<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — AI App</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=noto-sans-arabic:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        [dir="rtl"] { font-family: 'Noto Sans Arabic', 'Inter', sans-serif; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .card-animate { animation: fadeIn 0.3s ease-out both; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { animation: spin 0.8s linear infinite; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { border-radius: 3px; }
        .theme-dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #374151; }
        .theme-light .scrollbar-thin::-webkit-scrollbar-thumb { background: #d1d5db; }

        /* Theme variables */
        .theme-dark { --bg-body: #0f0f17; --bg-card: #16161f; --bg-bar: #12121c; --border: rgba(255,255,255,0.05); --border-hover: rgba(255,255,255,0.1); }
        .theme-light { --bg-body: #f8fafc; --bg-card: #ffffff; --bg-bar: #f1f5f9; --border: rgba(0,0,0,0.06); --border-hover: rgba(0,0,0,0.12); }
    </style>
</head>
<body class="h-full theme-dark bg-[var(--bg-body)] text-gray-100 transition-colors duration-300">

<div class="flex h-full flex-col">

    {{-- Header --}}
    <header class="shrink-0 border-b border-[var(--border)] bg-[var(--bg-body)]/80 backdrop-blur-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-semibold theme-dark:text-white theme-light:text-gray-900 tracking-tight" id="page-title">Product Catalog</h1>
                    <p id="subtitle" class="text-xs text-gray-500">Loading...</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Theme Toggle --}}
                <button id="btn-theme" class="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--border)] transition-all hover:border-[var(--border-hover)]" title="Toggle theme">
                    <svg id="icon-sun" class="h-4 w-4 text-amber-400 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                    </svg>
                    <svg id="icon-moon" class="h-4 w-4 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                    </svg>
                </button>
                {{-- Language Toggle --}}
                <button id="btn-lang" class="flex items-center gap-1.5 rounded-xl border border-[var(--border)] px-3 py-2 text-xs font-medium text-gray-400 transition-all hover:border-[var(--border-hover)] hover:text-gray-200" title="Toggle language">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/>
                    </svg>
                    <span id="lang-label">EN</span>
                </button>
                {{-- Chat Link --}}
                <a href="/chat" class="flex items-center gap-2 rounded-xl border border-[var(--border)] px-3 py-2 text-xs font-medium text-gray-400 transition-all hover:border-[var(--border-hover)] hover:text-gray-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                    Chat
                </a>
            </div>
        </div>
    </header>

    {{-- Toolbar: Search + Bulk Actions --}}
    <div class="shrink-0 border-b border-[var(--border)] bg-[var(--bg-bar)]">
        <div class="mx-auto flex max-w-7xl items-center gap-4 px-6 py-3">
            {{-- AI Search --}}
            <div class="relative flex-1 max-w-md">
                <form id="search-form" class="flex">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z"/>
                        </svg>
                        <input id="search-input" type="text" placeholder="AI Search... (e.g. cheap audio, out of stock)" autocomplete="off"
                            class="w-full rounded-l-xl border border-[var(--border)] bg-[var(--bg-card)] py-2 pl-10 pr-3 text-xs text-gray-200 placeholder-gray-500 outline-none transition-all focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/30 theme-light:text-gray-900" />
                    </div>
                    <button type="submit" id="btn-search" class="rounded-r-xl bg-indigo-600 px-4 text-xs font-medium text-white transition-all hover:bg-indigo-500 disabled:opacity-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </button>
                </form>
                <div id="search-status" class="hidden mt-1.5 text-[10px] text-gray-500"></div>
            </div>

            {{-- Stats --}}
            <div class="hidden items-center gap-4 sm:flex">
                <div class="flex items-center gap-1.5">
                    <span class="flex h-5 min-w-5 items-center justify-center rounded bg-white/5 px-1 text-[10px] font-bold text-gray-400" id="stat-total">—</span>
                    <span class="text-[10px] text-gray-500">Total</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="flex h-5 min-w-5 items-center justify-center rounded bg-indigo-500/10 px-1 text-[10px] font-bold text-indigo-400" id="stat-no-desc">—</span>
                    <span class="text-[10px] text-gray-500">No desc</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="flex h-5 min-w-5 items-center justify-center rounded bg-amber-500/10 px-1 text-[10px] font-bold text-amber-400" id="stat-no-trans">—</span>
                    <span class="text-[10px] text-gray-500">No AR</span>
                </div>
            </div>

            {{-- Bulk --}}
            <div class="flex items-center gap-2">
                <button id="btn-generate-all" class="inline-flex items-center gap-1 rounded-lg bg-indigo-500/15 px-2.5 py-1.5 text-[11px] font-medium text-indigo-400 transition-all hover:bg-indigo-500/25 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    All Desc
                </button>
                <button id="btn-translate-all" class="inline-flex items-center gap-1 rounded-lg bg-amber-500/15 px-2.5 py-1.5 text-[11px] font-medium text-amber-400 transition-all hover:bg-amber-500/25 disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg>
                    All AR
                </button>
            </div>
        </div>
    </div>

    {{-- Product Grid --}}
    <main class="flex-1 overflow-y-auto scrollbar-thin">
        <div class="mx-auto max-w-7xl px-6 py-6">
            {{-- Skeleton --}}
            <div id="loading-skeleton" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @for ($i = 0; $i < 9; $i++)
                <div class="animate-pulse rounded-2xl border border-[var(--border)] bg-[var(--bg-card)] p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="h-4 w-28 rounded bg-white/5"></div>
                        <div class="h-5 w-14 rounded bg-white/5"></div>
                    </div>
                    <div class="mb-2 h-3 w-full rounded bg-white/5"></div>
                    <div class="mb-4 h-3 w-3/4 rounded bg-white/5"></div>
                    <div class="flex justify-between">
                        <div class="h-5 w-16 rounded bg-white/5"></div>
                        <div class="flex gap-1.5">
                            <div class="h-6 w-14 rounded-lg bg-white/5"></div>
                            <div class="h-6 w-10 rounded-lg bg-white/5"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            <div id="products-grid" class="hidden grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"></div>

            {{-- Pagination --}}
            <div id="pagination" class="hidden mt-6 flex items-center justify-center gap-2"></div>
        </div>
    </main>
</div>

</body>
</html>
