@extends('layouts.dashboard-layout')

@section('title', 'Notes — SHEELEARN AI')

@section('styles')
<style>
    :root {
        --bg: #020617;
        --surface: rgba(15,23,42,0.65);
        --surface-2: rgba(15,23,42,0.52);
        --surface-3: rgba(15,23,42,0.38);
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
        --success: #10b981;
    }
    body { background: var(--bg); color: var(--text); }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 18px;
        transition: all 0.25s;
    }
    .stat-card:hover { border-color: var(--border-hover); transform: translateY(-2px); }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; margin-bottom: 12px;
    }
    .stat-value { font-size: 24px; font-weight: 800; line-height: 1; }
    .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }

    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px;
    }

    .pill {
        padding: 7px 14px; border-radius: 9px; font-size: 13px; font-weight: 600;
        border: 1px solid var(--border); background: var(--surface); cursor: pointer;
        transition: all 0.2s; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px;
    }
    .pill:hover { border-color: var(--border-hover); color: var(--text); }
    .pill.active { background: var(--accent-dim); border-color: var(--accent-border); color: var(--accent); }

    .field {
        width: 100%; background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 10px; padding: 10px 14px; font-size: 14px; color: var(--text);
        font-family: 'Inter', system-ui, sans-serif; transition: border-color 0.2s; outline: none;
    }
    .field:focus { border-color: var(--accent-border); }
    .field::placeholder { color: var(--text-dim); }
    textarea.field { resize: vertical; min-height: 240px; line-height: 1.6; }

    .note-item {
        background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
        padding: 16px 18px; cursor: pointer; transition: all 0.2s; position: relative;
    }
    .note-item:hover { border-color: var(--border-hover); transform: translateY(-2px); }
    .note-item.active { border-color: var(--accent-border); background: var(--accent-dim); }
    
    .note-actions { 
        position: absolute; top: 12px; right: 12px; display: flex; gap: 4px;
        opacity: 0; transition: opacity 0.2s;
    }
    .note-item:hover .note-actions { opacity: 1; }
    
    .icon-btn {
        width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center;
        justify-content: center; background: var(--surface-2); border: 1px solid var(--border);
        color: var(--text-muted); cursor: pointer; transition: all 0.2s; font-size: 12px;
    }
    .icon-btn:hover { border-color: var(--border-hover); color: var(--text); }
    .icon-btn.danger:hover { background: var(--danger-dim); border-color: rgba(239,68,68,0.3); color: var(--danger); }
    .icon-btn.fav.active { color: var(--warning); background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3); }
    .icon-btn.pin.active { color: var(--accent); background: var(--accent-dim); border-color: var(--accent-border); }

    .folder-item {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 12px 14px; border-radius: 10px; cursor: pointer; transition: all 0.2s;
        background: var(--surface-2); border: 1px solid var(--border);
    }
    .folder-item:hover { border-color: var(--border-hover); }
    .folder-item.active { border-color: var(--accent-border); background: var(--accent-dim); }

    .gen-btn {
        padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 700;
        background: var(--accent); color: #021a0f; border: none; cursor: pointer;
        transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
    }
    .gen-btn i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .gen-btn:hover { background: var(--accent-hover); transform: translateY(-1px); }

    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
        z-index: 100; display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: opacity 0.2s;
    }
    .modal-overlay.show { opacity: 1; pointer-events: auto; }
    .modal-content {
        background: var(--surface); border: 1px solid var(--border); border-radius: 18px;
        padding: 24px; width: 90%; max-width: 600px; transform: translateY(20px);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .modal-overlay.show .modal-content { transform: translateY(0); }

    .toast {
        padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 10px; transform: translateX(120%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 24px rgba(0,0,0,0.4); max-width: 360px;
    }
    .toast.show { transform: translateX(0); }
    .toast-success { background: #065f46; color: #a7f3d0; border: 1px solid rgba(16,185,129,0.3); }
    .toast-error { background: #7f1d1d; color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .toast-info { background: #1e3a5f; color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }

    .empty-state {
        text-align: center; padding: 40px 20px; color: var(--text-dim);
    }
    .empty-state i { font-size: 32px; margin-bottom: 12px; display: block; color: var(--text-dim); }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .view { animation: fadeUp 0.3s ease; }

    @media (max-width: 1024px) {
        .main-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endsection

@section('content')
<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<!-- Create/Edit Note Modal -->
<div id="noteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold" id="modalTitle">New Note</h3>
            <button onclick="closeNoteModal()" class="icon-btn"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <input type="hidden" id="noteId">
        <div class="space-y-4">
            <div>
                <label class="text-xs font-bold uppercase tracking-widest block mb-2" style="color:var(--text-dim)">Title</label>
                <input id="noteTitle" type="text" class="field" placeholder="Note title...">
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-widest block mb-2" style="color:var(--text-dim)">Folder</label>
                <select id="noteFolder" class="field">
                    <option value="">No Folder</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-bold uppercase tracking-widest block mb-2" style="color:var(--text-dim)">Content</label>
                <textarea id="noteContent" class="field" placeholder="Start writing your notes here..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button onclick="closeNoteModal()" class="pill">Cancel</button>
                <button onclick="saveNote()" class="gen-btn"><i class="fa-solid fa-check"></i> Save Note</button>
            </div>
        </div>
    </div>
</div>

<!-- Folder Modal -->
<div id="folderModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold">New Folder</h3>
            <button onclick="closeFolderModal()" class="icon-btn"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="space-y-4">
            <input id="folderName" type="text" class="field" placeholder="Folder name...">
            <div class="flex justify-end gap-3">
                <button onclick="closeFolderModal()" class="pill">Cancel</button>
                <button onclick="saveFolder()" class="gen-btn"><i class="fa-solid fa-check"></i> Create</button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto space-y-6 view">
    
    <!-- Header -->
    <div class="pt-8 pb-2">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color:var(--text-dim)">Workspace</p>
                <h1 class="text-3xl font-black mt-2">Notes</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Create, organize, and manage all your study notes.</p>
            </div>
            <button onclick="openNoteModal()" class="gen-btn">
                <i class="fa-solid fa-plus"></i> New Note
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(34,211,238,0.1);color:var(--accent);"><i class="fa-solid fa-note-sticky"></i></div>
            <p class="stat-value" id="statTotal">0</p>
            <p class="stat-label">Total Notes</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(129,140,248,0.1);color:#818cf8;"><i class="fa-solid fa-folder"></i></div>
            <p class="stat-value" id="statFolders">0</p>
            <p class="stat-label">Folders</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;"><i class="fa-solid fa-star"></i></div>
            <p class="stat-value" id="statFavorites">0</p>
            <p class="stat-label">Favorites</p>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981;"><i class="fa-solid fa-thumbtack"></i></div>
            <p class="stat-value" id="statPinned">0</p>
            <p class="stat-label">Pinned</p>
        </div>
    </div>

    <!-- Main Grid: Notes List + Sidebar -->
    <div class="main-grid" style="display:grid; grid-template-columns: 1fr 320px; gap: 24px;">

        <!-- Left Column: Notes -->
        <div class="space-y-5">
            
            <!-- Search & Filters -->
            <div class="panel" style="padding: 16px 20px;">
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-1 w-full">
                        <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--text-dim)"></i>
                        <input id="searchInput" type="text" class="field" style="padding-left: 32px;" placeholder="Search notes..." oninput="renderNotes()">
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        <button class="pill active" data-filter="all" onclick="setFilter('all', this)">All</button>
                        <button class="pill" data-filter="pinned" onclick="setFilter('pinned', this)"><i class="fa-solid fa-thumbtack text-[10px]"></i> Pinned</button>
                        <button class="pill" data-filter="favorites" onclick="setFilter('favorites', this)"><i class="fa-solid fa-star text-[10px]"></i> Favorites</button>
                    </div>
                </div>
            </div>

            <!-- Notes List -->
            <div id="notesList" class="space-y-3"></div>

        </div>

        <!-- Right Column: Folders & Quick Links -->
        <div class="space-y-5">

            <!-- Folders -->
            <div class="panel">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-sm">Folders</h3>
                    <button onclick="openFolderModal()" class="icon-btn" style="width:28px; height:28px;"><i class="fa-solid fa-plus text-[10px]"></i></button>
                </div>
                <div id="foldersList" class="space-y-2"></div>
            </div>

            <!-- Quick Tools -->
            <div class="panel">
                <h3 class="font-bold text-sm mb-4">Quick Tools</h3>
                <div class="space-y-2">
                    <a href="{{ route('ai-chat') }}" class="folder-item" style="text-decoration:none; color:var(--text);">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-robot" style="color:var(--accent)"></i>
                            <span class="text-sm font-semibold">Ask AI</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-xs" style="color:var(--text-dim)"></i>
                    </a>
                    <a href="{{ route('flashcards') }}" class="folder-item" style="text-decoration:none; color:var(--text);">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-layer-group" style="color:#818cf8"></i>
                            <span class="text-sm font-semibold">Flashcards</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-xs" style="color:var(--text-dim)"></i>
                    </a>
                    <a href="{{ route('quizzes') }}" class="folder-item" style="text-decoration:none; color:var(--text);">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-clipboard-check" style="color:#10b981"></i>
                            <span class="text-sm font-semibold">Quizzes</span>
                        </div>
                        <i class="fa-solid fa-arrow-right text-xs" style="color:var(--text-dim)"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const state = {
    currentFilter: 'all',
    currentFolder: null
};

// ===== Data Access =====
function getNotes() {
    try { return JSON.parse(localStorage.getItem('nt_notes') || '[]'); }
    catch { return []; }
}
function saveNotes(notes) {
    try { localStorage.setItem('nt_notes', JSON.stringify(notes)); } catch {}
}
function getFolders() {
    try { return JSON.parse(localStorage.getItem('nt_folders') || '[]'); }
    catch { return []; }
}
function saveFolders(folders) {
    try { localStorage.setItem('nt_folders', JSON.stringify(folders)); } catch {}
}
function uid() { return 'n' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }

// ===== Toast =====
function showToast(message, type) {
    type = type || 'success';
    const container = document.getElementById('toasts');
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    const icons = { success: 'check-circle', error: 'exclamation-circle', info: 'info-circle' };
    toast.innerHTML = '<i class="fa-solid fa-' + (icons[type] || icons.info) + '"></i><span>' + message + '</span>';
    container.appendChild(toast);
    requestAnimationFrame(function() { toast.classList.add('show'); });
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3500);
}

function escHtml(val) {
    const d = document.createElement('div');
    d.textContent = String(val);
    return d.innerHTML;
}

// ===== Modals =====
function openNoteModal(id) {
    const modal = document.getElementById('noteModal');
    const titleEl = document.getElementById('noteTitle');
    const contentEl = document.getElementById('noteContent');
    const folderEl = document.getElementById('noteFolder');
    const idEl = document.getElementById('noteId');
    const modalTitle = document.getElementById('modalTitle');

    // Populate folder dropdown
    const folders = getFolders();
    folderEl.innerHTML = '<option value="">No Folder</option>' + folders.map(f => 
        '<option value="' + f.id + '">' + escHtml(f.name) + '</option>'
    ).join('');

    if (id) {
        const note = getNotes().find(n => n.id === id);
        if (note) {
            idEl.value = note.id;
            titleEl.value = note.title;
            contentEl.value = note.content;
            folderEl.value = note.folderId || '';
            modalTitle.textContent = 'Edit Note';
        }
    } else {
        idEl.value = '';
        titleEl.value = '';
        contentEl.value = '';
        if (state.currentFolder) folderEl.value = state.currentFolder;
        modalTitle.textContent = 'New Note';
    }

    modal.classList.add('show');
    setTimeout(() => titleEl.focus(), 100);
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.remove('show');
}

function openFolderModal() {
    document.getElementById('folderName').value = '';
    document.getElementById('folderModal').classList.add('show');
    setTimeout(() => document.getElementById('folderName').focus(), 100);
}

function closeFolderModal() {
    document.getElementById('folderModal').classList.remove('show');
}

// ===== CRUD Operations =====
function saveNote() {
    const id = document.getElementById('noteId').value;
    const title = document.getElementById('noteTitle').value.trim();
    const content = document.getElementById('noteContent').value.trim();
    const folderId = document.getElementById('noteFolder').value;

    if (!title) {
        showToast('Please enter a note title', 'error');
        return;
    }

    const notes = getNotes();

    if (id) {
        const idx = notes.findIndex(n => n.id === id);
        if (idx >= 0) {
            notes[idx].title = title;
            notes[idx].content = content;
            notes[idx].folderId = folderId;
            notes[idx].updatedAt = Date.now();
        }
        showToast('Note updated', 'success');
    } else {
        notes.unshift({
            id: uid(),
            title: title,
            content: content,
            folderId: folderId,
            isFavorite: false,
            isPinned: false,
            createdAt: Date.now(),
            updatedAt: Date.now()
        });
        showToast('Note created', 'success');
    }

    saveNotes(notes);
    closeNoteModal();
    renderAll();
}

function deleteNote(id) {
    if (!confirm('Are you sure you want to delete this note?')) return;
    saveNotes(getNotes().filter(n => n.id !== id));
    showToast('Note deleted', 'info');
    renderAll();
}

function toggleFavorite(id) {
    const notes = getNotes();
    const note = notes.find(n => n.id === id);
    if (note) {
        note.isFavorite = !note.isFavorite;
        saveNotes(notes);
        renderAll();
    }
}

function togglePin(id) {
    const notes = getNotes();
    const note = notes.find(n => n.id === id);
    if (note) {
        note.isPinned = !note.isPinned;
        saveNotes(notes);
        renderAll();
    }
}

function saveFolder() {
    const name = document.getElementById('folderName').value.trim();
    if (!name) {
        showToast('Please enter a folder name', 'error');
        return;
    }

    const folders = getFolders();
    folders.push({ id: uid(), name: name, createdAt: Date.now() });
    saveFolders(folders);
    
    closeFolderModal();
    showToast('Folder created', 'success');
    renderAll();
}

function deleteFolder(id) {
    if (!confirm('Delete this folder? Notes inside will be moved to "No Folder".')) return;
    saveFolders(getFolders().filter(f => f.id !== id));
    
    // Move notes out of deleted folder
    const notes = getNotes();
    notes.forEach(n => { if (n.folderId === id) n.folderId = ''; });
    saveNotes(notes);

    if (state.currentFolder === id) {
        state.currentFolder = null;
        setFilter('all', document.querySelector('[data-filter="all"]'));
    }
    
    showToast('Folder deleted', 'info');
    renderAll();
}

// ===== Filtering =====
function setFilter(filter, btn) {
    state.currentFilter = filter;
    state.currentFolder = null;
    
    document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    document.querySelectorAll('.folder-item').forEach(b => b.classList.remove('active'));
    renderNotes();
}

function setFolderFilter(folderId, element) {
    state.currentFolder = folderId;
    state.currentFilter = 'folder';
    
    document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.folder-item').forEach(b => b.classList.remove('active'));
    if (element) element.classList.add('active');
    
    renderNotes();
}

// ===== Rendering =====
function renderStats() {
    const notes = getNotes();
    const folders = getFolders();
    
    document.getElementById('statTotal').textContent = notes.length;
    document.getElementById('statFolders').textContent = folders.length;
    document.getElementById('statFavorites').textContent = notes.filter(n => n.isFavorite).length;
    document.getElementById('statPinned').textContent = notes.filter(n => n.isPinned).length;
}

function renderNotes() {
    const container = document.getElementById('notesList');
    let notes = getNotes();
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();

    // Apply search
    if (searchQuery) {
        notes = notes.filter(n => 
            n.title.toLowerCase().includes(searchQuery) || 
            n.content.toLowerCase().includes(searchQuery)
        );
    }

    // Apply filter
    if (state.currentFilter === 'pinned') {
        notes = notes.filter(n => n.isPinned);
    } else if (state.currentFilter === 'favorites') {
        notes = notes.filter(n => n.isFavorite);
    } else if (state.currentFilter === 'folder' && state.currentFolder) {
        notes = notes.filter(n => n.folderId === state.currentFolder);
    }

    // Sort: Pinned first, then by updatedAt
    notes.sort((a, b) => {
        if (a.isPinned && !b.isPinned) return -1;
        if (!a.isPinned && b.isPinned) return 1;
        return (b.updatedAt || b.createdAt) - (a.updatedAt || a.createdAt);
    });

    if (notes.length === 0) {
        container.innerHTML = `
            <div class="panel empty-state">
                <i class="fa-solid fa-note-sticky"></i>
                <p class="font-semibold" style="color:var(--text)">${searchQuery ? 'No notes found' : 'No notes yet'}</p>
                <p style="margin-top:4px">${searchQuery ? 'Try a different search term' : 'Create your first note to get started.'}</p>
                ${!searchQuery ? '<button onclick="openNoteModal()" class="gen-btn" style="margin-top:16px"><i class="fa-solid fa-plus"></i> New Note</button>' : ''}
            </div>`;
        return;
    }

    const folders = getFolders();

    container.innerHTML = notes.map(note => {
        const folder = folders.find(f => f.id === note.folderId);
        const folderName = folder ? folder.name : '';
        const preview = note.content ? note.content.substring(0, 120) + (note.content.length > 120 ? '...' : '') : 'Empty note';
        const dateStr = new Date(note.updatedAt || note.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const isActive = state.currentFolder === note.folderId;

        return `
        <div class="note-item ${isActive ? 'active' : ''}">
            <div class="note-actions">
                <button class="icon-btn pin ${note.isPinned ? 'active' : ''}" onclick="event.stopPropagation(); togglePin('${note.id}')" title="Pin">
                    <i class="fa-solid fa-thumbtack"></i>
                </button>
                <button class="icon-btn fav ${note.isFavorite ? 'active' : ''}" onclick="event.stopPropagation(); toggleFavorite('${note.id}')" title="Favorite">
                    <i class="fa-solid fa-star"></i>
                </button>
                <button class="icon-btn" onclick="event.stopPropagation(); openNoteModal('${note.id}')" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button class="icon-btn danger" onclick="event.stopPropagation(); deleteNote('${note.id}')" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <div onclick="openNoteModal('${note.id}')">
                <div class="flex items-center gap-2 mb-1">
                    ${note.isPinned ? '<i class="fa-solid fa-thumbtack text-[10px]" style="color:var(--accent)"></i>' : ''}
                    <h3 class="font-bold text-sm truncate">${escHtml(note.title)}</h3>
                </div>
                <p class="text-xs leading-relaxed mb-3" style="color:var(--text-muted)">${escHtml(preview)}</p>
                <div class="flex items-center gap-3 text-[11px]" style="color:var(--text-dim)">
                    <span><i class="fa-solid fa-calendar"></i> ${dateStr}</span>
                    ${folderName ? '<span><i class="fa-solid fa-folder"></i> ' + escHtml(folderName) + '</span>' : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

function renderFolders() {
    const container = document.getElementById('foldersList');
    const folders = getFolders();
    const notes = getNotes();

    if (folders.length === 0) {
        container.innerHTML = '<p class="text-xs text-center py-4" style="color:var(--text-dim)">No folders yet. Create one to organize your notes.</p>';
        return;
    }

    container.innerHTML = folders.map(folder => {
        const count = notes.filter(n => n.folderId === folder.id).length;
        const isActive = state.currentFolder === folder.id;
        
        return `
        <div class="folder-item ${isActive ? 'active' : ''}" onclick="setFolderFilter('${folder.id}', this)">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <i class="fa-solid fa-folder text-sm" style="color:${isActive ? 'var(--accent)' : 'var(--text-dim)'}"></i>
                <span class="text-sm font-semibold truncate">${escHtml(folder.name)}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold" style="color:var(--text-dim)">${count}</span>
                <button class="icon-btn danger" style="width:24px; height:24px; opacity:0.5;" onclick="event.stopPropagation(); deleteFolder('${folder.id}')" title="Delete Folder">
                    <i class="fa-solid fa-xmark text-[9px]"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

function renderAll() {
    renderStats();
    renderNotes();
    renderFolders();
}

// ===== Initialization =====
function initNotesPage() {
    renderAll();

    // Close modals on overlay click
    document.getElementById('noteModal').addEventListener('click', function(e) {
        if (e.target === this) closeNoteModal();
    });
    document.getElementById('folderModal').addEventListener('click', function(e) {
        if (e.target === this) closeFolderModal();
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeNoteModal();
            closeFolderModal();
        }
    });
}

document.addEventListener('DOMContentLoaded', initNotesPage);
</script>
@endsection