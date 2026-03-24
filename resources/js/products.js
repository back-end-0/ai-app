/**
 * Products UI — card grid with language toggle, theme, AI search, pagination.
 */

if (!document.getElementById('products-grid')) {
    // Not on the products page
} else {

const API = '/api/products';
const grid = document.getElementById('products-grid');
const skeleton = document.getElementById('loading-skeleton');
const subtitle = document.getElementById('subtitle');
const paginationEl = document.getElementById('pagination');
const statTotal = document.getElementById('stat-total');
const statNoDesc = document.getElementById('stat-no-desc');
const statNoTrans = document.getElementById('stat-no-trans');
const btnGenerateAll = document.getElementById('btn-generate-all');
const btnTranslateAll = document.getElementById('btn-translate-all');
const btnTheme = document.getElementById('btn-theme');
const btnLang = document.getElementById('btn-lang');
const langLabel = document.getElementById('lang-label');
const searchForm = document.getElementById('search-form');
const searchInput = document.getElementById('search-input');
const searchStatus = document.getElementById('search-status');
const btnSearch = document.getElementById('btn-search');

let products = [];
let allProducts = [];
let currentLang = 'en';
let currentTheme = localStorage.getItem('theme') || 'dark';
let currentPage = 1;
let lastPage = 1;
let isSearchMode = false;

// ── Theme ─────────────────────────────────────────────────
function applyTheme(theme) {
    const body = document.body;
    body.classList.remove('theme-dark', 'theme-light');
    body.classList.add(`theme-${theme}`);

    const iconSun = document.getElementById('icon-sun');
    const iconMoon = document.getElementById('icon-moon');

    if (theme === 'light') {
        body.classList.remove('text-gray-100');
        body.classList.add('text-gray-900');
        iconSun.classList.remove('hidden');
        iconMoon.classList.add('hidden');
    } else {
        body.classList.remove('text-gray-900');
        body.classList.add('text-gray-100');
        iconSun.classList.add('hidden');
        iconMoon.classList.remove('hidden');
    }
    localStorage.setItem('theme', theme);
    currentTheme = theme;
}

btnTheme.addEventListener('click', () => {
    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
    renderProducts();
    renderPagination();
});

applyTheme(currentTheme);

// ── Language ──────────────────────────────────────────────
btnLang.addEventListener('click', () => {
    currentLang = currentLang === 'en' ? 'ar' : 'en';
    langLabel.textContent = currentLang.toUpperCase();
    renderProducts();
});

// ── API ───────────────────────────────────────────────────
async function fetchProducts(page = 1) {
    const { data } = await axios.get(`${API}?page=${page}&per_page=9`);
    return data;
}

async function fetchAllProducts() {
    const { data } = await axios.get(`${API}?per_page=999`);
    return data.data;
}

async function searchProducts(query) {
    const { data } = await axios.get(`${API}/search`, { params: { q: query } });
    return data;
}

async function generateDescription(productId) {
    const { data } = await axios.post(`${API}/${productId}/generate-description`);
    return data.data;
}

async function translateProduct(productId) {
    const { data } = await axios.post(`${API}/${productId}/translate`);
    return data.data;
}

// ── Helpers ───────────────────────────────────────────────
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function isDark() { return currentTheme === 'dark'; }

function setButtonLoading(btn, label) {
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = `<svg class="h-3 w-3 spinner" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 19.644l3.181-3.182"/></svg> ${label}`;
    btn.classList.add('opacity-60', 'pointer-events-none');
}

const checkSvg = '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>';

// ── Render ────────────────────────────────────────────────
function renderProducts() {
    grid.innerHTML = '';

    if (products.length === 0) {
        const emptyMsg = isSearchMode ? 'No results found. Try a different search.' : 'No products found.';
        grid.innerHTML = `<div class="col-span-full py-20 text-center text-gray-500">${emptyMsg}</div>`;
        skeleton.classList.add('hidden');
        grid.classList.remove('hidden');
        return;
    }

    const isAr = currentLang === 'ar';
    const dk = isDark();

    products.forEach((p, i) => {
        const t = p.arabic_translation;
        const card = document.createElement('div');
        card.id = `product-${p.id}`;
        card.className = `card-animate rounded-2xl border border-[var(--border)] bg-[var(--bg-card)] p-5 transition-all hover:border-[var(--border-hover)] ${dk ? '' : 'shadow-sm hover:shadow-md'}`;
        card.style.animationDelay = `${i * 30}ms`;
        if (isAr) card.dir = 'rtl';

        const displayName = (isAr && t) ? t.name : p.name;
        const displayDesc = (isAr && t) ? t.description : (p.description || null);
        const hasDesc = !!displayDesc;
        const noOriginalDesc = !p.description;

        const inStock = p.quantity > 0;
        const stockBg = inStock
            ? (dk ? 'bg-emerald-500/10 text-emerald-400' : 'bg-emerald-50 text-emerald-600')
            : (dk ? 'bg-red-500/10 text-red-400' : 'bg-red-50 text-red-600');
        const stockText = inStock ? `${p.quantity} in stock` : 'Out of stock';

        const nameColor = dk ? 'text-gray-100' : 'text-gray-900';
        const priceColor = dk ? 'text-white' : 'text-gray-900';
        const descColor = dk ? 'text-gray-400' : 'text-gray-600';
        const noDescColor = dk ? 'text-gray-600' : 'text-gray-400';
        const priceBg = dk ? 'bg-white/5' : 'bg-gray-100';
        const doneBtnDesc = dk ? 'bg-emerald-500/10 text-emerald-500/70' : 'bg-emerald-50 text-emerald-500';
        const doneBtnAr = dk ? 'bg-amber-500/10 text-amber-500/70' : 'bg-amber-50 text-amber-500';
        const genBtnCls = dk ? 'bg-indigo-500/15 text-indigo-400 hover:bg-indigo-500/25' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100';
        const trBtnCls = dk ? 'bg-amber-500/15 text-amber-400 hover:bg-amber-500/25' : 'bg-amber-50 text-amber-600 hover:bg-amber-100';

        card.innerHTML = `
            <div class="mb-3 flex items-start justify-between gap-3">
                <h3 class="min-w-0 flex-1 text-sm font-semibold leading-snug ${nameColor}">${escapeHtml(displayName)}</h3>
                <span class="shrink-0 rounded-lg ${priceBg} px-2.5 py-1 text-xs font-bold ${priceColor}">$${parseFloat(p.price).toFixed(2)}</span>
            </div>
            <div class="mb-4 min-h-10">
                <p id="desc-${p.id}" class="text-xs leading-relaxed ${hasDesc ? descColor : `italic ${noDescColor}`}">
                    ${hasDesc ? escapeHtml(displayDesc) : (isAr ? '\u0644\u0627 \u064a\u0648\u062c\u062f \u0648\u0635\u0641 \u0628\u0639\u062f' : 'No description yet')}
                </p>
            </div>
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-medium ${stockBg}">${stockText}</span>
                <div class="flex items-center gap-1.5" id="actions-${p.id}">
                    ${noOriginalDesc ? `
                        <button id="btn-gen-${p.id}" onclick="handleGenerate(${p.id})"
                            class="inline-flex items-center gap-1 rounded-lg ${genBtnCls} px-2.5 py-1 text-[11px] font-medium transition-all" title="Generate description">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            Desc
                        </button>
                    ` : `<span class="inline-flex items-center gap-1 rounded-lg ${doneBtnDesc} px-2.5 py-1 text-[11px]">${checkSvg} Desc</span>`}
                    ${!t ? `
                        <button id="btn-tr-${p.id}" onclick="handleTranslate(${p.id})"
                            class="inline-flex items-center gap-1 rounded-lg ${trBtnCls} px-2.5 py-1 text-[11px] font-medium transition-all" title="Translate to Arabic">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg>
                            AR
                        </button>
                    ` : `<span class="inline-flex items-center gap-1 rounded-lg ${doneBtnAr} px-2.5 py-1 text-[11px]">${checkSvg} AR</span>`}
                </div>
            </div>
        `;
        grid.appendChild(card);
    });

    skeleton.classList.add('hidden');
    grid.classList.remove('hidden');
    updateStats();
}

function renderPagination() {
    paginationEl.innerHTML = '';
    if (lastPage <= 1 || isSearchMode) {
        paginationEl.classList.add('hidden');
        return;
    }
    paginationEl.classList.remove('hidden');

    const dk = isDark();
    const baseCls = 'rounded-lg px-3 py-1.5 text-xs font-medium transition-all';
    const activeCls = `${baseCls} bg-indigo-600 text-white`;
    const inactiveCls = `${baseCls} ${dk ? 'bg-white/5 text-gray-400 hover:bg-white/10' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}`;

    for (let i = 1; i <= lastPage; i++) {
        const btn = document.createElement('button');
        btn.className = i === currentPage ? activeCls : inactiveCls;
        btn.textContent = i;
        btn.addEventListener('click', () => loadPage(i));
        paginationEl.appendChild(btn);
    }
}

function updateStats() {
    const noDesc = allProducts.filter(p => !p.description).length;
    const noTrans = allProducts.filter(p => !p.arabic_translation).length;

    statTotal.textContent = allProducts.length;
    statNoDesc.textContent = noDesc;
    statNoTrans.textContent = noTrans;

    const prefix = isSearchMode ? `Search: ${products.length} results` : `${allProducts.length} products`;
    subtitle.textContent = `${prefix} \u2014 ${noDesc} need desc, ${noTrans} need translation`;

    btnGenerateAll.disabled = noDesc === 0;
    btnTranslateAll.disabled = noTrans === 0;
}

// ── Actions ───────────────────────────────────────────────
window.handleGenerate = async function(productId) {
    const btn = document.getElementById(`btn-gen-${productId}`);
    setButtonLoading(btn, 'AI...');

    try {
        const result = await generateDescription(productId);
        [products, allProducts].forEach(arr => {
            const p = arr.find(x => x.id === productId);
            if (p) p.description = result.description;
        });
        renderProducts();
    } catch (err) {
        if (btn) {
            btn.innerHTML = '<span class="text-[10px] text-red-400">Error</span>';
            btn.disabled = false;
            btn.classList.remove('opacity-60', 'pointer-events-none');
        }
        console.error('Generate error:', err);
    }
};

window.handleTranslate = async function(productId) {
    const btn = document.getElementById(`btn-tr-${productId}`);
    setButtonLoading(btn, 'AI...');

    try {
        const result = await translateProduct(productId);
        [products, allProducts].forEach(arr => {
            const p = arr.find(x => x.id === productId);
            if (p) p.arabic_translation = { name: result.name, description: result.description };
        });
        renderProducts();
    } catch (err) {
        if (btn) {
            btn.innerHTML = '<span class="text-[10px] text-red-400">Error</span>';
            btn.disabled = false;
            btn.classList.remove('opacity-60', 'pointer-events-none');
        }
        console.error('Translate error:', err);
    }
};

async function handleGenerateAll() {
    const missing = allProducts.filter(p => !p.description);
    if (missing.length === 0) return;
    btnGenerateAll.disabled = true;
    let done = 0;
    for (const product of missing) {
        btnGenerateAll.innerHTML = `<svg class="h-3 w-3 spinner" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 19.644l3.181-3.182"/></svg> ${++done}/${missing.length}`;
        try { await window.handleGenerate(product.id); } catch {}
    }
    btnGenerateAll.innerHTML = `<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg> All Desc`;
    updateStats();
}

async function handleTranslateAll() {
    const missing = allProducts.filter(p => !p.arabic_translation);
    if (missing.length === 0) return;
    btnTranslateAll.disabled = true;
    let done = 0;
    for (const product of missing) {
        btnTranslateAll.innerHTML = `<svg class="h-3 w-3 spinner" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 19.644l3.181-3.182"/></svg> ${++done}/${missing.length}`;
        try { await window.handleTranslate(product.id); } catch {}
    }
    btnTranslateAll.innerHTML = `<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/></svg> All AR`;
    updateStats();
}

// ── Search ────────────────────────────────────────────────
searchForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const q = searchInput.value.trim();
    if (!q) { exitSearch(); return; }

    btnSearch.disabled = true;
    searchStatus.classList.remove('hidden');
    searchStatus.textContent = 'AI is searching...';
    searchStatus.className = 'mt-1.5 text-[10px] text-gray-500';

    try {
        const result = await searchProducts(q);
        isSearchMode = true;
        products = result.data;
        searchStatus.textContent = `Found ${products.length} result(s) for \u201c${q}\u201d`;
        renderProducts();
        renderPagination();
    } catch (err) {
        searchStatus.textContent = 'Search failed. Try again.';
        searchStatus.className = 'mt-1.5 text-[10px] text-red-400';
        console.error('Search error:', err);
    } finally {
        btnSearch.disabled = false;
    }
});

searchInput.addEventListener('input', () => {
    if (!searchInput.value.trim() && isSearchMode) exitSearch();
});

function exitSearch() {
    isSearchMode = false;
    searchStatus.classList.add('hidden');
    searchInput.value = '';
    loadPage(currentPage);
}

// ── Pagination ────────────────────────────────────────────
async function loadPage(page) {
    try {
        const result = await fetchProducts(page);
        products = result.data;
        currentPage = result.current_page;
        lastPage = result.last_page;
        renderProducts();
        renderPagination();
    } catch (err) {
        console.error('Load error:', err);
    }
}

// ── Events ────────────────────────────────────────────────
btnGenerateAll.addEventListener('click', handleGenerateAll);
btnTranslateAll.addEventListener('click', handleTranslateAll);

// ── Init ──────────────────────────────────────────────────
async function init() {
    try {
        const [pageResult, all] = await Promise.all([fetchProducts(1), fetchAllProducts()]);
        allProducts = all;
        products = pageResult.data;
        currentPage = pageResult.current_page;
        lastPage = pageResult.last_page;
        renderProducts();
        renderPagination();
    } catch (err) {
        skeleton.classList.add('hidden');
        grid.classList.remove('hidden');
        grid.innerHTML = '<div class="col-span-full py-20 text-center text-red-400">Failed to load products.</div>';
        console.error(err);
    }
}

init();

} // end guard
