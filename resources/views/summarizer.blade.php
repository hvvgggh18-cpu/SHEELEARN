@extends('layouts.dashboard-layout')

@section('title', 'Summarizer — SHEELEARN AI')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #020617;
        --surface: rgba(15,23,42,0.65);
        --surface-2: rgba(15,23,42,0.52);
        --surface-3: rgba(15,23,42,0.35);
        --border: rgba(226,232,240,0.06);
        --border-hover: rgba(34,211,238,0.12);
        --accent: #22d3ee;
        --accent-hover: #3b82f6;
        --accent-dim: rgba(34,211,238,0.08);
        --accent-border: rgba(34,211,238,0.15);
        --text: #e2e8f0;
        --text-muted: rgba(226,232,240,0.78);
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
    .dashboard-summarizer { width: 100%; }

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

    .src-btn {
        flex: 1; padding: 16px 10px; border-radius: 12px; border: 1px solid var(--border);
        background: var(--surface); cursor: pointer; transition: all 0.2s;
        display: flex; flex-direction: column; align-items: center; gap: 8px; text-align: center;
    }
    .src-btn:hover { border-color: var(--border-hover); background: var(--surface-2); }
    .src-btn.active { border-color: var(--accent-border); background: var(--accent-dim); }
    .src-btn i { font-size: 22px; color: var(--text-dim); transition: color 0.2s; }
    .src-btn.active i { color: var(--accent); }
    .src-btn span { font-size: 12px; font-weight: 600; color: var(--text-muted); }
    .src-btn.active span { color: var(--accent); }

    .field {
        width: 100%; background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 10px; padding: 10px 14px; font-size: 14px; color: var(--text);
        font-family: 'Outfit', sans-serif; transition: border-color 0.2s; outline: none;
    }
    .field:focus { border-color: var(--accent-border); }
    .field::placeholder { color: var(--text-dim); }
    textarea.field { resize: vertical; min-height: 200px; line-height: 1.7; }

    .dropzone {
        border: 1.5px dashed var(--border-hover); border-radius: 16px; padding: 48px 32px;
        text-align: center; cursor: pointer; transition: all 0.25s; min-height: 220px;
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
    .gen-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

    .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(2,26,15,0.3); border-top-color: #021a0f; border-radius: 50%; animation: spin 0.6s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

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

    .progress-track { height: 5px; background: var(--surface-3); border-radius: 5px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--accent); border-radius: 5px; transition: width 0.4s ease; }

    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; text-align: center; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-top: 6px; color: var(--text-dim); }

    .sidebar-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; }

    .toast { padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 8px 24px rgba(0,0,0,0.4); max-width: 360px; }
    .toast.show { transform: translateX(0); }
    .toast-success { background: #065f46; color: #a7f3d0; border: 1px solid rgba(16,185,129,0.3); }
    .toast-error { background: #7f1d1d; color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .toast-info { background: #1e3a5f; color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }

    .history-item {
        padding: 12px; border-radius: 10px; background: var(--surface-2);
        cursor: pointer; transition: all 0.2s; border: 1px solid transparent; position: relative;
    }
    .history-item:hover { border-color: var(--accent-border); background: var(--accent-dim); }

    .week-bar { flex: 1; border-radius: 4px 4px 0 0; transition: height 0.4s ease; min-height: 4px; }

    .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; }
    .toggle-row span { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .toggle-switch { width: 40px; height: 22px; border-radius: 11px; border: none; background: var(--surface-3); cursor: pointer; appearance: none; position: relative; transition: background 0.3s; flex-shrink: 0; }
    .toggle-switch:checked { background: var(--accent); }
    .toggle-switch::after { content: ''; position: absolute; width: 18px; height: 18px; border-radius: 50%; background: white; top: 2px; left: 2px; transition: left 0.3s; }
    .toggle-switch:checked::after { left: 20px; }

    /* Summary sections */
    .summary-section {
        border-radius: 12px; border: 1px solid var(--border); background: var(--surface-2);
        overflow: hidden; transition: all 0.25s; margin-bottom: 10px;
    }
    .summary-section:hover { border-color: var(--border-hover); }
    .section-head {
        display: flex; align-items: center; gap: 10px; padding: 14px 18px;
        cursor: pointer; user-select: none; transition: background 0.2s;
    }
    .section-head:hover { background: rgba(255,255,255,0.02); }
    .section-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
    .section-title { font-size: 14px; font-weight: 600; color: var(--text); flex: 1; }
    .section-badge { font-size: 11px; font-weight: 600; color: var(--text-dim); background: var(--surface-3); padding: 2px 8px; border-radius: 99px; }
    .section-chevron { font-size: 10px; color: var(--text-dim); transition: transform 0.25s; }
    .summary-section.open .section-chevron { transform: rotate(180deg); }
    .section-body { max-height: 0; overflow: hidden; transition: max-height 0.4s cubic-bezier(0.16,1,0.3,1); }
    .summary-section.open .section-body { max-height: 2000px; }
    .section-content { padding: 0 18px 16px; font-size: 14px; line-height: 1.75; color: var(--text-muted); }
    .section-content strong { color: var(--text); font-weight: 600; }
    .section-content ul, .section-content ol { margin: 8px 0; padding-left: 1.2rem; }
    .section-content li { margin-bottom: 4px; }
    .section-content p { margin-bottom: 6px; }

    /* Confidence bar */
    .conf-track { height: 6px; border-radius: 99px; background: var(--surface-3); overflow: hidden; flex: 1; }
    .conf-fill { height: 100%; border-radius: 99px; transition: width 0.8s cubic-bezier(0.16,1,0.3,1); }
    .conf-fill.high { background: linear-gradient(90deg, #10b981, #34d399); }
    .conf-fill.medium { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .conf-fill.low { background: linear-gradient(90deg, #ef4444, #f87171); }

    /* File card */
    .file-card {
        padding: 14px; border-radius: 12px; border: 1px solid var(--border);
        background: var(--surface-2); display: flex; align-items: center; gap: 12px;
    }
    .file-icon {
        width: 42px; height: 42px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
    }
    .file-icon.pdf { background: rgba(248,113,113,0.1); color: #f87171; }
    .file-icon.doc { background: rgba(59,130,246,0.1); color: #60a5fa; }
    .file-icon.txt { background: rgba(148,163,184,0.08); color: #94a3b8; }
    .file-icon.ppt { background: rgba(251,191,36,0.1); color: #fbbf24; }
    .file-icon.xls { background: rgba(52,211,153,0.1); color: #34d399; }
    .file-icon.img { background: rgba(236,72,153,0.1); color: #f472b6; }
    .file-icon.generic { background: var(--accent-dim); color: var(--accent); }

    /* Step progress */
    .step-bar { display: flex; align-items: center; gap: 0; padding: 10px 14px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); margin-bottom: 20px; }
    .step-item { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 0 0 auto; }
    .step-dot { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 1.5px solid var(--border); background: var(--surface-2); color: var(--text-dim); transition: all 0.3s; }
    .step-item.active .step-dot { border-color: var(--accent); background: var(--accent-dim); color: var(--accent); box-shadow: 0 0 16px rgba(16,185,129,0.2); }
    .step-item.done .step-dot { border-color: var(--accent); background: var(--accent); color: #021a0f; }
    .step-label { font-size: 10px; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.05em; }
    .step-item.active .step-label { color: var(--accent); }
    .step-item.done .step-label { color: var(--accent); }
    .step-line { flex: 1; height: 2px; background: var(--border); margin: 0 6px; margin-bottom: 18px; border-radius: 99px; transition: background 0.3s; }
    .step-line.done { background: var(--accent); }
    .step-status { font-size: 12px; color: var(--text-muted); margin-top: 8px; display: flex; align-items: center; gap: 6px; }
    .step-status .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: dotPulse 1.2s ease-in-out infinite; }
    @keyframes dotPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

    /* Keyword chips */
    .kw-chip { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 500; background: var(--accent-dim); color: var(--accent); border: 1px solid var(--accent-border); margin: 0 4px 6px 0; }

    .view { animation: fadeUp 0.3s ease; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    .confirm-overlay { position: absolute; inset: 0; background: rgba(10,12,16,0.94); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; z-index: 5; }

    @media (max-width: 1023px) { .main-grid { grid-template-columns: 1fr !important; } }
</style>
@endsection

@section('content')
<div class="dashboard-summarizer">

<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
<input type="file" id="fileInput" class="hidden" accept=".txt,.pdf,.md,.doc,.docx">

<!-- Header -->
<header class="pt-6 pb-5 px-1">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-800 tracking-tight">Summarizer</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Upload documents and get intelligent summaries</p>
        </div>
        <nav class="flex gap-2 bg-[var(--surface)] p-1.5 rounded-xl w-fit">
            <button class="tab-btn active" data-tab="create"><i class="fa-solid fa-plus text-xs"></i> New</button>
            <button class="tab-btn" data-tab="library"><i class="fa-solid fa-folder-open text-xs"></i> History</button>
        </nav>
    </div>
</header>

<!-- Two-Column Grid -->
<div class="main-grid grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 pb-8">

    <!-- ====== LEFT: Main Content ====== -->
    <div class="min-w-0">

        <!-- ============ CREATE VIEW ============ -->
        <section id="createView" class="view space-y-5">

            <!-- Source -->
            <div class="form-panel">
                <p class="section-label">Source</p>
                <div class="flex gap-3">
                    <button class="src-btn active" data-source="upload"><i class="fa-solid fa-cloud-arrow-up"></i><span>Upload File</span></button>
                    <button class="src-btn" data-source="text"><i class="fa-solid fa-paste"></i><span>Paste Text</span></button>
                    <button class="src-btn" data-source="manual"><i class="fa-solid fa-pen"></i><span>Type Manually</span></button>
                </div>
            </div>

            <!-- Content Input -->
            <div class="form-panel">
                <p class="section-label">Content</p>
                <div id="uploadZone" class="dropzone">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl mb-4" style="color:var(--accent)"></i>
                    <p class="text-base font-600" style="color:var(--text)">Drag and drop your file here</p>
                    <p class="text-sm mt-1" style="color:var(--text-dim)">Supports TXT, PDF, Markdown, DOC, DOCX</p>
                    <button type="button" onclick="document.getElementById('fileInput').click()" class="mt-5 px-6 py-2.5 rounded-lg text-sm font-600 transition" style="background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-border)">Browse Files</button>
                    <p id="uploadStatus" class="text-sm mt-4 hidden" style="color:var(--accent)"></p>
                </div>
                <div id="textZone" class="hidden">
                    <textarea id="sourceText" class="field" placeholder="Paste or type your content here..."></textarea>
                    <p class="text-xs mt-2" style="color:var(--text-dim)">The AI will analyze and summarize this content.</p>
                </div>
            </div>

            <!-- File Preview -->
            <div id="fileCard" class="hidden">
                <div class="file-card">
                    <div id="fileIconBox" class="file-icon generic"><i class="fa-solid fa-file"></i></div>
                    <div class="flex-1 min-w-0">
                        <p id="fileName" class="font-600 text-sm truncate" style="color:var(--text)"></p>
                        <p id="fileMeta" class="text-xs mt-0.5" style="color:var(--text-dim)"></p>
                    </div>
                    <button onclick="clearFile()" class="w-8 h-8 rounded-lg flex items-center justify-center transition" style="color:var(--text-dim)" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-dim)'"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>

            <!-- Settings -->
            <div class="form-panel">
                <p class="section-label">Settings</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Summary Length</p>
                        <div class="flex gap-2">
                            <button class="pill" data-len="brief">Brief</button>
                            <button class="pill active" data-len="standard">Standard</button>
                            <button class="pill" data-len="detailed">Detailed</button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Focus Area</p>
                        <div class="flex gap-2 flex-wrap">
                            <button class="pill active" data-focus="general">General</button>
                            <button class="pill" data-focus="key_points">Key Points</button>
                            <button class="pill" data-focus="academic">Academic</button>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="space-y-1">
                            <div class="toggle-row"><span>Include Keywords</span><input type="checkbox" class="toggle-switch" id="optKeywords" checked></div>
                            <div class="toggle-row"><span>Include Key Entities</span><input type="checkbox" class="toggle-switch" id="optEntities" checked></div>
                            <div class="toggle-row"><span>Include Takeaways</span><input type="checkbox" class="toggle-switch" id="optTakeaways" checked></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generate Button -->
            <div class="flex items-center gap-4 pt-1">
                <button id="summarizeBtn" class="gen-btn" onclick="generateSummary()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Analyze Document
                </button>
                <span id="genHint" class="text-xs" style="color:var(--text-dim)"><i class="fa-solid fa-clock"></i> Takes a few seconds</span>
            </div>

            <!-- Step Progress (shown during generation) -->
            <div id="stepProgress" class="hidden">
                <div class="step-bar">
                    <div class="step-item active" data-step="1"><div class="step-dot"><i class="fa-solid fa-cloud-arrow-up"></i></div><span class="step-label">Upload</span></div>
                    <div class="step-line" data-line="1"></div>
                    <div class="step-item" data-step="2"><div class="step-dot"><i class="fa-solid fa-file-lines"></i></div><span class="step-label">Extract</span></div>
                    <div class="step-line" data-line="2"></div>
                    <div class="step-item" data-step="3"><div class="step-dot"><i class="fa-solid fa-microchip"></i></div><span class="step-label">Analyze</span></div>
                    <div class="step-line" data-line="3"></div>
                    <div class="step-item" data-step="4"><div class="step-dot"><i class="fa-solid fa-check"></i></div><span class="step-label">Done</span></div>
                </div>
                <div class="step-status" id="stepStatus"><span class="dot"></span><span id="stepStatusText">Processing...</span></div>
            </div>

            <!-- ============ SUMMARY OUTPUT ============ -->
            <div id="summaryOutput" class="hidden space-y-5">

                <!-- Document Info -->
                <div class="form-panel">
                    <div class="flex items-center justify-between mb-4">
                        <p class="section-label" style="margin-bottom:0">Summary Result</p>
                        <div class="flex gap-2">
                            <button onclick="copySummary()" class="action-btn sm"><i class="fa-regular fa-copy"></i> Copy</button>
                            <button onclick="exportTXT()" class="action-btn sm"><i class="fa-solid fa-download"></i> Export</button>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                        <div class="text-center p-3 rounded-xl" style="background:var(--surface-2)"><p id="statOrigWords" class="text-xl font-800" style="color:var(--accent)">0</p><p class="text-[10px] font-600 mt-1" style="color:var(--text-dim)">ORIGINAL WORDS</p></div>
                        <div class="text-center p-3 rounded-xl" style="background:var(--surface-2)"><p id="statSummWords" class="text-xl font-800" style="color:var(--warning)">0</p><p class="text-[10px] font-600 mt-1" style="color:var(--text-dim)">SUMMARY WORDS</p></div>
                        <div class="text-center p-3 rounded-xl" style="background:var(--surface-2)"><p id="statReduction" class="text-xl font-800" style="color:#818cf8">0%</p><p class="text-[10px] font-600 mt-1" style="color:var(--text-dim)">REDUCTION</p></div>
                        <div class="text-center p-3 rounded-xl" style="background:var(--surface-2)"><p id="statReadTime" class="text-xl font-800" style="color:#f472b6">0m</p><p class="text-[10px] font-600 mt-1" style="color:var(--text-dim)">READ TIME</p></div>
                    </div>

                    <!-- Confidence -->
                    <div class="flex items-center gap-3 mb-4">
                        <i class="fa-solid fa-brain text-sm" style="color:var(--text-dim)"></i>
                        <span class="text-xs font-600" style="color:var(--text-muted)">Confidence</span>
                        <div class="conf-track"><div id="confFill" class="conf-fill high" style="width:0%"></div></div>
                        <span id="confPct" class="text-xs font-700" style="color:var(--accent)">0%</span>
                    </div>

                    <!-- Keywords -->
                    <div id="keywordsSection" class="mb-2">
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Keywords</p>
                        <div id="keywordsList"></div>
                    </div>
                </div>

                <!-- Summary Sections -->
                <div id="summarySections"></div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button onclick="regenerateSummary()" class="action-btn primary"><i class="fa-solid fa-rotate"></i> Regenerate</button>
                    <button onclick="resetAll()" class="action-btn"><i class="fa-solid fa-plus"></i> New Summary</button>
                </div>
            </div>
        </section>

        <!-- ============ LIBRARY / HISTORY VIEW ============ -->
        <section id="libraryView" class="view hidden">
            <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--text-dim)"></i>
                    <input id="searchHistory" type="text" class="field pl-9" placeholder="Search summaries...">
                </div>
            </div>
            <div id="historyGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
            <div id="emptyHistory" class="hidden text-center py-20">
                <i class="fa-solid fa-folder-open text-5xl mb-4" style="color:var(--text-dim)"></i>
                <p class="text-lg font-600" style="color:var(--text-muted)">No summaries yet</p>
                <p class="text-sm mt-1" style="color:var(--text-dim)">Upload a document to generate your first summary.</p>
                <button onclick="switchTab('create')" class="action-btn primary mt-5"><i class="fa-solid fa-plus"></i> New Summary</button>
            </div>
        </section>
    </div>

    <!-- ====== RIGHT: Sidebar ====== -->
    <aside class="space-y-5">
        <div class="grid grid-cols-2 gap-3">
            <div class="stat-card"><p id="sbTotal" class="stat-value" style="color:var(--accent)">0</p><p class="stat-label">Summaries</p></div>
            <div class="stat-card"><p id="sbWordsSaved" class="stat-value" style="color:var(--warning)">0</p><p class="stat-label">Words Saved</p></div>
            <div class="stat-card"><p id="sbDocs" class="stat-value" style="color:#818cf8">0</p><p class="stat-label">Documents</p></div>
            <div class="stat-card"><p id="sbStreak" class="stat-value" style="color:#f472b6">0</p><p class="stat-label">Day Streak</p></div>
        </div>

        <div class="sidebar-panel">
            <p class="section-label">Weekly Activity</p>
            <div id="weeklyBars" class="flex items-end gap-2" style="height:80px">
                <div class="week-bar" style="height:10%;background:rgba(16,185,129,0.2)"></div>
                <div class="week-bar" style="height:20%;background:rgba(16,185,129,0.25)"></div>
                <div class="week-bar" style="height:15%;background:rgba(16,185,129,0.2)"></div>
                <div class="week-bar" style="height:40%;background:rgba(16,185,129,0.3)"></div>
                <div class="week-bar" style="height:55%;background:rgba(16,185,129,0.4)"></div>
                <div class="week-bar" style="height:35%;background:rgba(16,185,129,0.35)"></div>
                <div class="week-bar" style="height:70%;background:rgba(16,185,129,0.5)"></div>
            </div>
            <div class="flex justify-between mt-2 text-[10px]" style="color:var(--text-dim)"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
        </div>

        <div class="sidebar-panel">
            <div class="flex items-center justify-between mb-3"><p class="section-label" style="margin-bottom:0">Recent Summaries</p><button onclick="switchTab('library')" class="text-xs font-600" style="color:var(--accent)">View All</button></div>
            <div id="recentList" class="space-y-2 max-h-[280px] overflow-y-auto pr-1"><p class="text-xs text-center py-4" style="color:var(--text-dim)">No summaries yet</p></div>
        </div>

        <div class="sidebar-panel">
            <p class="section-label">Tips</p>
            <div class="space-y-3">
                <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:var(--accent-dim)"><i class="fa-solid fa-file-lines text-xs" style="color:var(--accent)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Longer Documents</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">More text yields better, more detailed summaries.</p></div></div>
                <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:rgba(129,140,248,0.1)"><i class="fa-solid fa-bullseye text-xs" style="color:#818cf8"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Use Focus Areas</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Select Key Points or Academic focus for targeted results.</p></div></div>
                <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:var(--warning-dim)"><i class="fa-solid fa-rotate text-xs" style="color:var(--warning)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Regenerate</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Try different lengths or focus areas to refine results.</p></div></div>
            </div>
        </div>
    </aside>
</div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
if (typeof pdfjsLib !== 'undefined') { pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js'; }

const state = {
    currentTab: 'create',
    sourceType: 'upload',
    summaryLength: 'standard',
    focusArea: 'general',
    uploadedText: '',
    uploadedFileName: '',
    currentSummaryId: null,
    studyHistory: JSON.parse(localStorage.getItem('sm_history') || '[]'),
};

// Storage
function getSummaries() { try { return JSON.parse(localStorage.getItem('sm_summaries') || '[]'); } catch { return []; } }
function saveSummaries(s) { try { localStorage.setItem('sm_summaries', JSON.stringify(s)); } catch(e){} }
function getSummary(id) { return getSummaries().find(s => s.id === id) || null; }
function saveSummary(item) { const all = getSummaries(); const i = all.findIndex(s => s.id === item.id); if (i >= 0) all[i] = item; else all.unshift(item); saveSummaries(all); }
function deleteSummary(id) { saveSummaries(getSummaries().filter(s => s.id !== id)); }
function uid() { return 's' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function logStudy() { const today = new Date().toISOString().slice(0,10); state.studyHistory.push(today); try { localStorage.setItem('sm_history', JSON.stringify(state.studyHistory)); } catch(e){} }
function wordCount(t) { return t.trim().split(/\s+/).filter(w=>w.length>0).length; }
function truncate(s, n) { return s.length > n ? s.substring(0, n) + '...' : s; }

// Toast
function showToast(msg, type='info') {
    const c = document.getElementById('toasts'), t = document.createElement('div');
    t.className = `toast toast-${type}`;
    const icons = {success:'check-circle',error:'exclamation-circle',info:'info-circle'};
    t.innerHTML = `<i class="fa-solid fa-${icons[type]||icons.info}"></i><span>${msg}</span>`;
    c.appendChild(t); requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
}

// Tab
function switchTab(tab) {
    state.currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.getElementById('createView').classList.toggle('hidden', tab !== 'create');
    document.getElementById('libraryView').classList.toggle('hidden', tab !== 'library');
    if (tab === 'library') renderLibrary();
    updateSidebar();
}

// Source switching
document.querySelectorAll('.src-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.src-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active'); state.sourceType = btn.dataset.source;
        state.uploadedText = ''; state.uploadedFileName = '';
        document.getElementById('uploadStatus').classList.add('hidden');
        const isUpload = state.sourceType === 'upload';
        document.getElementById('uploadZone').classList.toggle('hidden', !isUpload);
        document.getElementById('textZone').classList.toggle('hidden', isUpload);
        document.getElementById('sourceText').placeholder = state.sourceType === 'manual' ? 'Type your content here...' : 'Paste your content here...';
    });
});

// Pills
function setupPills(sel, key, parser) {
    document.querySelectorAll(sel).forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll(sel).forEach(b => b.classList.remove('active'));
            btn.classList.add('active'); state[key] = parser ? parser(btn) : btn.dataset[key];
        });
    });
}
setupPills('[data-len]', 'summaryLength');
setupPills('[data-focus]', 'focusArea');

// File upload
const fileInput = document.getElementById('fileInput'), uploadZone = document.getElementById('uploadZone');
fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));
uploadZone.addEventListener('click', e => { if (e.target.tagName !== 'BUTTON') fileInput.click(); });
uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
uploadZone.addEventListener('drop', e => { e.preventDefault(); uploadZone.classList.remove('dragover'); if (e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]); });

async function handleFile(file) {
    if (!file) return;
    const status = document.getElementById('uploadStatus');
    status.textContent = 'Reading file...'; status.classList.remove('hidden');

    try {
        let text = '';
        if (file.type === 'application/pdf' && typeof pdfjsLib !== 'undefined') {
            const buf = await file.arrayBuffer(), pdf = await pdfjsLib.getDocument({data:buf}).promise;
            for (let i=1;i<=pdf.numPages;i++) { const pg = await pdf.getPage(i), ct = await pg.getTextContent(); text += ct.items.map(it=>it.str).join(' ')+'\n'; }
        } else if (file.name.endsWith('.pdf') && typeof pdfjsLib === 'undefined') {
            status.textContent = 'PDF reader not loaded. Paste text instead.'; status.style.color = 'var(--warning)'; return;
        } else { text = await file.text(); }

        if (!text.trim()) { status.textContent = 'No text extracted. Try pasting manually.'; status.style.color = 'var(--warning)'; return; }

        state.uploadedText = text.trim(); state.uploadedFileName = file.name;
        status.textContent = `Loaded: ${file.name} (${wordCount(text)} words)`; status.style.color = 'var(--accent)';

        // Show file card
        document.getElementById('fileCard').classList.remove('hidden');
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileMeta').textContent = `${wordCount(text)} words · ${(file.size/1024).toFixed(1)} KB`;

        const ext = file.name.split('.').pop().toLowerCase();
        const iconBox = document.getElementById('fileIconBox');
        iconBox.className = 'file-icon ' + ({pdf:'pdf',doc:'doc',docx:'doc',txt:'txt',md:'txt',ppt:'ppt',pptx:'ppt',xls:'xls',xlsx:'xls',png:'img',jpg:'img',jpeg:'img',webp:'img'}[ext] || 'generic');
        iconBox.innerHTML = `<i class="fa-solid fa-${{pdf:'file-pdf',doc:'file-word',docx:'file-word',txt:'file-lines',md:'file-lines',ppt:'file-powerpoint',pptx:'file-powerpoint',xls:'file-excel',xlsx:'file-excel',png:'file-image',jpg:'file-image',jpeg:'file-image',webp:'file-image'}[ext]||'file'}"></i>`;

        showToast(`File "${file.name}" loaded`, 'success');
    } catch(err) {
        status.textContent = `Error: ${err.message}`; status.style.color = 'var(--danger)';
        showToast('Failed to read file', 'error');
    }
}

function clearFile() {
    state.uploadedText = ''; state.uploadedFileName = '';
    document.getElementById('fileCard').classList.add('hidden');
    document.getElementById('uploadStatus').classList.add('hidden');
    fileInput.value = '';
}

// ── Text Analysis Engine ──
function analyzeText(text, length, focus) {
    const sentences = extractSentences(text);
    const words = text.trim().split(/\s+/).filter(w => w.length > 0);
    const origWordCount = words.length;
    const keywords = extractKeywords(text, 10);
    const entities = extractEntities(text);

    // Score sentences by importance
    const scoredSentences = sentences.map((s, i) => {
        let score = 0;
        const sWords = s.toLowerCase().split(/\s+/);

        // Keyword overlap
        keywords.forEach(kw => { if (s.toLowerCase().includes(kw.toLowerCase())) score += 3; });

        // Position bonus (first and last sentences)
        if (i === 0) score += 4;
        if (i === 1) score += 2;
        if (i === sentences.length - 1) score += 2;

        // Length preference (not too short, not too long)
        if (sWords.length >= 8 && sWords.length <= 35) score += 2;

        // Entity bonus
        entities.forEach(e => { if (s.toLowerCase().includes(e.toLowerCase())) score += 2; });

        // Focus adjustments
        if (focus === 'key_points') {
            if (/^(important|key|main|critical|essential|significant|notably)/i.test(s)) score += 3;
            if (/\b(conclusion|therefore|thus|hence|result|finding)\b/i.test(s)) score += 2;
        } else if (focus === 'academic') {
            if (/\b(study|research|analysis|evidence|data|method|hypothesis|theory)\b/i.test(s)) score += 3;
            if (/\b(shows|demonstrates|indicates|suggests|reveals)\b/i.test(s)) score += 2;
        }

        return { text: s, score, index: i };
    });

    // Determine how many sentences to include
    const targetRatios = { brief: 0.15, standard: 0.25, detailed: 0.4 };
    const ratio = targetRatios[length] || 0.25;
    const targetCount = Math.max(3, Math.min(Math.ceil(sentences.length * ratio), sentences.length));

    // Select top sentences, maintaining original order
    const selected = scoredSentences.sort((a, b) => b.score - a.score).slice(0, targetCount).sort((a, b) => a.index - b.index);

    const summaryText = selected.map(s => s.text).join(' ');
    const summaryWordCount = wordCount(summaryText);
    const reduction = origWordCount > 0 ? Math.round((1 - summaryWordCount / origWordCount) * 100) : 0;
    const readTime = Math.max(1, Math.ceil(summaryWordCount / 200));

    // Confidence based on coverage
    const coverage = selected.length / sentences.length;
    const keywordCoverage = keywords.filter(kw => summaryText.toLowerCase().includes(kw.toLowerCase())).length / Math.max(keywords.length, 1);
    const confidence = Math.min(98, Math.round((coverage * 0.4 + keywordCoverage * 0.4 + 0.2) * 100));

    // Generate sections
    const overview = selected.slice(0, Math.max(2, Math.ceil(selected.length * 0.4))).map(s => s.text).join(' ');
    const keyPoints = selected.slice(Math.ceil(selected.length * 0.2)).map(s => s.text);

    // Takeaways (derived from highest-scoring sentences)
    const takeaways = scoredSentences.sort((a, b) => b.score - a.score).slice(0, 5).map(s => s.text);

    return {
        summaryText, overview, keyPoints, takeaways,
        keywords, entities,
        origWordCount, summaryWordCount, reduction, readTime, confidence,
    };
}

function extractSentences(text) {
    return text.replace(/\r\n/g, '\n').replace(/\n{2,}/g, '\n\n').trim()
        .split(/(?<=[.!?])\s+(?=[A-Z])/)
        .map(s => s.trim())
        .filter(s => s.length > 15 && s.length < 500);
}

function extractKeywords(text, count) {
    const stopWords = new Set(['the','a','an','is','are','was','were','be','been','being','have','has','had','do','does','did','will','would','shall','should','may','might','can','could','must','need','dare','ought','used','to','of','in','for','on','with','at','by','from','as','into','through','during','before','after','above','below','between','out','off','over','under','again','further','then','once','here','there','when','where','why','how','all','both','each','few','more','most','other','some','such','no','nor','not','only','own','same','so','than','too','very','just','because','but','and','or','if','while','about','this','that','these','those','it','its','he','she','they','them','his','her','their','we','our','you','your','i','me','my','which','what','who','whom','also','however','therefore','thus','hence','moreover','furthermore','additionally','although','though','despite','nevertheless','meanwhile','according','based','using','called','known','including','such','within','without','among','throughout','during','since','until','against','upon','whether','rather','either','neither','yet','still','even','much','many','well','also','been','being','said','new','first','last','long','great','little','just','old','big','high','small','large','next','early','young','important','public','bad','same','able']);
    const freq = {};
    text.toLowerCase().replace(/[^a-z0-9\s'-]/g, '').split(/\s+/).forEach(w => {
        if (w.length > 3 && !stopWords.has(w)) freq[w] = (freq[w] || 0) + 1;
    });
    return Object.entries(freq).sort((a,b) => b[1] - a[1]).slice(0, count).map(e => e[0]);
}

function extractEntities(text) {
    const entities = new Set();
    // Capitalized multi-word phrases
    const matches = text.match(/[A-Z][a-z]+(?:\s+[A-Z][a-z]+)+/g);
    if (matches) matches.forEach(m => { if (m.length > 3 && m.length < 60) entities.add(m); });
    // Quoted terms
    const quoted = text.match(/"([^"]+)"/g);
    if (quoted) quoted.forEach(q => entities.add(q.replace(/"/g, '')));
    // Dates
    const dates = text.match(/\b\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}\b|\b(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},?\s*\d{4}\b/gi);
    if (dates) dates.forEach(d => entities.add(d));
    // Numbers with units
    const amounts = text.match(/\$[\d,.]+|\d+%|\d+\s*(?:million|billion|thousand|trillion)/gi);
    if (amounts) amounts.forEach(a => entities.add(a));
    return [...entities].slice(0, 15);
}

// ── Step Progress ──
function setStep(stepNum) {
    document.querySelectorAll('.step-item').forEach(el => {
        const s = parseInt(el.dataset.step);
        el.classList.remove('active', 'done');
        if (s < stepNum) el.classList.add('done');
        else if (s === stepNum) el.classList.add('active');
    });
    document.querySelectorAll('.step-line').forEach(el => {
        const l = parseInt(el.dataset.line);
        el.classList.toggle('done', l < stepNum);
    });
    const labels = { 1: 'Uploading file...', 2: 'Extracting text...', 3: 'Analyzing content...', 4: 'Complete!' };
    document.getElementById('stepStatusText').textContent = labels[stepNum] || 'Processing...';
}

// ── Generate Summary ──
async function generateSummary() {
    const btn = document.getElementById('summarizeBtn'), hint = document.getElementById('genHint');
    let content = '';
    if (state.sourceType === 'upload') {
        content = state.uploadedText;
        if (!content) { showToast('Upload a file first', 'error'); return; }
    } else {
        content = document.getElementById('sourceText').value.trim();
        if (!content) { showToast('Enter your content first', 'error'); return; }
        if (content.length < 50) { showToast('Enter more content (at least a paragraph)', 'error'); return; }
    }

    // Show progress
    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Analyzing...';
    hint.textContent = 'Processing your document...';
    document.getElementById('stepProgress').classList.remove('hidden');
    document.getElementById('summaryOutput').classList.add('hidden');

    setStep(1); await new Promise(r => setTimeout(r, 600));
    setStep(2); await new Promise(r => setTimeout(r, 800));
    hint.textContent = 'Analyzing content...';
    setStep(3); await new Promise(r => setTimeout(r, 1000 + Math.random() * 500));
    setStep(4); await new Promise(r => setTimeout(r, 400));

    // Analyze
    const result = analyzeText(content, state.summaryLength, state.focusArea);

    // Save
    const title = state.uploadedFileName || 'Text Summary';
    const item = {
        id: uid(), title, createdAt: Date.now(),
        sourceType: state.sourceType, length: state.summaryLength, focus: state.focusArea,
        origWordCount: result.origWordCount, summaryWordCount: result.summaryWordCount,
        reduction: result.reduction, confidence: result.confidence,
        keywords: result.keywords, entities: result.entities,
        overview: result.overview, keyPoints: result.keyPoints, takeaways: result.takeaways,
        summaryText: result.summaryText,
    };
    saveSummary(item); logStudy();
    state.currentSummaryId = item.id;

    // Render
    renderSummaryOutput(result, title);
    document.getElementById('stepProgress').classList.add('hidden');
    document.getElementById('summaryOutput').classList.remove('hidden');

    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Analyze Document';
    hint.innerHTML = '<i class="fa-solid fa-clock"></i> Takes a few seconds';

    showToast('Summary generated successfully', 'success');
    updateSidebar();
}

function renderSummaryOutput(result, title) {
    document.getElementById('statOrigWords').textContent = result.origWordCount.toLocaleString();
    document.getElementById('statSummWords').textContent = result.summaryWordCount.toLocaleString();
    document.getElementById('statReduction').textContent = result.reduction + '%';
    document.getElementById('statReadTime').textContent = result.readTime + 'm';

    // Confidence
    const confLevel = result.confidence >= 70 ? 'high' : result.confidence >= 40 ? 'medium' : 'low';
    document.getElementById('confFill').className = `conf-fill ${confLevel}`;
    document.getElementById('confFill').style.width = result.confidence + '%';
    document.getElementById('confPct').textContent = result.confidence + '%';
    document.getElementById('confPct').style.color = confLevel === 'high' ? 'var(--accent)' : confLevel === 'medium' ? 'var(--warning)' : 'var(--danger)';

    // Keywords
    const kwSection = document.getElementById('keywordsSection');
    if (document.getElementById('optKeywords').checked && result.keywords.length) {
        kwSection.classList.remove('hidden');
        document.getElementById('keywordsList').innerHTML = result.keywords.map(k => `<span class="kw-chip">${esc(k)}</span>`).join('');
    } else { kwSection.classList.add('hidden'); }

    // Build sections
    const sections = [];

    // Overview
    sections.push({ id: 'overview', icon: 'fa-file-lines', iconBg: 'var(--accent-dim)', iconColor: 'var(--accent)', title: 'Overview', content: `<p>${esc(result.overview)}</p>` });

    // Key Points
    if (result.keyPoints.length > 0) {
        sections.push({ id: 'keypoints', icon: 'fa-bullseye', iconBg: 'rgba(59,130,246,0.1)', iconColor: '#60a5fa', title: 'Key Points', badge: result.keyPoints.length, content: `<ul>${result.keyPoints.map(p => `<li>${esc(p)}</li>`).join('')}</ul>` });
    }

    // Entities
    if (document.getElementById('optEntities').checked && result.entities.length > 0) {
        sections.push({ id: 'entities', icon: 'fa-tags', iconBg: 'rgba(167,139,250,0.1)', iconColor: '#a78bfa', title: 'Key Entities', badge: result.entities.length, content: `<div class="flex flex-wrap gap-2">${result.entities.map(e => `<span class="kw-chip" style="background:rgba(167,139,250,0.1);color:#a78bfa;border-color:rgba(167,139,250,0.2)">${esc(e)}</span>`).join('')}</div>` });
    }

    // Takeaways
    if (document.getElementById('optTakeaways').checked && result.takeaways.length > 0) {
        sections.push({ id: 'takeaways', icon: 'fa-lightbulb', iconBg: 'var(--warning-dim)', iconColor: 'var(--warning)', title: 'Key Takeaways', badge: result.takeaways.length, content: `<ol>${result.takeaways.map(t => `<li>${esc(t)}</li>`).join('')}</ol>` });
    }

    const container = document.getElementById('summarySections');
    container.innerHTML = sections.map((sec, i) => `
        <div class="summary-section ${i < 2 ? 'open' : ''}" data-sec="${sec.id}">
            <div class="section-head" onclick="toggleSection(this)">
                <div class="section-icon" style="background:${sec.iconBg};color:${sec.iconColor}"><i class="fa-solid ${sec.icon}"></i></div>
                <span class="section-title">${sec.title}</span>
                ${sec.badge ? `<span class="section-badge">${sec.badge}</span>` : ''}
                <i class="fa-solid fa-chevron-down section-chevron"></i>
            </div>
            <div class="section-body"><div class="section-content">${sec.content}</div></div>
        </div>
    `).join('');
}

function toggleSection(head) {
    head.closest('.summary-section').classList.toggle('open');
}

function regenerateSummary() {
    document.getElementById('summaryOutput').classList.add('hidden');
    generateSummary();
}

function resetAll() {
    clearFile();
    document.getElementById('sourceText').value = '';
    document.getElementById('summaryOutput').classList.add('hidden');
    document.getElementById('stepProgress').classList.add('hidden');
    state.currentSummaryId = null;
}

function copySummary() {
    const item = getSummary(state.currentSummaryId);
    const text = item ? item.summaryText : document.getElementById('summarySections').innerText;
    navigator.clipboard.writeText(text).then(() => showToast('Summary copied to clipboard', 'success')).catch(() => showToast('Failed to copy', 'error'));
}

function exportTXT() {
    const item = getSummary(state.currentSummaryId);
    if (!item) return;
    const lines = [
        `Summary: ${item.title}`,
        `Generated: ${new Date(item.createdAt).toLocaleString()}`,
        `Original: ${item.origWordCount} words → Summary: ${item.summaryWordCount} words (${item.reduction}% reduction)`,
        `Confidence: ${item.confidence}%`,
        '',
        '--- Overview ---',
        item.overview,
        '',
        '--- Key Points ---',
        ...item.keyPoints.map((p, i) => `${i+1}. ${p}`),
        '',
        '--- Keywords ---',
        item.keywords.join(', '),
        '',
        '--- Key Takeaways ---',
        ...item.takeaways.map((t, i) => `${i+1}. ${t}`),
    ];
    if (item.entities.length) { lines.push('', '--- Key Entities ---', ...item.entities); }
    const blob = new Blob([lines.join('\n')], { type: 'text/plain' });
    const url = URL.createObjectURL(blob); const a = document.createElement('a');
    a.href = url; a.download = `${item.title.replace(/[^a-zA-Z0-9]/g, '_')}_summary.txt`;
    a.click(); URL.revokeObjectURL(url);
    showToast('Summary exported', 'success');
}

// ── Library ──
function renderLibrary(filter = '') {
    const items = getSummaries();
    const filtered = filter ? items.filter(s => s.title.toLowerCase().includes(filter.toLowerCase())) : items;
    const grid = document.getElementById('historyGrid'), empty = document.getElementById('emptyHistory');

    if (!filtered.length) { grid.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    grid.innerHTML = filtered.map(item => {
        const date = new Date(item.createdAt).toLocaleDateString();
        return `<div class="quiz-card" data-id="${item.id}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="font-700 text-[15px] truncate">${esc(item.title)}</h3>
                    <p class="text-xs mt-0.5 truncate" style="color:var(--text-muted)">${item.summaryWordCount} words · ${item.reduction}% reduction · ${item.length}</p>
                </div>
                <span class="text-sm font-800 shrink-0" style="color:${item.confidence>=70?'var(--accent)':item.confidence>=40?'var(--warning)':'var(--danger)'}">${item.confidence}%</span>
            </div>
            <div class="flex items-center gap-3 text-xs mb-3" style="color:var(--text-dim)">
                <span><i class="fa-solid fa-calendar"></i> ${date}</span>
                <span><i class="fa-solid fa-file-lines"></i> ${item.origWordCount} orig. words</span>
            </div>
            <div class="flex gap-2">
                <button onclick="event.stopPropagation();viewSummary('${item.id}')" class="action-btn primary text-xs flex-1" style="justify-content:center"><i class="fa-solid fa-eye"></i> View</button>
                <button onclick="event.stopPropagation();exportHistoryItem('${item.id}')" class="action-btn text-xs" title="Export"><i class="fa-solid fa-download"></i></button>
                <button onclick="event.stopPropagation();confirmDelete('${item.id}')" class="action-btn danger text-xs" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

function viewSummary(id) {
    const item = getSummary(id); if (!item) return;
    state.currentSummaryId = id;
    state.uploadedFileName = item.title;
    renderSummaryOutput({
        origWordCount: item.origWordCount, summaryWordCount: item.summaryWordCount,
        reduction: item.reduction, confidence: item.confidence, readTime: Math.max(1, Math.ceil(item.summaryWordCount / 200)),
        overview: item.overview, keyPoints: item.keyPoints, takeaways: item.takeaways,
        keywords: item.keywords, entities: item.entities,
    }, item.title);
    document.getElementById('summaryOutput').classList.remove('hidden');
    document.getElementById('stepProgress').classList.add('hidden');
    switchTab('create');
    document.getElementById('summaryOutput').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function exportHistoryItem(id) {
    state.currentSummaryId = id; exportTXT(); state.currentSummaryId = null;
}

function confirmDelete(id) {
    const card = document.querySelector(`.quiz-card[data-id="${id}"]`); if (!card || card.querySelector('.confirm-overlay')) return;
    const item = getSummary(id);
    const overlay = document.createElement('div'); overlay.className = 'confirm-overlay';
    overlay.innerHTML = `<p class="text-sm font-600" style="color:var(--text)">Delete "${esc(item?.title||'this')}?</p><div class="flex gap-2"><button class="action-btn text-xs" onclick="this.closest('.confirm-overlay').remove()">Cancel</button><button class="action-btn danger text-xs" onclick="doDelete('${id}')">Delete</button></div>`;
    card.appendChild(overlay);
}

function doDelete(id) { deleteSummary(id); renderLibrary(document.getElementById('searchHistory').value); updateSidebar(); showToast('Summary deleted', 'info'); }
document.getElementById('searchHistory').addEventListener('input', e => renderLibrary(e.target.value));

// ── Sidebar ──
function updateSidebar() {
    const items = getSummaries();
    const totalWordsSaved = items.reduce((s, i) => s + (i.origWordCount - i.summaryWordCount), 0);
    document.getElementById('sbTotal').textContent = items.length;
    document.getElementById('sbWordsSaved').textContent = totalWordsSaved > 1000 ? (totalWordsSaved / 1000).toFixed(1) + 'k' : totalWordsSaved;
    document.getElementById('sbDocs').textContent = new Set(items.map(i => i.title)).size;

    // Streak
    const today = new Date().toISOString().slice(0,10);
    const uniqueDays = [...new Set(state.studyHistory)].sort().reverse();
    let streak = 0;
    if (uniqueDays.length) {
        const check = new Date();
        for (let i = 0; i < 365; i++) {
            const ds = check.toISOString().slice(0,10);
            if (uniqueDays.includes(ds)) { streak++; check.setDate(check.getDate()-1); }
            else if (i === 0) { check.setDate(check.getDate()-1); continue; }
            else break;
        }
    }
    document.getElementById('sbStreak').textContent = streak;

    // Weekly bars
    const bars = document.querySelectorAll('#weeklyBars .week-bar');
    const dayMap = {}; state.studyHistory.forEach(d => { dayMap[d] = (dayMap[d]||0)+1; });
    const todayDate = new Date(); let maxC = 1; const weekC = [];
    for (let i=6;i>=0;i--) { const d = new Date(todayDate); d.setDate(d.getDate()-i); const ds = d.toISOString().slice(0,10); const c = dayMap[ds]||0; weekC.push(c); if (c>maxC) maxC=c; }
    bars.forEach((bar,idx) => { const pct = Math.max(5,Math.round((weekC[idx]/maxC)*100)); bar.style.height = pct+'%'; bar.style.background = `rgba(16,185,129,${0.2+(weekC[idx]/maxC)*0.5})`; });

    // Recent
    const recentList = document.getElementById('recentList');
    if (!items.length) { recentList.innerHTML = '<p class="text-xs text-center py-4" style="color:var(--text-dim)">No summaries yet</p>'; }
    else {
        recentList.innerHTML = items.slice(0,5).map(i => `
            <div class="history-item" onclick="viewSummary('${i.id}')">
                <p class="text-sm font-600 truncate">${esc(i.title)}</p>
                <div class="flex items-center gap-3 mt-1.5 text-[11px]" style="color:var(--text-dim)">
                    <span>${i.summaryWordCount} words</span>
                    <span style="color:var(--accent)">${i.reduction}% saved</span>
                </div>
            </div>
        `).join('');
    }
}

// ── Tab listeners ──
document.querySelectorAll('.tab-btn').forEach(btn => { btn.addEventListener('click', () => switchTab(btn.dataset.tab)); });

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('uploadZone').classList.remove('hidden');
    document.getElementById('textZone').classList.add('hidden');
    renderLibrary(); updateSidebar();
});
</script>
@endsection