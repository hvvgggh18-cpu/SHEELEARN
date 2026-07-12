@extends('layouts.dashboard-layout')

@section('title', 'Quizzes — SHEELEARN AI')

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
    .dashboard-quizzes { width: 100%; }

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
    textarea.field { resize: vertical; min-height: 180px; line-height: 1.7; }

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

    .progress-track { height: 5px; background: var(--surface-3); border-radius: 5px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--accent); border-radius: 5px; transition: width 0.4s ease; }

    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; text-align: center; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-top: 6px; color: var(--text-dim); }

    .sidebar-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; }

    .quiz-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; transition: all 0.25s; cursor: pointer; position: relative; }
    .quiz-card:hover { border-color: var(--border-hover); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }

    .toast { padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 8px 24px rgba(0,0,0,0.4); max-width: 360px; }
    .toast.show { transform: translateX(0); }
    .toast-success { background: #065f46; color: #a7f3d0; border: 1px solid rgba(16,185,129,0.3); }
    .toast-error { background: #7f1d1d; color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .toast-info { background: #1e3a5f; color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }

    /* Answer option */
    .answer-option {
        padding: 14px 18px; border-radius: 12px; border: 1px solid var(--border);
        background: var(--surface-2); cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; gap: 12px; font-size: 14px; color: var(--text-muted);
    }
    .answer-option:hover { border-color: var(--border-hover); background: var(--surface-3); color: var(--text); }
    .answer-option.selected { border-color: var(--accent-border); background: var(--accent-dim); color: var(--accent); }
    .answer-option.correct { border-color: rgba(16,185,129,0.5); background: rgba(16,185,129,0.12); color: var(--accent); }
    .answer-option.incorrect { border-color: rgba(239,68,68,0.5); background: rgba(239,68,68,0.1); color: var(--danger); }
    .answer-option .option-letter {
        width: 28px; height: 28px; border-radius: 8px; background: var(--surface-3);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; flex-shrink: 0; transition: all 0.2s;
    }
    .answer-option.selected .option-letter { background: var(--accent); color: #021a0f; }
    .answer-option.correct .option-letter { background: var(--accent); color: #021a0f; }
    .answer-option.incorrect .option-letter { background: var(--danger); color: white; }

    /* Question navigator */
    .q-nav-btn {
        width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--border);
        background: var(--surface-2); cursor: pointer; transition: all 0.15s;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: center;
    }
    .q-nav-btn:hover { border-color: var(--border-hover); color: var(--text); }
    .q-nav-btn.current { background: var(--accent); color: #021a0f; border-color: var(--accent); }
    .q-nav-btn.answered { background: var(--accent-dim); color: var(--accent); border-color: var(--accent-border); }
    .q-nav-btn.flagged { background: var(--warning-dim); color: var(--warning); border-color: rgba(245,158,11,0.3); }

    /* Type badge */
    .type-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .type-badge.mc { background: rgba(99,102,241,0.12); color: #818cf8; border: 1px solid rgba(99,102,241,0.25); }
    .type-badge.tf { background: rgba(245,158,11,0.12); color: var(--warning); border: 1px solid rgba(245,158,11,0.25); }

    /* Score circle */
    .score-ring { position: relative; width: 160px; height: 160px; margin: 0 auto; }
    .score-ring svg { transform: rotate(-90deg); }
    .score-ring .score-text { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }

    .kbd { display: inline-flex; align-items: center; justify-content: center; min-width: 24px; height: 22px; padding: 0 6px; border-radius: 5px; background: var(--surface-3); border: 1px solid var(--border); font-size: 11px; font-weight: 600; color: var(--text-dim); }

    .view { animation: fadeUp 0.3s ease; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    .recent-quiz-item { padding: 12px; border-radius: 10px; background: var(--surface-2); cursor: pointer; transition: all 0.2s; border: 1px solid transparent; }
    .recent-quiz-item:hover { border-color: var(--accent-border); background: var(--accent-dim); }

    .week-bar { flex: 1; border-radius: 4px 4px 0 0; transition: height 0.4s ease; min-height: 4px; }

    .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; }
    .toggle-row span { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .toggle-switch { width: 40px; height: 22px; border-radius: 11px; border: none; background: var(--surface-3); cursor: pointer; appearance: none; position: relative; transition: background 0.3s; flex-shrink: 0; }
    .toggle-switch:checked { background: var(--accent); }
    .toggle-switch::after { content: ''; position: absolute; width: 18px; height: 18px; border-radius: 50%; background: white; top: 2px; left: 2px; transition: left 0.3s; }
    .toggle-switch:checked::after { left: 20px; }

    .confirm-overlay { position: absolute; inset: 0; background: rgba(10,12,16,0.94); border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; z-index: 5; }

    @media (max-width: 1023px) { .main-grid { grid-template-columns: 1fr !important; } }
    @media (max-width: 640px) { .score-ring { width: 120px; height: 120px; } }
</style>
@endsection

@section('content')
<div class="dashboard-quizzes">

<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
<input type="file" id="fileInput" class="hidden" accept=".txt,.pdf,.md">
<input type="file" id="importInput" class="hidden" accept=".json">

<!-- Header -->
<header class="pt-6 pb-5 px-1">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-800 tracking-tight">Quizzes</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Generate, take, and track your quizzes</p>
        </div>
        <nav class="flex gap-2 bg-[var(--surface)] p-1.5 rounded-xl w-fit">
            <button class="tab-btn active" data-tab="create"><i class="fa-solid fa-plus text-xs"></i> Create</button>
            <button class="tab-btn" data-tab="library"><i class="fa-solid fa-folder-open text-xs"></i> Library</button>
            <button class="tab-btn" data-tab="take"><i class="fa-solid fa-pen-to-square text-xs"></i> Take Quiz</button>
        </nav>
    </div>
</header>

<!-- Two-Column Grid -->
<div class="main-grid grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 pb-8">

    <!-- ====== LEFT: Main Content ====== -->
    <div class="min-w-0">

        <!-- ============ CREATE VIEW ============ -->
        <section id="createView" class="view space-y-5">

            <div class="form-panel">
                <p class="section-label">Source</p>
                <div class="flex gap-3">
                    <button class="src-btn active" data-source="upload"><i class="fa-solid fa-cloud-arrow-up"></i><span>Upload File</span></button>
                    <button class="src-btn" data-source="text"><i class="fa-solid fa-paste"></i><span>Paste Text</span></button>
                    <button class="src-btn" data-source="manual"><i class="fa-solid fa-pen"></i><span>Type Manually</span></button>
                </div>
            </div>

            <div class="form-panel">
                <p class="section-label">Content</p>
                <div id="uploadZone" class="dropzone">
                    <i class="fa-solid fa-cloud-arrow-up text-4xl mb-4" style="color:var(--accent)"></i>
                    <p class="text-base font-600" style="color:var(--text)">Drag and drop your file here</p>
                    <p class="text-sm mt-1" style="color:var(--text-dim)">Supports TXT, PDF, and Markdown files</p>
                    <button type="button" onclick="document.getElementById('fileInput').click()" class="mt-5 px-6 py-2.5 rounded-lg text-sm font-600 transition" style="background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-border)">Browse Files</button>
                    <p id="uploadStatus" class="text-sm mt-4 hidden" style="color:var(--accent)"></p>
                </div>
                <div id="textZone" class="hidden">
                    <textarea id="quizContent" class="field" placeholder="Paste or type your study material here..."></textarea>
                    <p class="text-xs mt-2" style="color:var(--text-dim)">The AI will generate quiz questions from this content.</p>
                </div>
            </div>

            <div class="form-panel">
                <p class="section-label">Configuration</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Questions</p>
                        <div class="flex gap-2 flex-wrap">
                            <button class="pill" data-qcount="5">5</button>
                            <button class="pill active" data-qcount="10">10</button>
                            <button class="pill" data-qcount="15">15</button>
                            <button class="pill" data-qcount="20">20</button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Difficulty</p>
                        <div class="flex gap-2">
                            <button class="pill" data-diff="easy">Easy</button>
                            <button class="pill active" data-diff="medium">Medium</button>
                            <button class="pill" data-diff="hard">Hard</button>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Question Type</p>
                        <div class="flex gap-2 flex-wrap">
                            <button class="pill active" data-qtype="mixed">Mixed</button>
                            <button class="pill" data-qtype="mc">Multiple Choice</button>
                            <button class="pill" data-qtype="tf">True / False</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-panel">
                <p class="section-label">Options</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Time Limit</p>
                        <div class="flex gap-2 flex-wrap">
                            <button class="pill active" data-time="0">Unlimited</button>
                            <button class="pill" data-time="5">5 min</button>
                            <button class="pill" data-time="10">10 min</button>
                            <button class="pill" data-time="20">20 min</button>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="toggle-row"><span>Show Explanations</span><input type="checkbox" class="toggle-switch" id="optExplain" checked></div>
                        <div class="toggle-row"><span>Shuffle Questions</span><input type="checkbox" class="toggle-switch" id="optShuffle" checked></div>
                        <div class="toggle-row"><span>Show Hints</span><input type="checkbox" class="toggle-switch" id="optHints"></div>
                    </div>
                </div>
            </div>

            <div class="form-panel">
                <p class="section-label">Quiz Details</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Quiz Title</p>
                        <input id="quizTitle" type="text" class="field" placeholder="e.g. Biology Chapter 5 Assessment">
                    </div>
                    <div>
                        <p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Subject</p>
                        <input id="quizSubject" type="text" class="field" placeholder="e.g. Biology">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-1">
                <button id="generateBtn" class="gen-btn" onclick="generateQuiz()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Quiz
                </button>
                <span id="genHint" class="text-xs" style="color:var(--text-dim)"><i class="fa-solid fa-clock"></i> Takes a few seconds</span>
            </div>
        </section>

        <!-- ============ LIBRARY VIEW ============ -->
        <section id="libraryView" class="view hidden">
            <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
                <div class="relative flex-1 min-w-[200px]">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--text-dim)"></i>
                    <input id="searchQuizzes" type="text" class="field pl-9" placeholder="Search quizzes...">
                </div>
                <button onclick="document.getElementById('importInput').click()" class="action-btn"><i class="fa-solid fa-file-import"></i> Import</button>
            </div>
            <div id="quizGrid" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
            <div id="emptyLibrary" class="hidden text-center py-20">
                <i class="fa-solid fa-folder-open text-5xl mb-4" style="color:var(--text-dim)"></i>
                <p class="text-lg font-600" style="color:var(--text-muted)">No quizzes yet</p>
                <p class="text-sm mt-1" style="color:var(--text-dim)">Create your first quiz to get started.</p>
                <button onclick="switchTab('create')" class="action-btn primary mt-5"><i class="fa-solid fa-plus"></i> Create Quiz</button>
            </div>
        </section>

        <!-- ============ TAKE VIEW ============ -->
        <section id="takeView" class="view hidden">

            <!-- Empty State -->
            <div id="emptyTake" class="hidden text-center py-24">
                <i class="fa-solid fa-pen-to-square text-5xl mb-4" style="color:var(--text-dim)"></i>
                <p class="text-lg font-600" style="color:var(--text-muted)">No quiz selected</p>
                <p class="text-sm mt-1" style="color:var(--text-dim)">Choose a quiz from your library or create a new one.</p>
                <button onclick="switchTab('library')" class="action-btn primary mt-5"><i class="fa-solid fa-folder-open"></i> Browse Library</button>
            </div>

            <!-- Active Quiz -->
            <div id="activeQuiz" class="hidden space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 id="takeQuizTitle" class="text-lg font-700"></h2>
                        <p id="takeQuizMeta" class="text-xs mt-0.5" style="color:var(--text-muted)"></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="timerDisplay" class="text-sm font-700 px-3 py-1.5 rounded-lg" style="background:var(--surface-2);color:var(--text-muted)"></span>
                        <button onclick="exitQuiz()" class="action-btn"><i class="fa-solid fa-xmark"></i> Exit</button>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-600 mb-1.5">
                        <span id="takeProgress" style="color:var(--text-muted)">Question 1 of 10</span>
                        <span id="takeAnswered" style="color:var(--accent)">0 answered</span>
                    </div>
                    <div class="progress-track"><div id="takeProgressFill" class="progress-fill" style="width:0%"></div></div>
                </div>

                <!-- Question Card -->
                <div class="form-panel" style="padding:28px">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <span id="questionType" class="type-badge mc">MC</span>
                            <span id="questionDiff" class="text-xs font-600" style="color:var(--text-dim)"></span>
                        </div>
                        <button id="flagBtn" onclick="toggleFlag()" class="action-btn text-xs" style="padding:6px 12px"><i class="fa-solid fa-flag"></i> Flag</button>
                    </div>
                    <h3 id="questionText" class="text-lg font-700 mb-6 leading-relaxed"></h3>
                    <div id="answersContainer" class="space-y-3"></div>
                    <div id="hintSection" class="hidden mt-5 pt-4" style="border-top:1px solid var(--border)">
                        <button onclick="revealHint()" class="text-sm font-600 transition" style="color:var(--warning)"><i class="fa-solid fa-lightbulb"></i> Show Hint</button>
                        <p id="hintText" class="hidden text-sm mt-2" style="color:var(--text-muted)"></p>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between gap-3">
                    <button onclick="prevQuestion()" class="action-btn"><i class="fa-solid fa-arrow-left"></i> Previous</button>
                    <button onclick="submitQuiz()" class="action-btn primary"><i class="fa-solid fa-paper-plane"></i> Submit Quiz</button>
                    <button onclick="nextQuestion()">Next <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Results -->
            <div id="resultsView" class="hidden space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-700">Quiz Complete</h2>
                    <button onclick="exitQuiz()" class="action-btn"><i class="fa-solid fa-xmark"></i> Close</button>
                </div>

                <div class="form-panel text-center" style="padding:40px">
                    <div class="score-ring">
                        <svg width="160" height="160"><circle cx="80" cy="80" r="70" fill="none" stroke="var(--surface-3)" stroke-width="8"/><circle id="scoreCircle" cx="80" cy="80" r="70" fill="none" stroke="var(--accent)" stroke-width="8" stroke-linecap="round" stroke-dasharray="440" stroke-dashoffset="440"/></svg>
                        <div class="score-text">
                            <span id="resultPct" class="text-4xl font-800" style="color:var(--accent)">0%</span>
                            <span id="resultGrade" class="text-sm font-700" style="color:var(--text-muted)">—</span>
                        </div>
                    </div>
                    <p id="resultMessage" class="text-sm mt-4" style="color:var(--text-muted)"></p>
                    <div class="grid grid-cols-3 gap-4 mt-8 pt-6" style="border-top:1px solid var(--border)">
                        <div><p id="resultCorrect" class="text-2xl font-800" style="color:var(--accent)">0</p><p class="text-xs font-600 mt-1" style="color:var(--text-dim)">Correct</p></div>
                        <div><p id="resultIncorrect" class="text-2xl font-800" style="color:var(--danger)">0</p><p class="text-xs font-600 mt-1" style="color:var(--text-dim)">Incorrect</p></div>
                        <div><p id="resultSkipped" class="text-2xl font-800" style="color:var(--text-dim)">0</p><p class="text-xs font-600 mt-1" style="color:var(--text-dim)">Skipped</p></div>
                    </div>
                    <div class="flex justify-between text-sm mt-4 pt-4" style="border-top:1px solid var(--border);color:var(--text-muted)">
                        <span>Time Used</span><span id="resultTime" class="font-600" style="color:var(--text)">—</span>
                    </div>
                </div>

                <!-- Question Review -->
                <div class="form-panel">
                    <p class="section-label">Question Review</p>
                    <div id="reviewContainer" class="space-y-3"></div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button onclick="retakeQuiz()" class="action-btn primary"><i class="fa-solid fa-redo"></i> Retake Quiz</button>
                    <button onclick="exitQuiz()" class="action-btn"><i class="fa-solid fa-folder-open"></i> Back to Library</button>
                </div>
            </div>
        </section>
    </div>

    <!-- ====== RIGHT: Sidebar ====== -->
    <aside class="space-y-5">

        <!-- General Sidebar (Create / Library) -->
        <div id="generalSidebar">
            <div class="grid grid-cols-2 gap-3">
                <div class="stat-card"><p id="statQuizzes" class="stat-value" style="color:var(--accent)">0</p><p class="stat-label">Quizzes</p></div>
                <div class="stat-card"><p id="statQuestions" class="stat-value" style="color:#818cf8">0</p><p class="stat-label">Answered</p></div>
                <div class="stat-card"><p id="statAvgScore" class="stat-value" style="color:var(--warning)">0%</p><p class="stat-label">Avg Score</p></div>
                <div class="stat-card"><p id="statStreak" class="stat-value" style="color:#f472b6">0</p><p class="stat-label">Day Streak</p></div>
            </div>
            <div class="sidebar-panel mt-5">
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
            <div class="sidebar-panel mt-5">
                <div class="flex items-center justify-between mb-3"><p class="section-label" style="margin-bottom:0">Recent Quizzes</p><button onclick="switchTab('library')" class="text-xs font-600" style="color:var(--accent)">View All</button></div>
                <div id="recentQuizzesList" class="space-y-2 max-h-[240px] overflow-y-auto pr-1"><p class="text-xs text-center py-4" style="color:var(--text-dim)">No quizzes yet</p></div>
            </div>
            <div class="sidebar-panel mt-5">
                <p class="section-label">Quiz Tips</p>
                <div class="space-y-3">
                    <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:var(--accent-dim)"><i class="fa-solid fa-clock text-xs" style="color:var(--accent)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Read Carefully</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Pay attention to qualifiers like "always," "never," and "except."</p></div></div>
                    <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:rgba(129,140,248,0.1)"><i class="fa-solid fa-list-check text-xs" style="color:#818cf8"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Eliminate First</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Cross out clearly wrong options to improve your odds.</p></div></div>
                    <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:var(--warning-dim)"><i class="fa-solid fa-flag text-xs" style="color:var(--warning)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Flag and Return</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Skip hard questions and come back to them later.</p></div></div>
                </div>
            </div>
        </div>

        <!-- Quiz Sidebar (Take) -->
        <div id="quizSidebar" class="hidden">
            <div class="sidebar-panel">
                <p class="section-label">Quiz Info</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span style="color:var(--text-muted)">Title</span><span id="sbTitle" class="font-600 truncate ml-2">—</span></div>
                    <div class="flex justify-between"><span style="color:var(--text-muted)">Questions</span><span id="sbCount" class="font-600">—</span></div>
                    <div class="flex justify-between"><span style="color:var(--text-muted)">Difficulty</span><span id="sbDiff" class="font-600">—</span></div>
                    <div class="flex justify-between"><span style="color:var(--text-muted)">Answered</span><span id="sbAnswered" class="font-600" style="color:var(--accent)">0</span></div>
                </div>
            </div>
            <div class="sidebar-panel mt-5">
                <p class="section-label">Question Navigator</p>
                <div id="questionNav" class="flex flex-wrap gap-2"></div>
            </div>
            <div class="sidebar-panel mt-5">
                <p class="section-label">Keyboard Shortcuts</p>
                <div class="space-y-2 text-xs" style="color:var(--text-dim)">
                    <div class="flex justify-between"><span><span class="kbd">1</span>-<span class="kbd">4</span> Select answer</span></div>
                    <div class="flex justify-between"><span><span class="kbd">&larr;</span> Previous</span></div>
                    <div class="flex justify-between"><span><span class="kbd">&rarr;</span> Next</span></div>
                    <div class="flex justify-between"><span><span class="kbd">F</span> Flag</span></div>
                </div>
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
    questionCount: 10,
    difficulty: 'medium',
    questionType: 'mixed',
    timeLimit: 0,
    uploadedText: '',
    uploadedFileName: '',
    activeQuizId: null,
    currentQuestion: 0,
    timerInterval: null,
    startTime: null,
    elapsedSeconds: 0,
    quizCompleted: false,
    studyHistory: JSON.parse(localStorage.getItem('qz_history') || '[]'),
};

// Storage
function getQuizzes() { try { return JSON.parse(localStorage.getItem('qz_quizzes') || '[]'); } catch { return []; } }
function saveQuizzes(q) { try { localStorage.setItem('qz_quizzes', JSON.stringify(q)); } catch(e){} }
function getQuiz(id) { return getQuizzes().find(q => q.id === id) || null; }
function saveQuiz(quiz) { const all = getQuizzes(); const i = all.findIndex(q => q.id === quiz.id); if (i >= 0) all[i] = quiz; else all.unshift(quiz); saveQuizzes(all); }
function deleteQuiz(id) { saveQuizzes(getQuizzes().filter(q => q.id !== id)); }
function uid() { return 'q' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function shuffleArray(arr) { const a = [...arr]; for (let i = a.length - 1; i > 0; i--) { const j = Math.floor(Math.random() * (i + 1)); [a[i], a[j]] = [a[j], a[i]]; } return a; }
function truncate(s, n) { return s.length > n ? s.substring(0, n) + '...' : s; }
function logStudy() { const today = new Date().toISOString().slice(0,10); state.studyHistory.push(today); try { localStorage.setItem('qz_history', JSON.stringify(state.studyHistory)); } catch(e){} }

// Toast
function showToast(message, type = 'info') {
    const c = document.getElementById('toasts'), t = document.createElement('div');
    t.className = `toast toast-${type}`;
    const icons = { success:'check-circle', error:'exclamation-circle', info:'info-circle' };
    t.innerHTML = `<i class="fa-solid fa-${icons[type]||icons.info}"></i><span>${message}</span>`;
    c.appendChild(t); requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
}

// ── Question Generation ──
function extractSentences(text) {
    return text.replace(/\r\n/g,'\n').replace(/\n{2+}/g,'\n').split(/(?<=[.!?])\s+(?=[A-Z])/).map(s=>s.trim()).filter(s=>s.length>20&&s.length<500);
}

function extractDefinitions(sentences) {
    const defs = [];
    const patterns = [/^(.{3,50}?)\s+is\s+(.{10,})/i,/^(.{3,50}?)\s+are\s+(.{10,})/i,/^(.{3,50}?)\s+refers?\s+to\s+(.{10,})/i,/^(.{3,50}?)\s+means?\s+(.{10,})/i,/^(.{3,50}?)\s+can\s+be\s+defined\s+as\s+(.{10,})/i,/^(.{3,50}?)\s+describes?\s+(.{10,})/i];
    for (const s of sentences) {
        for (const p of patterns) {
            const m = s.match(p);
            if (m) { defs.push({ term: m[1].trim().replace(/^[-•*]\s*/,''), definition: m[2].trim().replace(/[.!?]+$/,''), source: s }); break; }
        }
    }
    return defs;
}

function extractTerms(text) {
    const terms = new Set();
    const q = text.match(/"([^"]+)"/g); if (q) q.forEach(t=>terms.add(t.replace(/"/g,'')));
    const c = text.match(/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/g); if (c) c.forEach(t=>terms.add(t));
    return [...terms];
}

const genericDistractors = [
    'A method used for data collection in research studies','A process involving chemical reactions and energy transfer',
    'A technique for analyzing and interpreting information','A type of mathematical calculation or formula',
    'A form of energy conversion in physical systems','A biological adaptation or evolutionary mechanism',
    'A structural component found in cellular biology','A fundamental principle of modern physics',
    'A statistical approach to measuring outcomes','A cognitive process related to memory formation',
    'An organizational framework for classification','A regulatory mechanism in biological systems',
    'A computational algorithm for processing data','A theoretical model for understanding phenomena',
];

function generateQuestionsFromText(text, count, difficulty, type) {
    const sentences = extractSentences(text);
    const definitions = extractDefinitions(sentences);
    const terms = extractTerms(text);
    let questions = [];
    let idx = 0;

    // MC from definitions
    if ((type === 'mc' || type === 'mixed') && definitions.length > 0) {
        const shuffledDefs = shuffleArray(definitions);
        for (const def of shuffledDefs) {
            if (questions.length >= count) break;
            questions.push(createMCQuestion(def, definitions, idx++));
        }
    }

    // TF from sentences
    if (type === 'tf' || (type === 'mixed' && questions.length < count)) {
        const shuffledSents = shuffleArray(sentences);
        for (const sent of shuffledSents) {
            if (questions.length >= count) break;
            if (sent.length > 25 && sent.length < 300) {
                questions.push(createTFQuestion(sent, idx++));
            }
        }
    }

    // MC from terms if still need more
    if (type === 'mc' || (type === 'mixed' && questions.length < count)) {
        for (const term of terms) {
            if (questions.length >= count) break;
            const relatedSentence = sentences.find(s => s.includes(term));
            if (relatedSentence) {
                questions.push(createMCFromTerm(term, relatedSentence, definitions, idx++));
            }
        }
    }

    // Fill remaining with sentence-based MC
    while (questions.length < count && sentences.length > 0) {
        const sent = sentences[idx % sentences.length];
        questions.push(createSentenceMC(sent, idx));
        idx++;
    }

    return shuffleArray(questions).slice(0, count);
}

function createMCQuestion(def, allDefs, index) {
    const question = `What is ${def.term}?`;
    const correct = truncate(def.definition, 90);
    const otherDefs = shuffleArray(allDefs.filter(d => d.term !== def.term));
    const distractors = otherDefs.slice(0, 3).map(d => truncate(d.definition, 90));
    let gi = 0;
    while (distractors.length < 3 && gi < genericDistractors.length) {
        if (!distractors.includes(genericDistractors[gi]) && genericDistractors[gi] !== correct) distractors.push(genericDistractors[gi]);
        gi++;
    }
    const options = shuffleArray([correct, ...distractors.slice(0,3)]);
    return { id:'q'+Date.now().toString(36)+index, type:'mc', question, options, correctIndex:options.indexOf(correct), explanation:`${def.term} is ${def.definition}.`, userAnswer:null, flagged:false };
}

function createTFQuestion(sentence, index) {
    const isTrue = Math.random() > 0.4;
    if (isTrue) {
        return { id:'q'+Date.now().toString(36)+index, type:'tf', question:sentence.replace(/[.!?]+$/,'.'), options:['True','False'], correctIndex:0, explanation:'This statement is correct as written.', userAnswer:null, flagged:false };
    } else {
        const modified = makeFalse(sentence);
        return { id:'q'+Date.now().toString(36)+index, type:'tf', question:modified, options:['True','False'], correctIndex:1, explanation:`The correct statement is: ${sentence.replace(/[.!?]+$/,'.')}`, userAnswer:null, flagged:false };
    }
}

function makeFalse(s) {
    let str = s.replace(/[.!?]+$/,'');
    if (/\balways\b/i.test(str)) return str.replace(/\balways\b/i,'never')+'.';
    if (/\bnever\b/i.test(str)) return str.replace(/\bnever\b/i,'always')+'.';
    if (/\ball\b/i.test(str)) return str.replace(/\ball\b/i,'no')+'.';
    if (/\bmust\b/i.test(str)) return str.replace(/\bmust\b/i,'does not need to')+'.';
    if (/\bcan\b/i.test(str)) return str.replace(/\bcan\b/i,'cannot')+'.';
    if (/\bis\b/i.test(str)) return str.replace(/\bis\b/i,'is not')+'.';
    if (/\bare\b/i.test(str)) return str.replace(/\bare\b/i,'are not')+'.';
    return 'It is not true that ' + str.charAt(0).toLowerCase() + str.slice(1) + '.';
}

function createMCFromTerm(term, sentence, allDefs, index) {
    const question = `Which of the following best relates to "${term}"?`;
    const correct = truncate(sentence, 90);
    const otherDefs = shuffleArray(allDefs.filter(d => !sentence.includes(d.term)));
    const distractors = otherDefs.slice(0,3).map(d => truncate(d.definition, 90));
    let gi = 0;
    while (distractors.length < 3 && gi < genericDistractors.length) { distractors.push(genericDistractors[gi++]); }
    const options = shuffleArray([correct, ...distractors.slice(0,3)]);
    return { id:'q'+Date.now().toString(36)+index, type:'mc', question, options, correctIndex:options.indexOf(correct), explanation:sentence, userAnswer:null, flagged:false };
}

function createSentenceMC(sentence, index) {
    const words = sentence.split(/\s+/).filter(w => w.length > 4);
    if (words.length < 4) return createTFQuestion(sentence, index);
    const keyWord = words[Math.floor(Math.random() * words.length)];
    const question = sentence.replace(keyWord, '______') + '\n\nWhich word best fills the blank?';
    const correct = keyWord.replace(/[.,;:!?]/g,'');
    const otherWords = shuffleArray(words.filter(w => w !== keyWord)).slice(0,3).map(w => w.replace(/[.,;:!?]/g,''));
    const fallbacks = ['concept','process','structure','function','system','method','element','factor'];
    let bi = 0;
    while (otherWords.length < 3) { otherWords.push(fallbacks[bi++]); }
    const options = shuffleArray([correct, ...otherWords.slice(0,3)]);
    return { id:'q'+Date.now().toString(36)+index, type:'mc', question, options, correctIndex:options.indexOf(correct), explanation:`The correct word is "${correct}". Full: ${sentence}`, userAnswer:null, flagged:false };
}

// ── Tab Switching ──
function switchTab(tab) {
    state.currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.getElementById('createView').classList.toggle('hidden', tab !== 'create');
    document.getElementById('libraryView').classList.toggle('hidden', tab !== 'library');
    document.getElementById('takeView').classList.toggle('hidden', tab !== 'take');
    document.getElementById('generalSidebar').classList.toggle('hidden', tab === 'take');
    document.getElementById('quizSidebar').classList.toggle('hidden', tab !== 'take');
    if (tab === 'library') renderLibrary();
    if (tab === 'take') renderTakeView();
    updateSidebar();
}

// ── Source Switching ──
document.querySelectorAll('.src-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.src-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active'); state.sourceType = btn.dataset.source;
        state.uploadedText = ''; state.uploadedFileName = '';
        document.getElementById('uploadStatus').classList.add('hidden');
        const isUpload = state.sourceType === 'upload';
        document.getElementById('uploadZone').classList.toggle('hidden', !isUpload);
        document.getElementById('textZone').classList.toggle('hidden', isUpload);
        document.getElementById('quizContent').placeholder = state.sourceType === 'manual' ? 'Type your study material here...' : 'Paste your study material here...';
    });
});

// ── Pills ──
function setupPills(sel, key, parser) {
    document.querySelectorAll(sel).forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll(sel).forEach(b => b.classList.remove('active'));
            btn.classList.add('active'); state[key] = parser ? parser(btn) : btn.dataset[key];
        });
    });
}
setupPills('[data-qcount]','questionCount',b=>parseInt(b.dataset.qcount));
setupPills('[data-diff]','difficulty');
setupPills('[data-qtype]','questionType',b=>b.dataset.qtype);
setupPills('[data-time]','timeLimit',b=>parseInt(b.dataset.time));

// ── File Upload ──
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
        status.textContent = `Loaded: ${file.name} (${text.trim().split(/\s+/).length} words)`; status.style.color = 'var(--accent)';
        showToast(`File "${file.name}" loaded`, 'success');
    } catch(err) { status.textContent = `Error: ${err.message}`; status.style.color = 'var(--danger)'; showToast('Failed to read file','error'); }
}

// ── Generate Quiz ──
async function generateQuiz() {
    const btn = document.getElementById('generateBtn'), hint = document.getElementById('genHint');
    let content = '';
    if (state.sourceType === 'upload') { content = state.uploadedText; if (!content) { showToast('Upload a file first','error'); return; } }
    else { content = document.getElementById('quizContent').value.trim(); if (!content) { showToast('Enter your study content','error'); return; } if (content.length < 30) { showToast('Enter more content (at least a few sentences)','error'); return; } }

    btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> Generating...'; hint.textContent = 'Analyzing content...';
    await new Promise(r => setTimeout(r, 800));
    hint.textContent = 'Creating questions...';
    await new Promise(r => setTimeout(r, 700));

    const questions = generateQuestionsFromText(content, state.questionCount, state.difficulty, state.questionType);
    if (!questions.length) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Quiz'; hint.innerHTML = '<i class="fa-solid fa-clock"></i> Takes a few seconds'; showToast('Could not generate questions. Add more text.','error'); return; }

    const title = document.getElementById('quizTitle').value.trim() || 'Untitled Quiz';
    const subject = document.getElementById('quizSubject').value.trim() || '';
    const quiz = { id: uid(), title, subject, difficulty: state.difficulty, questionType: state.questionType, timeLimit: state.timeLimit, createdAt: Date.now(), questions, completed: false, score: null, timeUsed: null };
    saveQuiz(quiz);

    // Reset form
    document.getElementById('quizTitle').value = ''; document.getElementById('quizSubject').value = '';
    document.getElementById('quizContent').value = ''; state.uploadedText = ''; state.uploadedFileName = '';
    document.getElementById('uploadStatus').classList.add('hidden'); fileInput.value = '';
    btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Quiz';
    hint.innerHTML = '<i class="fa-solid fa-clock"></i> Takes a few seconds';

    showToast(`Quiz "${title}" created with ${questions.length} questions`, 'success');
    startQuiz(quiz.id);
}

// ── Start / Take Quiz ──
function startQuiz(id) {
    const quiz = getQuiz(id);
    if (!quiz) return;
    // Reset answers for retake
    quiz.questions.forEach(q => { q.userAnswer = null; q.flagged = false; });
    quiz.completed = false; quiz.score = null; quiz.timeUsed = null;
    saveQuiz(quiz);
    state.activeQuizId = id; state.currentQuestion = 0; state.quizCompleted = false;
    state.startTime = Date.now(); state.elapsedSeconds = 0;
    if (state.timeLimit > 0) { state.elapsedSeconds = state.timeLimit * 60; }
    startTimer();
    switchTab('take');
}

function startTimer() {
    clearInterval(state.timerInterval);
    state.timerInterval = setInterval(() => {
        if (state.quizCompleted) { clearInterval(state.timerInterval); return; }
        if (state.timeLimit > 0) {
            state.elapsedSeconds--;
            if (state.elapsedSeconds <= 0) { state.elapsedSeconds = 0; submitQuiz(); return; }
        } else { state.elapsedSeconds = Math.floor((Date.now() - state.startTime) / 1000); }
        updateTimerDisplay();
    }, 1000);
}

function updateTimerDisplay() {
    const m = Math.floor(state.elapsedSeconds / 60), s = state.elapsedSeconds % 60;
    const display = document.getElementById('timerDisplay');
    display.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    if (state.timeLimit > 0 && state.elapsedSeconds < 60) display.style.color = 'var(--danger)';
    else display.style.color = 'var(--text-muted)';
}

function renderTakeView() {
    const emptyEl = document.getElementById('emptyTake'), activeEl = document.getElementById('activeQuiz'), resultsEl = document.getElementById('resultsView');
    if (!state.activeQuizId) { emptyEl.classList.remove('hidden'); activeEl.classList.add('hidden'); resultsEl.classList.add('hidden'); return; }
    const quiz = getQuiz(state.activeQuizId);
    if (!quiz) { state.activeQuizId = null; emptyEl.classList.remove('hidden'); activeEl.classList.add('hidden'); resultsEl.classList.add('hidden'); return; }

    if (quiz.completed) { emptyEl.classList.add('hidden'); activeEl.classList.add('hidden'); resultsEl.classList.remove('hidden'); renderResults(quiz); return; }

    emptyEl.classList.add('hidden'); activeEl.classList.remove('hidden'); resultsEl.classList.add('hidden');
    document.getElementById('takeQuizTitle').textContent = quiz.title;
    document.getElementById('takeQuizMeta').textContent = `${quiz.questions.length} questions · ${quiz.difficulty} · ${quiz.subject || 'No subject'}`;
    document.getElementById('sbTitle').textContent = quiz.title;
    document.getElementById('sbCount').textContent = quiz.questions.length;
    document.getElementById('sbDiff').textContent = quiz.difficulty.charAt(0).toUpperCase() + quiz.difficulty.slice(1);
    renderQuestion();
    updateTimerDisplay();
}

function renderQuestion() {
    const quiz = getQuiz(state.activeQuizId); if (!quiz) return;
    const q = quiz.questions[state.currentQuestion]; if (!q) return;

    document.getElementById('questionType').textContent = q.type === 'mc' ? 'MC' : 'T/F';
    document.getElementById('questionType').className = `type-badge ${q.type}`;
    document.getElementById('questionDiff').textContent = quiz.difficulty;
    document.getElementById('questionText').textContent = q.question;

    // Flag button
    const flagBtn = document.getElementById('flagBtn');
    flagBtn.style.color = q.flagged ? 'var(--warning)' : '';
    flagBtn.style.borderColor = q.flagged ? 'rgba(245,158,11,0.3)' : '';

    // Answers
    const container = document.getElementById('answersContainer');
    const letters = ['A','B','C','D'];
    container.innerHTML = q.options.map((opt, i) => {
        const selected = q.userAnswer === i ? 'selected' : '';
        return `<div class="answer-option ${selected}" onclick="selectAnswer(${i})"><span class="option-letter">${letters[i]||i+1}</span><span>${esc(opt)}</span></div>`;
    }).join('');

    // Hint
    const hintSection = document.getElementById('hintSection');
    const optHints = document.getElementById('optHints');
    if (optHints && optHints.checked && q.explanation) {
        hintSection.classList.remove('hidden');
        document.getElementById('hintText').classList.add('hidden');
        document.getElementById('hintText').textContent = q.explanation;
    } else { hintSection.classList.add('hidden'); }

    // Progress
    const answered = quiz.questions.filter(q => q.userAnswer !== null).length;
    document.getElementById('takeProgress').textContent = `Question ${state.currentQuestion+1} of ${quiz.questions.length}`;
    document.getElementById('takeAnswered').textContent = `${answered} answered`;
    document.getElementById('takeProgressFill').style.width = `${((state.currentQuestion+1)/quiz.questions.length)*100}%`;
    document.getElementById('sbAnswered').textContent = answered;

    // Question navigator
    renderQuestionNav(quiz);
}

function renderQuestionNav(quiz) {
    const nav = document.getElementById('questionNav');
    nav.innerHTML = quiz.questions.map((q, i) => {
        let cls = 'q-nav-btn';
        if (i === state.currentQuestion) cls += ' current';
        else if (q.flagged) cls += ' flagged';
        else if (q.userAnswer !== null) cls += ' answered';
        return `<button class="${cls}" onclick="goToQuestion(${i})">${i+1}</button>`;
    }).join('');
}

function selectAnswer(index) {
    const quiz = getQuiz(state.activeQuizId); if (!quiz || state.quizCompleted) return;
    quiz.questions[state.currentQuestion].userAnswer = index;
    saveQuiz(quiz); renderQuestion();
}

function toggleFlag() {
    const quiz = getQuiz(state.activeQuizId); if (!quiz) return;
    quiz.questions[state.currentQuestion].flagged = !quiz.questions[state.currentQuestion].flagged;
    saveQuiz(quiz); renderQuestion();
    showToast(quiz.questions[state.currentQuestion].flagged ? 'Question flagged' : 'Flag removed', 'info');
}

function revealHint() { document.getElementById('hintText').classList.toggle('hidden'); }

function prevQuestion() { const q = getQuiz(state.activeQuizId); if (!q) return; state.currentQuestion = Math.max(0, state.currentQuestion-1); renderQuestion(); }
function nextQuestion() { const q = getQuiz(state.activeQuizId); if (!q) return; state.currentQuestion = Math.min(q.questions.length-1, state.currentQuestion+1); renderQuestion(); }
function goToQuestion(i) { state.currentQuestion = i; renderQuestion(); }

// ── Submit Quiz ──
function submitQuiz() {
    const quiz = getQuiz(state.activeQuizId); if (!quiz || state.quizCompleted) return;
    const unanswered = quiz.questions.filter(q => q.userAnswer === null).length;
    if (unanswered > 0) {
        // Show inline confirmation
        const card = document.querySelector('#activeQuiz .form-panel');
        if (card.querySelector('.confirm-overlay')) return;
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';
        overlay.innerHTML = `<p class="text-sm font-600" style="color:var(--text)">${unanswered} question${unanswered>1?'s':''} unanswered</p><p class="text-xs" style="color:var(--text-dim)">Submit anyway?</p><div class="flex gap-2 mt-2"><button class="action-btn text-xs" onclick="this.closest('.confirm-overlay').remove()">Go Back</button><button class="action-btn primary text-xs" onclick="forceSubmit()">Submit</button></div>`;
        card.style.position = 'relative'; card.appendChild(overlay);
        return;
    }
    forceSubmit();
}

function forceSubmit() {
    const quiz = getQuiz(state.activeQuizId); if (!quiz) return;
    clearInterval(state.timerInterval);
    const timeUsed = state.timeLimit > 0 ? (state.timeLimit*60 - state.elapsedSeconds) : state.elapsedSeconds;
    const correct = quiz.questions.filter(q => q.userAnswer === q.correctIndex).length;
    const total = quiz.questions.length;
    quiz.completed = true;
    quiz.score = Math.round((correct/total)*100);
    quiz.timeUsed = timeUsed;
    saveQuiz(quiz);
    state.quizCompleted = true;
    logStudy();
    renderTakeView();
}

// ── Results ──
function renderResults(quiz) {
    const correct = quiz.questions.filter(q => q.userAnswer === q.correctIndex).length;
    const incorrect = quiz.questions.filter(q => q.userAnswer !== null && q.userAnswer !== q.correctIndex).length;
    const skipped = quiz.questions.filter(q => q.userAnswer === null).length;
    const pct = quiz.score || 0;

    // Score ring
    const circle = document.getElementById('scoreCircle');
    const offset = 440 - (440 * pct / 100);
    circle.style.strokeDashoffset = offset;
    circle.style.stroke = pct >= 70 ? 'var(--accent)' : pct >= 50 ? 'var(--warning)' : 'var(--danger)';
    document.getElementById('resultPct').textContent = pct + '%';
    document.getElementById('resultPct').style.color = pct >= 70 ? 'var(--accent)' : pct >= 50 ? 'var(--warning)' : 'var(--danger)';

    const grade = pct >= 90 ? 'A+' : pct >= 80 ? 'A' : pct >= 70 ? 'B' : pct >= 60 ? 'C' : pct >= 50 ? 'D' : 'F';
    document.getElementById('resultGrade').textContent = `Grade: ${grade}`;
    document.getElementById('resultMessage').textContent = pct >= 90 ? 'Outstanding work! You have mastered this material.' : pct >= 70 ? 'Good job! Review the questions you missed to improve further.' : pct >= 50 ? 'Fair performance. Focus on reviewing the incorrect topics.' : 'Keep studying! Review the material and try again.';
    document.getElementById('resultCorrect').textContent = correct;
    document.getElementById('resultIncorrect').textContent = incorrect;
    document.getElementById('resultSkipped').textContent = skipped;

    const m = Math.floor(quiz.timeUsed/60), s = quiz.timeUsed%60;
    document.getElementById('resultTime').textContent = `${m}m ${s}s`;

    // Review
    const letters = ['A','B','C','D'];
    const reviewContainer = document.getElementById('reviewContainer');
    reviewContainer.innerHTML = quiz.questions.map((q, i) => {
        const isCorrect = q.userAnswer === q.correctIndex;
        const isSkipped = q.userAnswer === null;
        const statusColor = isSkipped ? 'var(--text-dim)' : isCorrect ? 'var(--accent)' : 'var(--danger)';
        const statusIcon = isSkipped ? 'fa-minus' : isCorrect ? 'fa-check' : 'fa-xmark';
        const statusText = isSkipped ? 'Skipped' : isCorrect ? 'Correct' : 'Incorrect';
        return `<div style="padding:16px;border-radius:12px;border:1px solid var(--border);background:var(--surface-2)">
            <div class="flex items-center gap-3 mb-3">
                <span class="text-xs font-700" style="color:var(--text-dim)">Q${i+1}</span>
                <span class="type-badge ${q.type}" style="font-size:10px;padding:2px 8px">${q.type==='mc'?'MC':'T/F'}</span>
                <span class="ml-auto text-xs font-600 flex items-center gap-1" style="color:${statusColor}"><i class="fa-solid ${statusIcon}"></i> ${statusText}</span>
            </div>
            <p class="text-sm font-600 mb-2">${esc(q.question)}</p>
            ${!isSkipped ? `<p class="text-xs" style="color:${isCorrect?'var(--accent)':'var(--danger)'}">Your answer: ${letters[q.userAnswer]||q.options[q.userAnswer]} — ${esc(q.options[q.userAnswer]||'')}</p>` : ''}
            ${!isCorrect ? `<p class="text-xs mt-1" style="color:var(--accent)">Correct: ${letters[q.correctIndex]} — ${esc(q.options[q.correctIndex])}</p>` : ''}
            ${document.getElementById('optExplain')?.checked && q.explanation ? `<p class="text-xs mt-2" style="color:var(--text-dim)"><i class="fa-solid fa-lightbulb" style="color:var(--warning)"></i> ${esc(q.explanation)}</p>` : ''}
        </div>`;
    }).join('');
}

function retakeQuiz() { if (state.activeQuizId) startQuiz(state.activeQuizId); }
function exitQuiz() { clearInterval(state.timerInterval); state.activeQuizId = null; state.quizCompleted = false; switchTab('library'); }

// ── Library ──
function renderLibrary(filter = '') {
    const quizzes = getQuizzes();
    const filtered = filter ? quizzes.filter(q => q.title.toLowerCase().includes(filter.toLowerCase())) : quizzes;
    const grid = document.getElementById('quizGrid'), empty = document.getElementById('emptyLibrary');

    if (!filtered.length) { grid.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    grid.innerHTML = filtered.map(quiz => {
        const total = quiz.questions.length;
        const scoreDisplay = quiz.completed ? `${quiz.score}%` : '—';
        const scoreColor = !quiz.completed ? 'var(--text-dim)' : quiz.score >= 70 ? 'var(--accent)' : quiz.score >= 50 ? 'var(--warning)' : 'var(--danger)';
        const date = new Date(quiz.createdAt).toLocaleDateString();
        return `<div class="quiz-card" data-id="${quiz.id}">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="font-700 text-[15px] truncate">${esc(quiz.title)}</h3>
                    <p class="text-xs mt-0.5 truncate" style="color:var(--text-muted)">${esc(quiz.subject||'No subject')} · ${quiz.difficulty}</p>
                </div>
                <span class="text-lg font-800 shrink-0" style="color:${scoreColor}">${scoreDisplay}</span>
            </div>
            <div class="flex items-center gap-3 text-xs mb-3" style="color:var(--text-dim)">
                <span><i class="fa-solid fa-list"></i> ${total} questions</span>
                <span><i class="fa-solid fa-calendar"></i> ${date}</span>
                ${quiz.completed ? '<span style="color:var(--accent)"><i class="fa-solid fa-check-circle"></i> Completed</span>' : '<span><i class="fa-solid fa-clock"></i> In progress</span>'}
            </div>
            <div class="flex gap-2">
                <button onclick="event.stopPropagation();startQuiz('${quiz.id}')" class="action-btn primary text-xs flex-1" style="justify-content:center"><i class="fa-solid fa-play"></i> ${quiz.completed?'Retake':'Take'}</button>
                <button onclick="event.stopPropagation();exportQuiz('${quiz.id}')" class="action-btn text-xs" title="Export"><i class="fa-solid fa-download"></i></button>
                <button onclick="event.stopPropagation();confirmDeleteQuiz('${quiz.id}')" class="action-btn danger text-xs" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

function exportQuiz(id) {
    const quiz = getQuiz(id); if (!quiz) return;
    const blob = new Blob([JSON.stringify(quiz,null,2)],{type:'application/json'});
    const url = URL.createObjectURL(blob); const a = document.createElement('a');
    a.href = url; a.download = `${quiz.title.replace(/[^a-zA-Z0-9]/g,'_')}.json`; a.click(); URL.revokeObjectURL(url);
    showToast(`Exported "${quiz.title}"`,'success');
}

function confirmDeleteQuiz(id) {
    const quiz = getQuiz(id); if (!quiz) return;
    const card = document.querySelector(`.quiz-card[data-id="${id}"]`); if (!card || card.querySelector('.confirm-overlay')) return;
    const overlay = document.createElement('div'); overlay.className = 'confirm-overlay';
    overlay.innerHTML = `<p class="text-sm font-600" style="color:var(--text)">Delete "${esc(quiz.title)}"?</p><p class="text-xs" style="color:var(--text-dim)">This cannot be undone.</p><div class="flex gap-2 mt-1"><button class="action-btn text-xs" onclick="this.closest('.confirm-overlay').remove()">Cancel</button><button class="action-btn danger text-xs" onclick="doDeleteQuiz('${id}')">Delete</button></div>`;
    card.appendChild(overlay);
}

function doDeleteQuiz(id) { deleteQuiz(id); if (state.activeQuizId === id) state.activeQuizId = null; renderLibrary(document.getElementById('searchQuizzes').value); updateSidebar(); showToast('Quiz deleted','info'); }

document.getElementById('importInput').addEventListener('change', e => {
    const file = e.target.files[0]; if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        try {
            const quiz = JSON.parse(reader.result);
            if (!quiz.questions || !Array.isArray(quiz.questions) || !quiz.title) throw new Error('Invalid');
            quiz.id = uid(); saveQuiz(quiz); renderLibrary(); updateSidebar();
            showToast(`Imported "${quiz.title}"`,'success');
        } catch { showToast('Invalid file format','error'); }
    };
    reader.readAsText(file); e.target.value = '';
});

document.getElementById('searchQuizzes').addEventListener('input', e => renderLibrary(e.target.value));

// ── Sidebar ──
function updateSidebar() {
    const quizzes = getQuizzes();
    const completed = quizzes.filter(q => q.completed);
    const totalQ = completed.reduce((s,q) => s + q.questions.length, 0);
    const avgScore = completed.length ? Math.round(completed.reduce((s,q) => s + (q.score||0), 0) / completed.length) : 0;

    document.getElementById('statQuizzes').textContent = quizzes.length;
    document.getElementById('statQuestions').textContent = totalQ;
    document.getElementById('statAvgScore').textContent = avgScore + '%';

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
    document.getElementById('statStreak').textContent = streak;

    // Weekly bars
    const bars = document.querySelectorAll('#weeklyBars .week-bar');
    const dayMap = {}; state.studyHistory.forEach(d => { dayMap[d] = (dayMap[d]||0)+1; });
    const todayDate = new Date(); let maxC = 1; const weekC = [];
    for (let i=6;i>=0;i--) { const d = new Date(todayDate); d.setDate(d.getDate()-i); const ds = d.toISOString().slice(0,10); const c = dayMap[ds]||0; weekC.push(c); if (c>maxC) maxC=c; }
    bars.forEach((bar,idx) => { const pct = Math.max(5,Math.round((weekC[idx]/maxC)*100)); bar.style.height = pct+'%'; bar.style.background = `rgba(16,185,129,${0.2+(weekC[idx]/maxC)*0.5})`; });

    // Recent quizzes
    const recentList = document.getElementById('recentQuizzesList');
    if (!quizzes.length) { recentList.innerHTML = '<p class="text-xs text-center py-4" style="color:var(--text-dim)">No quizzes yet</p>'; }
    else {
        recentList.innerHTML = quizzes.slice(0,5).map(q => {
            const scoreTxt = q.completed ? `${q.score}%` : 'Not taken';
            const scoreClr = !q.completed ? 'var(--text-dim)' : q.score >= 70 ? 'var(--accent)' : 'var(--warning)';
            return `<div class="recent-quiz-item" onclick="startQuiz('${q.id}')"><p class="text-sm font-600 truncate">${esc(q.title)}</p><div class="flex items-center gap-3 mt-1.5 text-[11px]" style="color:var(--text-dim)"><span>${q.questions.length} Qs</span><span style="color:${scoreClr}">${scoreTxt}</span></div></div>`;
        }).join('');
    }
}

// ── Keyboard Shortcuts ──
document.addEventListener('keydown', e => {
    if (state.currentTab !== 'take' || !state.activeQuizId || state.quizCompleted) return;
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
    const quiz = getQuiz(state.activeQuizId); if (!quiz) return;
    const q = quiz.questions[state.currentQuestion];

    if (e.key >= '1' && e.key <= '4') { const i = parseInt(e.key)-1; if (i < q.options.length) { selectAnswer(i); e.preventDefault(); } }
    else if (e.code === 'ArrowRight') { e.preventDefault(); nextQuestion(); }
    else if (e.code === 'ArrowLeft') { e.preventDefault(); prevQuestion(); }
    else if (e.key.toLowerCase() === 'f') { e.preventDefault(); toggleFlag(); }
});

// ── Tab Listeners ──
document.querySelectorAll('.tab-btn').forEach(btn => { btn.addEventListener('click', () => switchTab(btn.dataset.tab)); });

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('uploadZone').classList.remove('hidden');
    document.getElementById('textZone').classList.add('hidden');
    renderLibrary(); updateSidebar();
});
</script>
@endsection