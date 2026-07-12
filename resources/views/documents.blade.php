@extends('layouts.dashboard-layout')

@section('title', 'My Documents — SHEELEARN AI')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #010615;
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
        --danger-dim: rgba(239,68,68,0.1);
        --warning: #f59e0b;
        --warning-dim: rgba(245,158,11,0.1);
    }
    * { box-sizing: border-box; }
    body { background: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }
    .dashboard-documents { width: 100%; }

    .tab-btn {
        padding: 8px 18px; border-radius: 10px; font-size: 14px; font-weight: 600;
        color: var(--text-muted); transition: all 0.2s; border: 1px solid transparent;
        cursor: pointer; background: transparent; display: inline-flex; align-items: center; gap: 7px;
    }
    .tab-btn:hover { color: var(--text); background: var(--surface-2); }
    .tab-btn.active { color: var(--accent); background: var(--accent-dim); border-color: var(--accent-border); }

    .pill {
        padding: 7px 16px; border-radius: 9px; font-size: 13px; font-weight: 600;
        border: 1px solid var(--border); background: var(--surface); cursor: pointer;
        transition: all 0.2s; color: var(--text-muted); white-space: nowrap;
    }
    .pill:hover { border-color: var(--border-hover); color: var(--text); }
    .pill.active { background: var(--accent-dim); border-color: var(--accent-border); color: var(--accent); }

    .field {
        width: 100%; background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 10px; padding: 10px 14px; font-size: 14px; color: var(--text);
        font-family: 'Outfit', sans-serif; transition: border-color 0.2s; outline: none;
    }
    .field:focus { border-color: var(--accent-border); }
    .field::placeholder { color: var(--text-dim); }

    .dropzone {
        border: 1.5px dashed var(--border-hover); border-radius: 16px; padding: 48px 32px;
        text-align: center; cursor: pointer; transition: all 0.25s; min-height: 200px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .dropzone:hover, .dropzone.dragover { border-color: var(--accent-border); background: var(--accent-dim); }

    .form-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 24px; }
    .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-dim); margin-bottom: 12px; }

    .gen-btn {
        padding: 13px 36px; border-radius: 12px; font-size: 15px; font-weight: 700;
        background: var(--accent); color: #021a0f; border: none; cursor: pointer;
        transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
    }
    .gen-btn:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(34,211,238,0.3); }

    .action-btn {
        padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 600;
        border: 1px solid var(--border); background: var(--surface-2); color: var(--text);
        cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
    }
    .action-btn:hover { border-color: var(--border-hover); background: var(--surface-3); }
    .action-btn.primary { background: var(--accent-dim); border-color: var(--accent-border); color: var(--accent); }
    .action-btn.primary:hover { background: var(--accent); color: #021a0f; }
    .action-btn.danger { color: var(--danger); }
    .action-btn.danger:hover { background: var(--danger-dim); border-color: rgba(239,68,68,0.3); }
    .action-btn.sm { padding: 6px 12px; font-size: 12px; }

    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; text-align: center; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-top: 6px; color: var(--text-dim); }

    .sidebar-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; }

    .toast { padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 8px 24px rgba(0,0,0,0.4); max-width: 360px; }
    .toast.show { transform: translateX(0); }
    .toast-success { background: #065f46; color: #a7f3d0; border: 1px solid rgba(34,211,238,0.3); }
    .toast-error { background: #7f1d1d; color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .toast-info { background: #1e3a5f; color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }

    .week-bar { flex: 1; border-radius: 4px 4px 0 0; transition: height 0.4s ease; min-height: 4px; }

    /* Document cards */
    .doc-card {
        background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
        padding: 20px; transition: all 0.25s; cursor: pointer; position: relative;
    }
    .doc-card:hover { border-color: var(--border-hover); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
    .doc-card.selected { border-color: var(--accent-border); background: var(--accent-dim); }

    .file-icon {
        width: 44px; height: 44px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
    }
    .file-icon.pdf { background: rgba(248,113,113,0.1); color: #f87171; }
    .file-icon.doc { background: rgba(59,130,246,0.1); color: #60a5fa; }
    .file-icon.txt { background: rgba(148,163,184,0.08); color: #94a3b8; }
    .file-icon.ppt { background: rgba(251,191,36,0.1); color: #fbbf24; }
    .file-icon.xls { background: rgba(34,211,238,0.1); color: #22d3ee; }
    .file-icon.img { background: rgba(236,72,153,0.1); color: #f472b6; }
    .file-icon.folder { background: var(--warning-dim); color: var(--warning); }
    .file-icon.generic { background: var(--accent-dim); color: var(--accent); }

    .confirm-overlay { position: absolute; inset: 0; background: rgba(10,12,16,0.94); border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; z-index: 5; }

    /* Storage bar */
    .storage-track { height: 6px; border-radius: 99px; background: var(--surface-3); overflow: hidden; }
    .storage-fill { height: 100%; border-radius: 99px; background: var(--accent); transition: width 0.6s ease; }

    .view { animation: fadeUp 0.3s ease; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 1023px) { .main-grid { grid-template-columns: 1fr !important; } }
</style>
@endsection

@section('content')
<div class="dashboard-documents">

<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
<input type="file" id="fileInput" class="hidden" multiple accept=".txt,.pdf,.md,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.png,.jpg,.jpeg,.webp">

<!-- Header -->
<header class="pt-6 pb-5 px-1">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-800 tracking-tight">My Documents</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Store, organize, and manage your study materials</p>
        </div>
        <nav class="flex gap-2 bg-[var(--surface)] p-1.5 rounded-xl w-fit">
            <button class="tab-btn active" data-tab="files"><i class="fa-solid fa-folder-open text-xs"></i> All Files</button>
            <button class="tab-btn" data-tab="favorites"><i class="fa-solid fa-star text-xs"></i> Favorites</button>
        </nav>
    </div>
</header>

<!-- Two-Column Grid -->
<div class="main-grid grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 pb-8">

    <!-- ====== LEFT: Main Content ====== -->
    <div class="min-w-0 space-y-5">

        <!-- Upload Zone -->
        <div id="uploadZone" class="dropzone" onclick="document.getElementById('fileInput').click()">
            <i class="fa-solid fa-cloud-arrow-up text-4xl mb-4" style="color:var(--accent)"></i>
            <p class="text-base font-600" style="color:var(--text)">Drag and drop files here</p>
            <p class="text-sm mt-1" style="color:var(--text-dim)">or click to browse · PDF, DOCX, TXT, Images, and more</p>
            <button type="button" class="mt-5 px-6 py-2.5 rounded-lg text-sm font-600 transition" style="background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-border);pointer-events:none">Browse Files</button>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--text-dim)"></i>
                <input id="searchInput" type="text" class="field pl-9" placeholder="Search documents..." oninput="renderDocs()">
            </div>
            <div class="flex gap-2 flex-wrap">
                <button class="pill active" data-filter="all" onclick="setFilter(this)">All</button>
                <button class="pill" data-filter="pdf" onclick="setFilter(this)">PDF</button>
                <button class="pill" data-filter="doc" onclick="setFilter(this)">Docs</button>
                <button class="pill" data-filter="txt" onclick="setFilter(this)">Text</button>
                <button class="pill" data-filter="img" onclick="setFilter(this)">Images</button>
            </div>
            <div class="flex gap-2">
                <select id="sortSelect" class="field" style="width:auto;padding:7px 12px;font-size:13px" onchange="renderDocs()">
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                    <option value="name">Name A-Z</option>
                    <option value="size">Largest</option>
                </select>
                <button id="addFolderBtn" class="action-btn sm" onclick="showFolderInput()"><i class="fa-solid fa-folder-plus"></i> Folder</button>
            </div>
        </div>

        <!-- Folder creation inline -->
        <div id="folderInput" class="hidden form-panel" style="padding:16px">
            <div class="flex gap-3">
                <input id="folderNameInput" type="text" class="field" placeholder="Folder name..." style="flex:1">
                <button class="action-btn primary" onclick="createFolder()">Create</button>
                <button class="action-btn" onclick="hideFolderInput()">Cancel</button>
            </div>
        </div>

        <!-- Documents Grid -->
        <div id="docsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-20">
            <i class="fa-solid fa-inbox text-5xl mb-4" style="color:var(--text-dim)"></i>
            <p class="text-lg font-600" style="color:var(--text-muted)">No documents found</p>
            <p class="text-sm mt-1" style="color:var(--text-dim)">Upload files or create a folder to get started.</p>
        </div>
    </div>

    <!-- ====== RIGHT: Sidebar ====== -->
    <aside class="space-y-5">

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-3">
            <div class="stat-card"><p id="sbTotal" class="stat-value" style="color:var(--accent)">0</p><p class="stat-label">Documents</p></div>
            <div class="stat-card"><p id="sbSize" class="stat-value" style="color:var(--warning)">0</p><p class="stat-label">Total Size</p></div>
            <div class="stat-card"><p id="sbAI" class="stat-value" style="color:#818cf8">0</p><p class="stat-label">AI Ready</p></div>
            <div class="stat-card"><p id="sbFavs" class="stat-value" style="color:#f472b6">0</p><p class="stat-label">Favorites</p></div>
        </div>

        <!-- Storage -->
        <div class="sidebar-panel">
            <p class="section-label">Storage</p>
            <div class="flex justify-between text-sm mb-2">
                <span id="storageUsed" class="font-600" style="color:var(--text)">0 KB</span>
                <span style="color:var(--text-dim)">of 50 MB</span>
            </div>
            <div class="storage-track"><div id="storageBar" class="storage-fill" style="width:0%"></div></div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-xs" style="color:var(--text-dim)">
                <div><span id="sbPDF" class="block text-sm font-700" style="color:#f87171">0</span>PDF</div>
                <div><span id="sbDOC" class="block text-sm font-700" style="color:#60a5fa">0</span>Docs</div>
                <div><span id="sbIMG" class="block text-sm font-700" style="color:#f472b6">0</span>Images</div>
            </div>
        </div>

        <!-- Selected Document Actions -->
        <div id="selectedPanel" class="sidebar-panel hidden">
            <p class="section-label">Selected Document</p>
            <div class="flex items-center gap-3 mb-4">
                <div id="selIcon" class="file-icon generic"><i class="fa-solid fa-file"></i></div>
                <div class="flex-1 min-w-0">
                    <p id="selName" class="text-sm font-600 truncate"></p>
                    <p id="selMeta" class="text-xs" style="color:var(--text-dim)"></p>
                </div>
            </div>
            <div class="space-y-2">
                <button onclick="previewSelected()" class="action-btn" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-eye"></i> Preview Text</button>
                <button onclick="downloadSelected()" class="action-btn" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-download"></i> Download Text</button>
                <button onclick="copySelectedText()" class="action-btn" style="width:100%;justify-content:flex-start"><i class="fa-regular fa-copy"></i> Copy Text</button>
                <button onclick="toggleFavSelected()" class="action-btn" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-star"></i> <span id="selFavLabel">Favorite</span></button>
            </div>
            <div style="border-top:1px solid var(--border);margin-top:12px;padding-top:12px">
                <p class="section-label">AI Actions</p>
                <div class="space-y-2">
                    <button onclick="aiAction('summary')" class="action-btn primary" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-align-left"></i> Summarize</button>
                    <button onclick="aiAction('flashcards')" class="action-btn primary" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-layer-group"></i> Flashcards</button>
                    <button onclick="aiAction('quiz')" class="action-btn primary" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-clipboard-check"></i> Quiz</button>
                </div>
            </div>
        </div>

        <!-- Quick Actions (shown when no doc selected) -->
        <div id="quickPanel" class="sidebar-panel">
            <p class="section-label">Quick Actions</p>
            <div class="space-y-2">
                <button onclick="document.getElementById('fileInput').click()" class="action-btn primary" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Files</button>
                <button onclick="showFolderInput()" class="action-btn" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-folder-plus"></i> New Folder</button>
                <button onclick="addTextNote()" class="action-btn" style="width:100%;justify-content:flex-start"><i class="fa-solid fa-pen"></i> Add Text Note</button>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="sidebar-panel">
            <p class="section-label">Recent Activity</p>
            <div id="activityList" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                <p class="text-xs text-center py-4" style="color:var(--text-dim)">No activity yet</p>
            </div>
        </div>
    </aside>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)" onclick="closePreview(event)">
    <div class="max-w-2xl mx-auto mt-20 mx-4 form-panel" style="max-height:70vh;overflow-y:auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 id="previewTitle" class="text-lg font-700"></h3>
            <button onclick="closePreview()" class="action-btn sm"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <pre id="previewText" class="text-sm leading-relaxed" style="color:var(--text-muted);white-space:pre-wrap;word-break:break-word;font-family:'Outfit',sans-serif"></pre>
    </div>
</div>

<!-- Text Note Modal -->
<div id="noteModal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px)" onclick="closeNoteModal(event)">
    <div class="max-w-lg mx-auto mt-20 mx-4 form-panel" onclick="event.stopPropagation()">
        <h3 class="text-lg font-700 mb-4">Add Text Note</h3>
        <input id="noteTitle" type="text" class="field mb-3" placeholder="Note title...">
        <textarea id="noteContent" class="field" style="min-height:150px" placeholder="Write or paste your note content here..."></textarea>
        <div class="flex gap-3 mt-4">
            <button onclick="saveTextNote()" class="gen-btn" style="padding:10px 24px;font-size:14px">Save Note</button>
            <button onclick="closeNoteModal()" class="action-btn">Cancel</button>
        </div>
    </div>
</div>

</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
if (typeof pdfjsLib !== 'undefined') { pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'; }

const state = {
    currentTab: 'files',
    currentFilter: 'all',
    selectedId: null,
    docs: [],
    activity: JSON.parse(localStorage.getItem('doc_activity') || '[]'),
};

// Storage
function getDocs() { try { return JSON.parse(localStorage.getItem('doc_files') || '[]'); } catch { return []; } }
function saveDocs(d) { try { localStorage.setItem('doc_files', JSON.stringify(d)); } catch(e) { showToast('Storage full. Delete some files.', 'error'); } }
function uid() { return 'f' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function logActivity(text) { state.activity.unshift({ text, time: Date.now() }); if (state.activity.length > 20) state.activity.length = 20; try { localStorage.setItem('doc_activity', JSON.stringify(state.activity)); } catch(e){} }

// Toast
function showToast(msg, type='info') {
    const c = document.getElementById('toasts'), t = document.createElement('div');
    t.className = `toast toast-${type}`;
    const icons = {success:'check-circle',error:'exclamation-circle',info:'info-circle'};
    t.innerHTML = `<i class="fa-solid fa-${icons[type]||icons.info}"></i><span>${msg}</span>`;
    c.appendChild(t); requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
}

// Tabs
function switchTab(tab) {
    state.currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    renderDocs();
}
document.querySelectorAll('.tab-btn').forEach(btn => { btn.addEventListener('click', () => switchTab(btn.dataset.tab)); });

// Filter
function setFilter(el) {
    document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
    el.classList.add('active'); state.currentFilter = el.dataset.filter; renderDocs();
}

// File type helpers
function getFileCategory(name) {
    const ext = name.split('.').pop().toLowerCase();
    if (ext === 'pdf') return 'pdf';
    if (['doc','docx'].includes(ext)) return 'doc';
    if (['ppt','pptx'].includes(ext)) return 'ppt';
    if (['xls','xlsx','csv'].includes(ext)) return 'xls';
    if (['png','jpg','jpeg','webp','gif'].includes(ext)) return 'img';
    if (['txt','md'].includes(ext)) return 'txt';
    return 'generic';
}
function getFileIconClass(cat) {
    const m = {pdf:'pdf',doc:'doc',ppt:'ppt',xls:'xls',img:'img',txt:'txt',folder:'folder'};
    return m[cat] || 'generic';
}
function getFileIconFA(cat) {
    const m = {pdf:'fa-file-pdf',doc:'fa-file-word',ppt:'fa-file-powerpoint',xls:'fa-file-excel',img:'fa-file-image',txt:'fa-file-lines',folder:'fa-folder'};
    return m[cat] || 'fa-file';
}
function formatSize(bytes) { if (bytes < 1024) return bytes + ' B'; if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB'; return (bytes/1048576).toFixed(1) + ' MB'; }
function formatDate(ts) { return new Date(ts).toLocaleDateString(); }

// ── Upload Handling ──
const fileInput = document.getElementById('fileInput');
const uploadZone = document.getElementById('uploadZone');

fileInput.addEventListener('change', () => handleFiles(fileInput.files));

uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => {
    e.preventDefault(); uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) handleFiles(e.dataTransfer.files);
});

async function handleFiles(files) {
    if (!files || !files.length) return;
    const docs = getDocs();
    let added = 0;

    for (const file of files) {
        const cat = getFileCategory(file.name);
        const doc = {
            id: uid(), name: file.name, type: cat, size: file.size,
            createdAt: Date.now(), favorite: false, folder: false,
            textContent: '', hasText: false,
        };

        try {
            let text = '';
            if (file.type === 'application/pdf' && typeof pdfjsLib !== 'undefined') {
                const buf = await file.arrayBuffer(), pdf = await pdfjsLib.getDocument({data:buf}).promise;
                for (let i=1;i<=pdf.numPages;i++) { const pg = await pdf.getPage(i), ct = await pg.getTextContent(); text += ct.items.map(it=>it.str).join(' ')+'\n'; }
            } else if (['text/plain','text/markdown','text/csv','application/csv'].includes(file.type) || cat === 'txt') {
                text = await file.text();
            }
            if (text.trim()) { doc.textContent = text.trim(); doc.hasText = true; }
        } catch(e) { /* silently skip text extraction */ }

        docs.unshift(doc);
        added++;
        logActivity(`Uploaded ${file.name}`);
    }

    saveDocs(docs);
    showToast(`${added} file${added>1?'s':''} uploaded successfully`, 'success');
    fileInput.value = '';
    renderDocs();
    updateSidebar();
}

// ── Folders ──
function showFolderInput() { document.getElementById('folderInput').classList.remove('hidden'); document.getElementById('folderNameInput').focus(); }
function hideFolderInput() { document.getElementById('folderInput').classList.add('hidden'); document.getElementById('folderNameInput').value = ''; }
function createFolder() {
    const name = document.getElementById('folderNameInput').value.trim();
    if (!name) { showToast('Enter a folder name', 'error'); return; }
    const docs = getDocs();
    docs.unshift({ id: uid(), name: name, type: 'folder', size: 0, createdAt: Date.now(), favorite: false, folder: true, textContent: '', hasText: false });
    saveDocs(docs); hideFolderInput(); renderDocs(); updateSidebar();
    logActivity(`Created folder "${name}"`);
    showToast(`Folder "${name}" created`, 'success');
}

// ── Text Notes ──
function addTextNote() { document.getElementById('noteModal').classList.remove('hidden'); }
function closeNoteModal(e) { if (e && e.target !== e.currentTarget) return; document.getElementById('noteModal').classList.add('hidden'); }
function saveTextNote() {
    const title = document.getElementById('noteTitle').value.trim() || 'Untitled Note';
    const content = document.getElementById('noteContent').value.trim();
    if (!content) { showToast('Enter some content', 'error'); return; }
    const docs = getDocs();
    docs.unshift({ id: uid(), name: title + '.txt', type: 'txt', size: new Blob([content]).size, createdAt: Date.now(), favorite: false, folder: false, textContent: content, hasText: true });
    saveDocs(docs); document.getElementById('noteTitle').value = ''; document.getElementById('noteContent').value = '';
    closeNoteModal(); renderDocs(); updateSidebar();
    logActivity(`Added note "${title}"`);
    showToast(`Note "${title}" saved`, 'success');
}

// ── Render Documents ──
function renderDocs() {
    const docs = getDocs();
    const search = document.getElementById('searchInput').value.toLowerCase();
    const sort = document.getElementById('sortSelect').value;
    const filter = state.currentFilter;
    const tab = state.currentTab;

    let filtered = docs.filter(d => {
        if (tab === 'favorites' && !d.favorite) return false;
        if (filter !== 'all' && d.type !== filter) return false;
        if (search && !d.name.toLowerCase().includes(search)) return false;
        return true;
    });

    // Sort
    if (sort === 'newest') filtered.sort((a,b) => b.createdAt - a.createdAt);
    else if (sort === 'oldest') filtered.sort((a,b) => a.createdAt - b.createdAt);
    else if (sort === 'name') filtered.sort((a,b) => a.name.localeCompare(b.name));
    else if (sort === 'size') filtered.sort((a,b) => b.size - a.size);

    const grid = document.getElementById('docsGrid');
    const empty = document.getElementById('emptyState');

    if (!filtered.length) { grid.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    grid.innerHTML = filtered.map(doc => {
        const cat = doc.type;
        const iconCls = getFileIconClass(cat);
        const iconFA = getFileIconFA(cat);
        const selected = state.selectedId === doc.id ? 'selected' : '';
        return `<div class="doc-card ${selected}" data-id="${doc.id}" onclick="selectDoc('${doc.id}')">
            <div class="flex items-start gap-3 mb-3">
                <div class="file-icon ${iconCls}"><i class="fa-solid ${iconFA}"></i></div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-600 truncate" style="color:var(--text)">${esc(doc.name)}</p>
                    <p class="text-xs mt-0.5" style="color:var(--text-dim)">${doc.folder ? 'Folder' : cat.toUpperCase()} · ${doc.size > 0 ? formatSize(doc.size) : '—'} · ${formatDate(doc.createdAt)}</p>
                </div>
                ${doc.favorite ? '<i class="fa-solid fa-star text-xs" style="color:var(--warning)"></i>' : ''}
                ${doc.hasText ? '<i class="fa-solid fa-brain text-xs" style="color:var(--accent)" title="AI Ready"></i>' : ''}
            </div>
            <div class="flex gap-2">
                ${doc.hasText ? `<button onclick="event.stopPropagation();previewDoc('${doc.id}')" class="action-btn sm"><i class="fa-solid fa-eye"></i> Preview</button>` : ''}
                ${doc.hasText ? `<button onclick="event.stopPropagation();downloadDoc('${doc.id}')" class="action-btn sm"><i class="fa-solid fa-download"></i></button>` : ''}
                <button onclick="event.stopPropagation();toggleFav('${doc.id}')" class="action-btn sm" style="color:${doc.favorite?'var(--warning)':''}"><i class="fa-solid fa-star"></i></button>
                <button onclick="event.stopPropagation();confirmDelete('${doc.id}')" class="action-btn sm danger"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

// ── Select Document ──
function selectDoc(id) {
    state.selectedId = state.selectedId === id ? null : id;
    renderDocs();
    updateSelectedPanel();
}

function updateSelectedPanel() {
    const selPanel = document.getElementById('selectedPanel');
    const quickPanel = document.getElementById('quickPanel');
    if (!state.selectedId) { selPanel.classList.add('hidden'); quickPanel.classList.remove('hidden'); return; }
    const docs = getDocs();
    const doc = docs.find(d => d.id === state.selectedId);
    if (!doc) { selPanel.classList.add('hidden'); quickPanel.classList.remove('hidden'); return; }

    selPanel.classList.remove('hidden'); quickPanel.classList.add('hidden');

    const cat = doc.type;
    document.getElementById('selIcon').className = `file-icon ${getFileIconClass(cat)}`;
    document.getElementById('selIcon').innerHTML = `<i class="fa-solid ${getFileIconFA(cat)}"></i>`;
    document.getElementById('selName').textContent = doc.name;
    document.getElementById('selMeta').textContent = `${cat.toUpperCase()} · ${doc.size > 0 ? formatSize(doc.size) : '—'} · ${formatDate(doc.createdAt)}`;
    document.getElementById('selFavLabel').textContent = doc.favorite ? 'Unfavorite' : 'Favorite';
}

// ── Actions ──
function toggleFav(id) {
    const docs = getDocs(); const doc = docs.find(d => d.id === id); if (!doc) return;
    doc.favorite = !doc.favorite; saveDocs(docs); renderDocs(); updateSelectedPanel(); updateSidebar();
    showToast(doc.favorite ? 'Added to favorites' : 'Removed from favorites', 'info');
    logActivity(`${doc.favorite?'Favorited':'Unfavorited'} ${doc.name}`);
}
function toggleFavSelected() { if (state.selectedId) toggleFav(state.selectedId); }

function confirmDelete(id) {
    const card = document.querySelector(`.doc-card[data-id="${id}"]`); if (!card || card.querySelector('.confirm-overlay')) return;
    const docs = getDocs(); const doc = docs.find(d => d.id === id);
    const overlay = document.createElement('div'); overlay.className = 'confirm-overlay';
    overlay.innerHTML = `<p class="text-sm font-600" style="color:var(--text)">Delete "${esc(doc?.name||'this')}?</p><div class="flex gap-2"><button class="action-btn text-xs" onclick="this.closest('.confirm-overlay').remove()">Cancel</button><button class="action-btn danger text-xs" onclick="doDelete('${id}')">Delete</button></div>`;
    card.appendChild(overlay);
}
function doDelete(id) {
    let docs = getDocs(); const doc = docs.find(d => d.id === id);
    const name = doc?.name || 'Document'; docs = docs.filter(d => d.id !== id); saveDocs(docs);
    if (state.selectedId === id) state.selectedId = null;
    renderDocs(); updateSelectedPanel(); updateSidebar();
    logActivity(`Deleted ${name}`);
    showToast(`"${name}" deleted`, 'info');
}

function previewDoc(id) {
    const docs = getDocs(); const doc = docs.find(d => d.id === id);
    if (!doc || !doc.hasText) { showToast('No text content available for preview', 'error'); return; }
    document.getElementById('previewTitle').textContent = doc.name;
    document.getElementById('previewText').textContent = doc.textContent;
    document.getElementById('previewModal').classList.remove('hidden');
}
function previewSelected() { if (state.selectedId) previewDoc(state.selectedId); }
function closePreview(e) { if (e && e.target !== e.currentTarget) return; document.getElementById('previewModal').classList.add('hidden'); }

function downloadDoc(id) {
    const docs = getDocs(); const doc = docs.find(d => d.id === id);
    if (!doc || !doc.hasText) { showToast('No text content to download', 'error'); return; }
    const blob = new Blob([doc.textContent], {type:'text/plain'});
    const url = URL.createObjectURL(blob); const a = document.createElement('a');
    a.href = url; a.download = doc.name.replace(/\.[^.]+$/, '') + '_text.txt';
    a.click(); URL.revokeObjectURL(url);
    showToast(`Downloaded text for "${doc.name}"`, 'success');
}
function downloadSelected() { if (state.selectedId) downloadDoc(state.selectedId); }

function copySelectedText() {
    if (!state.selectedId) return;
    const docs = getDocs(); const doc = docs.find(d => d.id === state.selectedId);
    if (!doc || !doc.hasText) { showToast('No text content to copy', 'error'); return; }
    navigator.clipboard.writeText(doc.textContent).then(() => showToast('Text copied to clipboard', 'success')).catch(() => showToast('Failed to copy', 'error'));
}

function aiAction(type) {
    if (!state.selectedId) { showToast('Select a document first', 'info'); return; }
    const docs = getDocs(); const doc = docs.find(d => d.id === state.selectedId);
    if (!doc) return;
    if (!doc.hasText) { showToast('This file has no extractable text. Try a PDF or TXT file.', 'error'); return; }

    // Copy text to clipboard for use in other tools
    const labels = { summary: 'Summarizer', flashcards: 'Flashcards', quiz: 'Quizzes' };
    navigator.clipboard.writeText(doc.textContent).then(() => {
        showToast(`Text copied! Open ${labels[type]} and paste to generate.`, 'success');
    }).catch(() => {
        showToast(`Open ${labels[type]} and use this document.`, 'info');
    });
    logActivity(`Used ${doc.name} for ${labels[type]}`);
}

// ── Sidebar ──
function updateSidebar() {
    const docs = getDocs();
    const totalSize = docs.reduce((s,d) => s + d.size, 0);
    const aiReady = docs.filter(d => d.hasText).length;
    const favs = docs.filter(d => d.favorite).length;
    const pdfs = docs.filter(d => d.type === 'pdf').length;
    const docTypes = docs.filter(d => d.type === 'doc').length;
    const imgs = docs.filter(d => d.type === 'img').length;

    document.getElementById('sbTotal').textContent = docs.length;
    document.getElementById('sbSize').textContent = formatSize(totalSize);
    document.getElementById('sbAI').textContent = aiReady;
    document.getElementById('sbFavs').textContent = favs;
    document.getElementById('sbPDF').textContent = pdfs;
    document.getElementById('sbDOC').textContent = docTypes;
    document.getElementById('sbIMG').textContent = imgs;

    document.getElementById('storageUsed').textContent = formatSize(totalSize);
    const pct = Math.min(100, (totalSize / (50 * 1024 * 1024)) * 100);
    document.getElementById('storageBar').style.width = pct + '%';

    // Activity
    const actList = document.getElementById('activityList');
    if (!state.activity.length) { actList.innerHTML = '<p class="text-xs text-center py-4" style="color:var(--text-dim)">No activity yet</p>'; }
    else {
        actList.innerHTML = state.activity.slice(0, 8).map(a => {
            const ago = getTimeAgo(a.time);
            return `<div class="flex items-start gap-2 text-xs"><i class="fa-solid fa-circle text-[4px] mt-1.5" style="color:var(--accent)"></i><div class="flex-1"><p style="color:var(--text-muted)">${esc(a.text)}</p><p style="color:var(--text-dim)">${ago}</p></div></div>`;
        }).join('');
    }
}

function getTimeAgo(ts) {
    const diff = Date.now() - ts;
    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff/60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff/3600000) + 'h ago';
    return Math.floor(diff/86400000) + 'd ago';
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    renderDocs();
    updateSidebar();
});
</script>
@endsection