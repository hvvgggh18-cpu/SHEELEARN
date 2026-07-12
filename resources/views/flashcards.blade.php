@extends('layouts.dashboard-layout')

@section('title', 'Flashcards')

@section('styles')
<style>
    :root {
        --surface: rgba(15,23,42,0.65);
        --surface-2: rgba(15,23,42,0.50);
        --surface-3: rgba(15,23,42,0.35);
        --border: rgba(226,232,240,0.06);
        --border-hover: rgba(34,211,238,0.12);
        --accent: #22d3ee;
        --accent-hover: #3b82f6;
        --accent-dim: rgba(34,211,238,0.12);
        --accent-border: rgba(34,211,238,0.18);
        --text: #e2e8f0;
        --text-muted: rgba(226,232,240,0.75);
        --text-dim: rgba(226,232,240,0.45);
        --danger: #ef4444;
        --warning: #f59e0b;
    }

    .tab-btn {
        padding: 8px 18px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.2s;
        border: 1px solid transparent;
        cursor: pointer;
        background: transparent;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .tab-btn:hover { color: var(--text); background: var(--surface-2); }
    .tab-btn.active { color: var(--accent); background: var(--accent-dim); border-color: var(--accent-border); }

    .pill {
        padding: 7px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--border);
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s;
        color: var(--text-muted);
    }
    .pill:hover { border-color: var(--border-hover); color: var(--text); }
    .pill.active { background: var(--accent-dim); border-color: var(--accent-border); color: var(--accent); }

    .src-btn {
        flex: 1;
        padding: 14px 8px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        text-align: center;
    }
    .src-btn:hover { border-color: var(--border-hover); background: var(--surface-2); }
    .src-btn.active { border-color: var(--accent-border); background: var(--accent-dim); }
    .src-btn i { font-size: 20px; color: var(--text-dim); transition: color 0.2s; }
    .src-btn.active i { color: var(--accent); }
    .src-btn span { font-size: 12px; font-weight: 600; color: var(--text-muted); }
    .src-btn.active span { color: var(--accent); }

    .field {
        width: 100%;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        color: var(--text);
        font-family: 'Inter', system-ui, sans-serif;
        transition: border-color 0.2s;
        outline: none;
    }
    .field:focus { border-color: var(--accent-border); }
    .field::placeholder { color: var(--text-dim); }
    textarea.field { resize: vertical; min-height: 140px; line-height: 1.6; }

    .dropzone {
        border: 1.5px dashed var(--border-hover);
        border-radius: 14px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        background: var(--surface);
    }
    .dropzone:hover, .dropzone.dragover { border-color: var(--accent-border); background: var(--accent-dim); }

    .fc-wrap { perspective: 1200px; }
    .fc-inner {
        position: relative;
        width: 100%;
        height: 340px;
        transition: transform 0.55s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    .fc-inner.flipped { transform: rotateY(180deg); }
    .fc-face {
        position: absolute;
        inset: 0;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        border-radius: 16px;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        padding: 28px;
        overflow-y: auto;
    }
    .fc-front { background: var(--surface-2); }
    .fc-back { background: var(--surface-3); transform: rotateY(180deg); }

    .deck-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        transition: all 0.2s;
        cursor: pointer;
    }
    .deck-card:hover { border-color: var(--border-hover); transform: translateY(-2px); }

    .toast {
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(120%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        max-width: 360px;
    }
    .toast.show { transform: translateX(0); }
    .toast-success { background: #065f46; color: #a7f3d0; border: 1px solid rgba(16,185,129,0.3); }
    .toast-error { background: #7f1d1d; color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .toast-info { background: #1e3a5f; color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }

    .gen-btn {
        padding: 12px 32px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        background: var(--accent);
        color: #021a0f;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .gen-btn:hover { background: var(--accent-hover); transform: translateY(-1px); }
    .gen-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    .spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(2,26,15,0.3);
        border-top-color: #021a0f;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .progress-track {
        height: 4px;
        background: var(--surface-3);
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: var(--accent);
        border-radius: 4px;
        transition: width 0.4s ease;
    }

    .action-btn {
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--border);
        background: var(--surface-2);
        color: var(--text);
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .action-btn:hover { border-color: var(--border-hover); background: var(--surface-3); }
    .action-btn.primary { background: var(--accent-dim); border-color: var(--accent-border); color: var(--accent); }
    .action-btn.primary:hover { background: var(--accent); color: #021a0f; }
    .action-btn.danger { color: var(--danger); }
    .action-btn.danger:hover { background: rgba(239,68,68,0.1); border-color: rgba(239,68,68,0.3); }

    .kbd {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 6px;
        border-radius: 5px;
        background: var(--surface-3);
        border: 1px solid var(--border);
        font-size: 11px;
        font-weight: 600;
        color: var(--text-dim);
    }

    .view { animation: fadeUp 0.3s ease; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 640px) {
        .fc-inner { height: 280px; }
        .fc-face { padding: 20px; }
    }
</style>
@endsection

@section('content')
<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
<input type="file" id="fileInput" class="hidden" accept=".txt,.pdf,.md">
<input type="file" id="importInput" class="hidden" accept=".json">
<div class="max-w-7xl mx-auto space-y-6">
    <div class="pt-8 pb-6">
        <h1 class="text-3xl font-800 tracking-tight">Flashcards</h1>
        <p class="text-sm mt-1 text-c-60">Create, study, and master your cards</p>

        <nav class="flex gap-2 mt-6 bg-[var(--surface)] p-1.5 rounded-xl w-fit">
            <button class="tab-btn active" data-tab="create"><i class="fa-solid fa-plus text-xs"></i> Create</button>
            <button class="tab-btn" data-tab="library"><i class="fa-solid fa-folder-open text-xs"></i> Library</button>
            <button class="tab-btn" data-tab="study"><i class="fa-solid fa-graduation-cap text-xs"></i> Study</button>
        </nav>
    </div>

    <section id="createView" class="view space-y-6">
        <div>
            <p class="text-xs font-600 uppercase tracking-widest mb-3 text-c-40">Source</p>
            <div class="flex gap-3 flex-wrap">
                <button class="src-btn active" data-source="upload">
                    <i class="fa-solid fa-cloud-arrow-up"></i><span>Upload File</span>
                </button>
                <button class="src-btn" data-source="text">
                    <i class="fa-solid fa-paste"></i><span>Paste Text</span>
                </button>
                <button class="src-btn" data-source="manual">
                    <i class="fa-solid fa-pen"></i><span>Type Manually</span>
                </button>
            </div>
        </div>

        <div>
            <p class="text-xs font-600 uppercase tracking-widest mb-3 text-c-40">Content</p>
            <div id="uploadZone" class="dropzone">
                <i class="fa-solid fa-cloud-arrow-up text-3xl mb-3" style="color:var(--accent)"></i>
                <p class="text-sm font-600 text-c">Drag and drop your file here</p>
                <p class="text-xs mt-1 text-c-60">Supports TXT, PDF, and Markdown files</p>
                <button type="button" onclick="document.getElementById('fileInput').click()"
                    class="mt-4 px-5 py-2 rounded-lg text-sm font-600 transition"
                    style="background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-border)">
                    Browse Files
                </button>
                <p id="uploadStatus" class="text-xs mt-3 hidden" style="color:var(--accent)"></p>
            </div>

            <div id="textZone" class="hidden mt-4">
                <textarea id="sourceText" class="field" placeholder="Paste or type your study material here..."></textarea>
                <p class="text-xs mt-2 text-c-60">The AI will generate flashcards from this content.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <p class="text-xs font-600 uppercase tracking-widest mb-2 text-c-40">Cards</p>
                <div class="flex gap-2 flex-wrap">
                    <button class="pill" data-count="5">5</button>
                    <button class="pill active" data-count="10">10</button>
                    <button class="pill" data-count="15">15</button>
                    <button class="pill" data-count="20">20</button>
                </div>
            </div>
            <div>
                <p class="text-xs font-600 uppercase tracking-widest mb-2 text-c-40">Difficulty</p>
                <div class="flex gap-2 flex-wrap">
                    <button class="pill" data-diff="easy">Easy</button>
                    <button class="pill active" data-diff="medium">Medium</button>
                    <button class="pill" data-diff="hard">Hard</button>
                </div>
            </div>
            <div>
                <p class="text-xs font-600 uppercase tracking-widest mb-2 text-c-40">Style</p>
                <div class="flex gap-2 flex-wrap">
                    <button class="pill active" data-style="question_answer">Q & A</button>
                    <button class="pill" data-style="definition">Definition</button>
                    <button class="pill" data-style="term">Term</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-xs font-600 uppercase tracking-widest mb-2 text-c-40">Deck Title</p>
                <input id="deckTitle" type="text" class="field" placeholder="e.g. Biology Chapter 5">
            </div>
            <div>
                <p class="text-xs font-600 uppercase tracking-widest mb-2 text-c-40">Description</p>
                <input id="deckDesc" type="text" class="field" placeholder="Optional short description">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-2">
            <button id="generateBtn" class="gen-btn">
                <i class="fa-solid fa-bolt"></i> Generate Flashcards
            </button>
            <span id="genHint" class="text-xs text-c-60">
                <i class="fa-solid fa-clock"></i> Takes a few seconds
            </span>
        </div>
    </section>

    <section id="libraryView" class="view hidden">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-5">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-c-60"></i>
                <input id="searchDecks" type="text" class="field pl-9" placeholder="Search decks...">
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('importInput').click()" class="action-btn">
                    <i class="fa-solid fa-file-import"></i> Import
                </button>
            </div>
        </div>

        <div id="deckGrid" class="space-y-3"></div>

        <div id="emptyLibrary" class="hidden text-center py-16 text-c-60">
            <i class="fa-solid fa-folder-open text-4xl mb-4"></i>
            <p class="text-lg font-600 text-c">No decks yet</p>
            <p class="text-sm mt-1">Create your first flashcard deck to get started.</p>
            <button onclick="switchTab('create')" class="action-btn primary mt-5">
                <i class="fa-solid fa-plus"></i> Create Deck
            </button>
        </div>
    </section>

    <section id="studyView" class="view hidden">
        <div id="emptyStudy" class="text-center py-20 text-c-60">
            <i class="fa-solid fa-graduation-cap text-4xl mb-4"></i>
            <p class="text-lg font-600 text-c">No deck selected</p>
            <p class="text-sm mt-1">Choose a deck from your library to start studying.</p>
            <button onclick="switchTab('library')" class="action-btn primary mt-5">
                <i class="fa-solid fa-folder-open"></i> Browse Library
            </button>
        </div>

        <div id="activeStudy" class="hidden space-y-5">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h2 id="studyDeckTitle" class="text-lg font-700"></h2>
                    <p id="studyDeckMeta" class="text-xs mt-0.5 text-c-60"></p>
                </div>
                <button onclick="exitStudy()" class="action-btn">
                    <i class="fa-solid fa-xmark"></i> Exit
                </button>
            </div>

            <div>
                <div class="flex justify-between text-xs font-600 mb-1.5 text-c-60">
                    <span id="progressLabel">Card 1 of 10</span>
                    <span id="masteryLabel" class="text-cy">0% mastered</span>
                </div>
                <div class="progress-track">
                    <div id="progressFill" class="progress-fill" style="width:0%"></div>
                </div>
            </div>

            <div class="fc-wrap" id="flashcardWrap">
                <div class="fc-inner" id="flashcardInner">
                    <div class="fc-face fc-front">
                        <span class="text-xs font-600 uppercase tracking-widest text-cy">Question</span>
                        <div class="flex-1 flex items-center justify-center text-center px-4">
                            <p id="cardQuestion" class="text-xl font-600 leading-relaxed"></p>
                        </div>
                        <p class="text-center text-xs text-c-60">Click or press <span class="kbd">Space</span> to flip</p>
                    </div>
                    <div class="fc-face fc-back">
                        <span class="text-xs font-600 uppercase tracking-widest text-amber-300">Answer</span>
                        <div class="flex-1 flex items-center justify-center text-center px-4">
                            <p id="cardAnswer" class="text-lg leading-relaxed text-c"></p>
                        </div>
                        <p class="text-center text-xs text-c-60">Click or press <span class="kbd">Space</span> to flip back</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <button onclick="prevCard()" class="action-btn"><i class="fa-solid fa-arrow-left"></i> Prev</button>
                <div class="flex flex-wrap gap-2 justify-center">
                    <button onclick="rateCard(false)" class="action-btn danger" style="border-color:rgba(239,68,68,0.2)">
                        <i class="fa-solid fa-xmark"></i> Still Learning
                    </button>
                    <button onclick="rateCard(true)" class="action-btn primary">
                        <i class="fa-solid fa-check"></i> Know It
                    </button>
                </div>
                <button onclick="nextCard()" class="action-btn">Next <i class="fa-solid fa-arrow-right"></i></button>
            </div>

            <div class="flex flex-wrap justify-center gap-4 text-xs text-c-60">
                <span><span class="kbd">Space</span> Flip</span>
                <span><span class="kbd">?</span> Prev</span>
                <span><span class="kbd">?</span> Next</span>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
}

const state = {
    currentTab: 'create',
    sourceType: 'upload',
    cardCount: 10,
    difficulty: 'medium',
    cardStyle: 'question_answer',
    uploadedText: '',
    uploadedFileName: '',
    activeDeckId: null,
    studyIndex: 0,
    isFlipped: false,
    sessionCorrect: 0,
    sessionIncorrect: 0,
};

function getDecks() {
    try { return JSON.parse(localStorage.getItem('fc_decks') || '[]'); }
    catch { return []; }
}
function saveDecks(decks) { try { localStorage.setItem('fc_decks', JSON.stringify(decks)); } catch(e) {} }
function getDeck(id) { return getDecks().find(d => d.id === id) || null; }
function saveDeck(deck) {
    const decks = getDecks();
    const idx = decks.findIndex(d => d.id === deck.id);
    if (idx >= 0) decks[idx] = deck; else decks.unshift(deck);
    saveDecks(decks);
}
function deleteDeck(id) { saveDecks(getDecks().filter(d => d.id !== id)); }
function uid() { return 'd' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }

function generateCardsFromText(text, count, style) {
    const chunks = text
        .replace(/\r\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim()
        .split(/\n\n+/)
        .flatMap(para => para.split(/(?<=[.!?])\s+(?=[A-Z])/))
        .map(s => s.trim())
        .filter(s => s.length > 15);

    if (chunks.length === 0) {
        const lines = text.split(/\n/).map(l => l.trim()).filter(l => l.length > 5);
        if (lines.length === 0) return [];
        return lines.slice(0, count).map((line, i) => makeCard(
            `Explain: ${line.substring(0, 80)}${line.length > 80 ? '...' : ''}`,
            line, i
        ));
    }

    const cards = [];
    for (let i = 0; i < chunks.length && cards.length < count; i++) {
        const chunk = chunks[i];
        let q, a;

        if (style === 'question_answer') {
            const sentences = chunk.split(/(?<=[.!?])\s+/).filter(s => s.length > 5);
            if (sentences.length >= 2) {
                q = toQuestion(sentences[0]);
                a = sentences.slice(1).join(' ').trim();
                if (!a) a = sentences[0];
            } else {
                q = toQuestion(chunk);
                a = chunk;
            }
        } else if (style === 'definition') {
            const term = extractTerm(chunk);
            q = `What is ${term}?`;
            a = chunk;
        } else {
            const term = extractTerm(chunk);
            q = `Define: ${term}`;
            a = chunk;
        }

        cards.push(makeCard(q, a, i));
    }

    let idx = cards.length;
    while (cards.length < count && idx < chunks.length * 2) {
        const chunk = chunks[idx % chunks.length];
        const term = extractTerm(chunk);
        cards.push(makeCard(`Explain the concept of ${term}`, chunk, idx));
        idx++;
    }

    return cards.slice(0, count);
}

function makeCard(question, answer, index) {
    return {
        id: 'c' + Date.now().toString(36) + index,
        question,
        answer,
        correctCount: 0,
        incorrectCount: 0,
        lastReviewed: null,
        mastered: false,
    };
}

function toQuestion(statement) {
    let s = statement.replace(/[.!?]+$/, '').trim();
    if (/^(what|who|where|when|why|how|which)/i.test(s)) return s + '?';

    const isM = s.match(/^(.{3,50}?)\s+is\s+/i);
    if (isM) return `What is ${isM[1].trim()}?`;
    const areM = s.match(/^(.{3,50}?)\s+are\s+/i);
    if (areM) return `What are ${areM[1].trim()}?`;
    const canM = s.match(/^(.{3,50}?)\s+can\s+/i);
    if (canM) return `What can ${canM[1].trim()} do?`;

    const words = s.split(/\s+/);
    const keyPhrase = words.slice(0, Math.min(5, words.length)).join(' ');
    return `Explain: ${keyPhrase}${words.length > 5 ? '...' : ''}`;
}

function extractTerm(text) {
    const quoted = text.match(/"([^"]+)"/);
    if (quoted) return quoted[1];
    const caps = text.match(/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/);
    if (caps) return caps[1];
    const isM = text.match(/^(.{3,40}?)\s+(?:is|are|means|refers?\s+to)\s+/i);
    if (isM) return isM[1].trim();
    const words = text.replace(/[^a-zA-Z0-9\s]/g, '').split(/\s+/).filter(w => w.length > 3);
    return words.slice(0, Math.min(3, words.length)).join(' ') || 'this concept';
}

function showToast(message, type = 'info') {
    const container = document.getElementById('toasts');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    toast.innerHTML = `<i class="fa-solid fa-${icons[type] || icons.info}"></i><span>${message}</span>`;
    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

function switchTab(tab) {
    state.currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.tab === tab);
    });
    document.getElementById('createView').classList.toggle('hidden', tab !== 'create');
    document.getElementById('libraryView').classList.toggle('hidden', tab !== 'library');
    document.getElementById('studyView').classList.toggle('hidden', tab !== 'study');

    if (tab === 'library') renderLibrary();
    if (tab === 'study') renderStudyView();
}

function setupPills(selector, stateKey) {
    document.querySelectorAll(selector).forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll(selector).forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const val = btn.dataset[stateKey];
            if (stateKey === 'count') state.cardCount = parseInt(val);
            else if (stateKey === 'diff') state.difficulty = val;
            else if (stateKey === 'style') state.cardStyle = val;
        });
    });
}

setupPills('[data-count]', 'count');
setupPills('[data-diff]', 'diff');
setupPills('[data-style]', 'style');

const fileInput = document.getElementById('fileInput');
const uploadZone = document.getElementById('uploadZone');

fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));

uploadZone.addEventListener('click', (e) => {
    if (e.target.tagName !== 'BUTTON') fileInput.click();
});
uploadZone.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', (e) => {
    e.preventDefault(); uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]);
});

async function handleFile(file) {
    if (!file) return;

    const status = document.getElementById('uploadStatus');
    status.textContent = 'Reading file...';
    status.classList.remove('hidden');

    try {
        let text = '';

        if (file.type === 'application/pdf' && typeof pdfjsLib !== 'undefined') {
            const buf = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: buf }).promise;
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const content = await page.getTextContent();
                text += content.items.map(it => it.str).join(' ') + '\n';
            }
        } else if (file.name.endsWith('.pdf') && typeof pdfjsLib === 'undefined') {
            status.textContent = 'PDF.js not loaded. Please paste content as text instead.';
            status.style.color = 'var(--warning)';
            return;
        } else {
            text = await file.text();
        }

        if (!text.trim()) {
            status.textContent = 'Could not extract text from this file. Try pasting content manually.';
            status.style.color = 'var(--warning)';
            return;
        }

        state.uploadedText = text.trim();
        state.uploadedFileName = file.name;
        status.textContent = `Loaded: ${file.name} (${text.trim().split(/\s+/).length} words)`;
        status.style.color = 'var(--accent)';
        showToast(`File "${file.name}" loaded successfully`, 'success');
    } catch (err) {
        status.textContent = `Error reading file: ${err.message}`;
        status.style.color = 'var(--danger)';
        showToast('Failed to read file', 'error');
    }
}

function renderLibrary(filter = '') {
    const decks = getDecks();
    const filtered = filter
        ? decks.filter(d => d.title.toLowerCase().includes(filter.toLowerCase()))
        : decks;

    const grid = document.getElementById('deckGrid');
    const empty = document.getElementById('emptyLibrary');

    if (filtered.length === 0) {
        grid.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }

    empty.classList.add('hidden');
    grid.innerHTML = filtered.map(deck => {
        const total = deck.cards.length;
        const mastered = deck.cards.filter(c => c.mastered).length;
        const masteryPct = total > 0 ? Math.round((mastered / total) * 100) : 0;
        const date = new Date(deck.createdAt).toLocaleDateString();
        return `
            <div class="deck-card" data-id="${deck.id}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-700 text-[15px] truncate">${esc(deck.title)}</h3>
                        <p class="text-xs mt-1 truncate text-c-60">${esc(deck.description)}</p>
                        <div class="flex items-center gap-3 mt-3 text-xs text-c-60">
                            <span><i class="fa-solid fa-layer-group"></i> ${total} cards</span>
                            <span><i class="fa-solid fa-trophy"></i> ${masteryPct}% mastered</span>
                            <span><i class="fa-solid fa-calendar"></i> ${date}</span>
                        </div>
                        <div class="progress-track mt-3" style="height:3px">
                            <div class="progress-fill" style="width:${masteryPct}%"></div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 shrink-0">
                        <button onclick="studyDeck('${deck.id}')" class="action-btn primary text-xs" style="padding:6px 12px">
                            <i class="fa-solid fa-play"></i> Study
                        </button>
                        <button onclick="exportDeck('${deck.id}')" class="action-btn text-xs" style="padding:6px 12px">
                            <i class="fa-solid fa-download"></i> Export
                        </button>
                        <button onclick="confirmDeleteDeck('${deck.id}')" class="action-btn danger text-xs" style="padding:6px 12px">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function studyDeck(id) {
    state.activeDeckId = id;
    state.studyIndex = 0;
    state.isFlipped = false;
    state.sessionCorrect = 0;
    state.sessionIncorrect = 0;
    switchTab('study');
}

function exportDeck(id) {
    const deck = getDeck(id);
    if (!deck) return;
    const blob = new Blob([JSON.stringify(deck, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = `${deck.title.replace(/[^a-zA-Z0-9]/g, '_')}.json`;
    a.click(); URL.revokeObjectURL(url);
    showToast(`Exported "${deck.title}"`, 'success');
}

function confirmDeleteDeck(id) {
    const deck = getDeck(id);
    if (!deck) return;
    const card = document.querySelector(`.deck-card[data-id="${id}"]`);
    if (!card) return;
    if (card.querySelector('.delete-confirm')) return;

    const overlay = document.createElement('div');
    overlay.className = 'delete-confirm';
    overlay.style.cssText = 'position:absolute;inset:0;background:rgba(10,12,16,0.92);border-radius:14px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;z-index:5;';
    overlay.innerHTML = `
        <p class="text-sm font-600 text-c">Delete "${esc(deck.title)}"?</p>
        <div class="flex gap-2">
            <button class="action-btn text-xs" onclick="this.closest('.delete-confirm').remove()">Cancel</button>
            <button class="action-btn danger text-xs" onclick="doDeleteDeck('${id}')">Delete</button>
        </div>
    `;
    card.style.position = 'relative';
    card.appendChild(overlay);
}

function doDeleteDeck(id) {
    deleteDeck(id);
    if (state.activeDeckId === id) state.activeDeckId = null;
    renderLibrary(document.getElementById('searchDecks').value);
    showToast('Deck deleted', 'info');
}

document.getElementById('importInput').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        try {
            const deck = JSON.parse(reader.result);
            if (!deck.cards || !Array.isArray(deck.cards) || !deck.title) {
                throw new Error('Invalid deck format');
            }
            deck.id = uid();
            deck.updatedAt = Date.now();
            saveDeck(deck);
            showToast(`Imported "${deck.title}"`, 'success');
            renderLibrary();
        } catch (err) {
            showToast('Invalid deck file', 'error');
        }
    };
    reader.readAsText(file);
});

function renderStudyView() {
    const deck = getDeck(state.activeDeckId);
    if (!deck) {
        document.getElementById('emptyStudy').classList.remove('hidden');
        document.getElementById('activeStudy').classList.add('hidden');
        return;
    }

    document.getElementById('emptyStudy').classList.add('hidden');
    document.getElementById('activeStudy').classList.remove('hidden');
    document.getElementById('studyDeckTitle').textContent = deck.title;
    document.getElementById('studyDeckMeta').textContent = `${deck.cards.length} cards • ${deck.description}`;
    renderCard();
}

function renderCard() {
    const deck = getDeck(state.activeDeckId);
    if (!deck || !deck.cards.length) return;
    const card = deck.cards[state.studyIndex];
    document.getElementById('cardQuestion').textContent = card.question;
    document.getElementById('cardAnswer').textContent = card.answer;
    document.getElementById('progressLabel').textContent = `Card ${state.studyIndex + 1} of ${deck.cards.length}`;
    const masteredCount = deck.cards.filter(c => c.mastered).length;
    document.getElementById('masteryLabel').textContent = `${Math.round((masteredCount / deck.cards.length) * 100)}% mastered`;
    document.getElementById('progressFill').style.width = `${(state.studyIndex / deck.cards.length) * 100}%`;
    document.getElementById('flashcardInner').classList.toggle('flipped', state.isFlipped);
}

function prevCard() {
    const deck = getDeck(state.activeDeckId);
    if (!deck) return;
    state.studyIndex = Math.max(0, state.studyIndex - 1);
    state.isFlipped = false;
    renderCard();
}

function nextCard() {
    const deck = getDeck(state.activeDeckId);
    if (!deck) return;
    state.studyIndex = Math.min(deck.cards.length - 1, state.studyIndex + 1);
    state.isFlipped = false;
    renderCard();
}

function rateCard(knowsIt) {
    const deck = getDeck(state.activeDeckId);
    if (!deck) return;
    const card = deck.cards[state.studyIndex];
    if (knowsIt) {
        card.correctCount += 1;
        card.mastered = true;
    } else {
        card.incorrectCount += 1;
        card.mastered = false;
    }
    saveDeck(deck);
    showToast(knowsIt ? 'Marked as known' : 'Marked for review', knowsIt ? 'success' : 'info');
    nextCard();
}

function exitStudy() {
    state.activeDeckId = null;
    state.studyIndex = 0;
    state.isFlipped = false;
    document.getElementById('studyView').classList.add('hidden');
    switchTab('library');
}

function esc(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function initFlashcardsPage() {
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => switchTab(button.dataset.tab));
    });

    document.getElementById('generateBtn').addEventListener('click', generateDeck);
    document.getElementById('searchDecks').addEventListener('input', (event) => renderLibrary(event.target.value));
    document.getElementById('flashcardWrap').addEventListener('click', () => {
        state.isFlipped = !state.isFlipped;
        document.getElementById('flashcardInner').classList.toggle('flipped', state.isFlipped);
    });
    document.addEventListener('keydown', (event) => {
        if (document.getElementById('studyView').classList.contains('hidden')) return;
        if (event.code === 'Space') {
            event.preventDefault();
            state.isFlipped = !state.isFlipped;
            document.getElementById('flashcardInner').classList.toggle('flipped', state.isFlipped);
        }
        if (event.code === 'ArrowLeft') prevCard();
        if (event.code === 'ArrowRight') nextCard();
    });

    renderLibrary();
}

function generateDeck() {
    const btn = document.getElementById('generateBtn');
    const hint = document.getElementById('genHint');
    let content = '';

    if (state.sourceType === 'upload') {
        content = state.uploadedText;
        if (!content) { showToast('Please upload a file first', 'error'); return; }
    } else {
        content = document.getElementById('sourceText').value.trim();
        if (!content) { showToast('Please enter your study content', 'error'); return; }
        if (content.length < 30) { showToast('Please enter more content (at least a few sentences)', 'error'); return; }
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Generating...';
    hint.textContent = 'Creating your flashcards...';

    setTimeout(() => {
        const cards = generateCardsFromText(content, state.cardCount, state.cardStyle);
        if (cards.length === 0) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-bolt"></i> Generate Flashcards';
            hint.innerHTML = '<i class="fa-solid fa-clock"></i> Takes a few seconds';
            showToast('Could not generate flashcards from this content. Try adding more text.', 'error');
            return;
        }

        const title = document.getElementById('deckTitle').value.trim() || 'Untitled Deck';
        const desc = document.getElementById('deckDesc').value.trim() || `${cards.length} flashcards generated from your content`;
        const deck = { id: uid(), title, description: desc, difficulty: state.difficulty, cardStyle: state.cardStyle, createdAt: Date.now(), updatedAt: Date.now(), cards };

        saveDeck(deck);
        state.activeDeckId = deck.id;
        state.studyIndex = 0;
        state.isFlipped = false;
        state.sessionCorrect = 0;
        state.sessionIncorrect = 0;

        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-bolt"></i> Generate Flashcards';
        hint.innerHTML = '<i class="fa-solid fa-clock"></i> Takes a few seconds';

        document.getElementById('deckTitle').value = '';
        document.getElementById('deckDesc').value = '';
        document.getElementById('sourceText').value = '';
        state.uploadedText = '';
        state.uploadedFileName = '';
        document.getElementById('uploadStatus').classList.add('hidden');
        fileInput.value = '';

        showToast(`Deck "${title}" created with ${cards.length} cards`, 'success');
        switchTab('study');
    }, 1200 + Math.random() * 800);
}

initFlashcardsPage();
</script>
@endsection
