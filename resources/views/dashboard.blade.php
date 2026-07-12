@extends('layouts.dashboard-layout')

@section('title', 'Dashboard — SHEELEARN AI')

@section('styles')
<style>
    :root {
        --surface: rgba(16,22,36,0.72);
        --surface-2: rgba(16,22,36,0.55);
        --surface-3: rgba(16,22,36,0.36);
        --border: rgba(226,232,240,0.07);
        --border-hover: rgba(45,212,220,0.22);
        --accent: #2dd4dc;
        --accent-hover: #38bdf8;
        --accent-dim: rgba(45,212,220,0.12);
        --accent-border: rgba(45,212,220,0.22);
        --indigo: #818cf8;
        --emerald: #34d399;
        --amber: #fb923c;
        --text: #eef2f8;
        --text-muted: rgba(238,242,248,0.68);
        --text-dim: rgba(238,242,248,0.42);
        --danger: #f87171;
        --warning: #fbbf24;
        --success: #34d399;
        --ring-1: #2dd4dc;
        --ring-2: #818cf8;
    }

    body.theme-light {
        --surface: rgba(255,255,255,0.92);
        --surface-2: rgba(255,255,255,0.86);
        --surface-3: rgba(248,250,252,0.90);
        --border: rgba(15,23,42,0.08);
        --border-hover: rgba(59,130,246,0.12);
        --accent: #22d3ee;
        --accent-hover: #38bdf8;
        --accent-dim: rgba(34,211,238,0.12);
        --accent-border: rgba(34,211,238,0.28);
        --text: #0f172a;
        --text-muted: rgba(15,23,42,0.65);
        --text-dim: rgba(15,23,42,0.45);
        --ring-1: #22d3ee;
        --ring-2: #818cf8;
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation-duration: 0.001ms !important; animation-iteration-count: 1 !important; transition-duration: 0.001ms !important; }
    }

    /* ---------- Header ---------- */
    .hero-wrap {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        padding: 34px 28px;
        background: linear-gradient(180deg, rgba(45,212,220,0.05), transparent 60%), var(--surface-3);
        border: 1px solid var(--border);
    }
    .hero-blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(60px);
        opacity: 0.35;
        pointer-events: none;
        animation: drift 14s ease-in-out infinite;
    }
    .hero-blob.b1 { width: 260px; height: 260px; background: var(--accent); top: -120px; right: 40px; }
    .hero-blob.b2 { width: 220px; height: 220px; background: var(--indigo); bottom: -140px; right: 260px; animation-delay: -6s; }
    @keyframes drift {
        0%, 100% { transform: translate(0,0); }
        50% { transform: translate(-16px, 14px); }
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--text-dim);
    }
    .eyebrow::before {
        content: '';
        width: 14px;
        height: 2px;
        border-radius: 2px;
        background: var(--accent);
    }

    .gradient-text {
        background: linear-gradient(135deg, var(--accent) 0%, var(--indigo) 55%, var(--emerald) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .status-cluster {
        display: flex;
        align-items: stretch;
        gap: 0;
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        background: var(--surface);
        backdrop-filter: blur(6px);
    }
    .status-cell {
        padding: 14px 20px;
        min-width: 128px;
    }
    .status-cell + .status-cell { border-left: 1px solid var(--border); }
    .status-cell.streak-cell {
        background: linear-gradient(180deg, rgba(251,146,60,0.08), transparent);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .flame-icon {
        font-size: 19px;
        color: var(--amber);
        animation: flicker 2.4s ease-in-out infinite;
    }
    @keyframes flicker {
        0%, 100% { transform: scale(1) rotate(0deg); opacity: 1; }
        50% { transform: scale(1.08) rotate(-2deg); opacity: 0.85; }
    }

    /* ---------- Stat cards ---------- */
    .stat-card {
        position: relative;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
        overflow: hidden;
        transition: transform 0.25s cubic-bezier(0.16,1,0.3,1), border-color 0.25s;
    }
    .stat-card:hover {
        border-color: var(--border-hover);
        transform: translateY(-3px);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        width: 90px; height: 90px;
        border-radius: 50%;
        filter: blur(30px);
        top: -30px; right: -30px;
        opacity: 0.5;
        background: var(--glow, var(--accent));
        pointer-events: none;
    }
    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        position: relative;
        z-index: 1;
    }
    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
        position: relative;
        z-index: 1;
    }

    /* ---------- Section headers ---------- */
    .section-title {
        font-size: 15px;
        font-weight: 800;
        letter-spacing: -0.01em;
    }
    .section-link {
        font-size: 13px;
        font-weight: 700;
        color: var(--accent);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: gap 0.2s;
    }
    .section-link:hover { gap: 8px; }

    /* ---------- Deck / continue-learning cards ---------- */
    .deck-item {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform 0.2s cubic-bezier(0.16,1,0.3,1), border-color 0.2s;
        text-decoration: none;
        color: var(--text);
    }
    .deck-item:hover {
        border-color: var(--border-hover);
        transform: translateY(-3px);
    }
    .deck-meta-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        background: var(--surface-2);
        border: 1px solid var(--border);
        padding: 3px 8px;
        border-radius: 999px;
    }
    .ring-wrap { position: relative; width: 56px; height: 56px; flex-shrink: 0; }
    .ring-wrap .ring-pct {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800;
    }

    /* ---------- Quick actions ---------- */
    .action-card {
        position: relative;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 22px 16px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        display: block;
        color: var(--text);
        overflow: hidden;
    }
    .action-card:hover {
        border-color: var(--accent-border);
        transform: translateY(-4px);
        box-shadow: 0 10px 30px rgba(45,212,220,0.08);
    }
    .action-card .fa-arrow-right {
        position: absolute;
        top: 14px; right: 14px;
        font-size: 11px;
        color: var(--text-dim);
        opacity: 0;
        transform: translate(-4px, 4px);
        transition: all 0.25s;
    }
    .action-card:hover .fa-arrow-right { opacity: 1; transform: translate(0,0); color: var(--accent); }
    .action-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 19px;
        transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
    }
    .action-card:hover .action-icon { transform: scale(1.08) rotate(-3deg); }

    /* ---------- Progress panel ---------- */
    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 20px;
    }
    .progress-row-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-muted);
    }
    .progress-row-label i { color: var(--text-dim); font-size: 12px; width: 14px; }
    .progress-track {
        height: 7px;
        background: var(--surface-3);
        border-radius: 99px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent), var(--indigo));
        border-radius: 99px;
        transition: width 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .week-dot {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        transition: all 0.2s;
        position: relative;
    }
    .week-dot.active {
        background: var(--accent-dim);
        border: 1px solid var(--accent-border);
        color: var(--accent);
    }
    .week-dot.inactive {
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text-dim);
    }
    .week-dot.today {
        background: linear-gradient(135deg, var(--accent), var(--indigo));
        color: #021018;
        border: 1px solid transparent;
        box-shadow: 0 0 0 3px var(--accent-dim);
    }

    /* ---------- Activity feed ---------- */
    .activity-feed { position: relative; }
    .activity-feed::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 6px;
        bottom: 6px;
        width: 1px;
        background: linear-gradient(180deg, var(--border), transparent);
    }
    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 11px 8px;
        border-radius: 10px;
        position: relative;
        transition: background 0.15s;
    }
    .activity-item:hover { background: var(--surface-2); }
    .activity-dot {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    /* ---------- Buttons / misc ---------- */
    .gen-btn {
        padding: 10px 22px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        background: var(--accent);
        color: #021a0f;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .gen-btn:hover { background: var(--accent-hover); transform: translateY(-1px); color: #021a0f; }

    .empty-state {
        text-align: center;
        padding: 44px 20px;
        color: var(--text-dim);
    }
    .empty-state i { font-size: 30px; margin-bottom: 12px; display: block; color: var(--text-dim); }
    .empty-state .empty-icon-badge {
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 14px;
        background: var(--surface-2);
        border: 1px solid var(--border);
    }
    .empty-state .empty-icon-badge i { margin: 0; font-size: 20px; color: var(--text-muted); }

    .toast {
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(120%);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        max-width: 360px;
        backdrop-filter: blur(8px);
    }
    .toast.show { transform: translateX(0); }
    .toast-success { background: rgba(6,95,70,0.92); color: #a7f3d0; border: 1px solid rgba(52,211,153,0.3); }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .view { animation: fadeUp 0.35s ease; }
    .stagger { animation: fadeUp 0.45s cubic-bezier(0.16,1,0.3,1) both; }

    /* focus visibility */
    a:focus-visible, button:focus-visible {
        outline: 2px solid var(--accent);
        outline-offset: 2px;
        border-radius: 8px;
    }

    @media (max-width: 640px) {
        .stat-value { font-size: 22px; }
        .stat-card { padding: 16px; }
        .hero-wrap { padding: 24px 18px; }
        .deck-item { flex-direction: column; align-items: flex-start; }
    }
</style>
@endsection

@section('content')
<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<div class="max-w-7xl mx-auto space-y-8 view">

    <!-- Welcome Header -->
    <div class="hero-wrap">
        <div class="hero-blob b1"></div>
        <div class="hero-blob b2"></div>
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 relative">
            <div>
                <span class="eyebrow">Welcome back</span>
                <h1 class="text-3xl lg:text-4xl font-black mt-3 tracking-tight">
                    Good <span id="timeGreeting">afternoon</span>, <span class="gradient-text">{{ explode(' ', Auth::user()->name)[0] }}</span>
                </h1>
                <p class="text-sm mt-2" style="color:var(--text-muted)" id="subtitleText">Ready to continue your learning journey?</p>
            </div>
            <div class="status-cluster">
                <div class="status-cell">
                    <p class="text-[10px] font-bold uppercase tracking-widest" style="color:var(--text-dim)">Today</p>
                    <p class="text-sm font-black mt-1" id="currentDate">--</p>
                    <p class="text-xs mt-0.5" style="color:var(--text-muted)" id="currentTime">--:--</p>
                </div>
                <div class="status-cell streak-cell">
                    <i class="fa-solid fa-fire flame-icon"></i>
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider" style="color:var(--text-dim)">Streak</div>
                        <div class="text-lg font-black" style="color:var(--amber)" id="streakCount">0 days</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card stagger" style="--glow:var(--accent); animation-delay:0.02s">
            <div class="stat-icon" style="background:rgba(45,212,220,0.1);color:var(--accent);">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <p class="stat-value" id="totalCards">0</p>
            <p class="stat-label">Flashcards</p>
        </div>

        <div class="stat-card stagger" style="--glow:var(--indigo); animation-delay:0.08s">
            <div class="stat-icon" style="background:rgba(129,140,248,0.1);color:var(--indigo);">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <p class="stat-value" id="totalDecks">0</p>
            <p class="stat-label">Decks</p>
        </div>

        <div class="stat-card stagger" style="--glow:var(--emerald); animation-delay:0.14s">
            <div class="stat-icon" style="background:rgba(52,211,153,0.1);color:var(--emerald);">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <p class="stat-value" id="masteredCards">0</p>
            <p class="stat-label">Mastered</p>
        </div>

        <div class="stat-card stagger" style="--glow:var(--amber); animation-delay:0.2s">
            <div class="stat-icon" style="background:rgba(251,146,60,0.1);color:var(--amber);">
                <i class="fa-solid fa-chart-simple"></i>
            </div>
            <p class="stat-value" id="masteryRate">0%</p>
            <p class="stat-label">Mastery Rate</p>
        </div>
    </div>

    <!-- Continue Learning removed -->

    <!-- Quick Actions -->
    <div>
        <h2 class="section-title mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('flashcards') }}" class="action-card">
                <i class="fa-solid fa-arrow-right"></i>
                <div class="action-icon" style="background:rgba(45,212,220,0.1);color:var(--accent);">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h3 class="font-bold text-sm">Flashcards</h3>
                <p class="text-xs mt-1" style="color:var(--text-dim)">Create & study</p>
            </a>

            <a href="{{ route('quizzes') }}" class="action-card">
                <i class="fa-solid fa-arrow-right"></i>
                <div class="action-icon" style="background:rgba(129,140,248,0.1);color:var(--indigo);">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <h3 class="font-bold text-sm">Quizzes</h3>
                <p class="text-xs mt-1" style="color:var(--text-dim)">Test yourself</p>
            </a>

            <a href="{{ route('ai-chat') }}" class="action-card">
                <i class="fa-solid fa-arrow-right"></i>
                <div class="action-icon" style="background:rgba(52,211,153,0.1);color:var(--emerald);">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <h3 class="font-bold text-sm">AI Chat</h3>
                <p class="text-xs mt-1" style="color:var(--text-dim)">Ask anything</p>
            </a>

            <a href="{{ route('summarizer') }}" class="action-card">
                <i class="fa-solid fa-arrow-right"></i>
                <div class="action-icon" style="background:rgba(251,146,60,0.1);color:var(--amber);">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <h3 class="font-bold text-sm">Summarizer</h3>
                <p class="text-xs mt-1" style="color:var(--text-dim)">Condense content</p>
            </a>
        </div>
    </div>

    <!-- Progress + Activity (Two Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Learning Progress -->
        <div>
            <h2 class="section-title mb-4">Learning Progress</h2>
            <div class="panel space-y-5">
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="progress-row-label"><i class="fa-solid fa-graduation-cap"></i> Cards Mastered</span>
                        <span class="font-bold text-sm" id="progMasteredLabel">0 / 0</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" id="progMasteredBar" style="width:0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="progress-row-label"><i class="fa-solid fa-layer-group"></i> Decks Studied</span>
                        <span class="font-bold text-sm" id="progDecksLabel">0 / 0</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" id="progDecksBar" style="width:0%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="progress-row-label"><i class="fa-solid fa-calendar-week"></i> Weekly Goal</span>
                        <span class="font-bold text-sm" id="progWeekLabel">0 / 7 days</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" id="progWeekBar" style="width:0%"></div>
                    </div>
                </div>

                <!-- Weekly Study Map -->
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest mb-3" style="color:var(--text-dim)">This Week</p>
                    <div class="flex items-center justify-between gap-2" id="weekMap"></div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div>
            <h2 class="section-title mb-4">Recent Activity</h2>
            <div class="panel" id="activitySection" style="max-height: 360px; overflow-y: auto;"></div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
/* ===== Data Access ===== */
function getDecks() {
    try { return JSON.parse(localStorage.getItem('fc_decks') || '[]'); }
    catch { return []; }
}

function getStreakData() {
    try {
        return JSON.parse(localStorage.getItem('fc_streak') || '{"current":0,"lastStudyDate":null,"studyDays":[]}');
    } catch { return { current: 0, lastStudyDate: null, studyDays: [] }; }
}

function saveStreakData(data) {
    try { localStorage.setItem('fc_streak', JSON.stringify(data)); } catch {}
}

function getActivities() {
    try { return JSON.parse(localStorage.getItem('fc_activity') || '[]'); }
    catch { return []; }
}

function saveActivities(acts) {
    try { localStorage.setItem('fc_activity', JSON.stringify(acts.slice(0, 50))); } catch {}
}

/* ===== Activity Logging ===== */
function logActivity(type, title, description, icon) {
    const activities = getActivities();
    const recent = activities[0];
    if (recent && recent.title === title && Date.now() - recent.timestamp < 10000) return;

    activities.unshift({
        type, title, description,
        icon: icon || 'check',
        timestamp: Date.now()
    });
    saveActivities(activities);
}

/* ===== Streak Calculation ===== */
function computeStreak() {
    const streakData = getStreakData();
    const decks = getDecks();
    const today = new Date().toDateString();
    const yesterday = new Date(Date.now() - 86400000).toDateString();
    const todayStart = new Date();
    todayStart.setHours(0, 0, 0, 0);

    const studiedToday = decks.some(d => d.updatedAt && d.updatedAt >= todayStart.getTime());

    if (studiedToday && streakData.lastStudyDate !== today) {
        if (streakData.lastStudyDate === yesterday) {
            streakData.current = (streakData.current || 0) + 1;
        } else {
            streakData.current = 1;
        }
        streakData.lastStudyDate = today;
        if (!streakData.studyDays) streakData.studyDays = [];
        if (!streakData.studyDays.includes(today)) {
            streakData.studyDays.push(today);
        }
        saveStreakData(streakData);
        logActivity('study', 'Study session completed', 'You studied flashcards today', 'graduation-cap');
    } else if (!studiedToday && streakData.lastStudyDate !== today && streakData.lastStudyDate !== yesterday) {
        if (streakData.current > 0) {
            streakData.current = 0;
            saveStreakData(streakData);
        }
    }

    return streakData;
}

/* ===== Derived Activities from Decks ===== */
function deriveActivitiesFromDecks() {
    const decks = getDecks();
    const explicit = getActivities();
    const derived = [];

    decks.forEach(deck => {
        if (deck.createdAt) {
            derived.push({
                type: 'create',
                title: 'Created "' + deck.title + '"',
                description: deck.cards.length + ' flashcards generated',
                icon: 'plus',
                timestamp: deck.createdAt
            });
        }

        const studiedCards = deck.cards ? deck.cards.filter(c => c.correctCount > 0 || c.incorrectCount > 0).length : 0;
        if (studiedCards > 0 && deck.updatedAt && deck.updatedAt !== deck.createdAt) {
            derived.push({
                type: 'study',
                title: 'Studied "' + deck.title + '"',
                description: studiedCards + ' cards reviewed',
                icon: 'graduation-cap',
                timestamp: deck.updatedAt
            });
        }
    });

    const all = [...explicit, ...derived];

    const seen = new Set();
    return all.filter(a => {
        const key = a.title + '|' + a.timestamp;
        if (seen.has(key)) return false;
        seen.add(key);
        return true;
    }).sort((a, b) => b.timestamp - a.timestamp).slice(0, 20);
}

/* ===== Stats Computation ===== */
function computeStats() {
    const decks = getDecks();
    let totalCards = 0;
    let masteredCards = 0;
    let decksStudied = 0;

    decks.forEach(deck => {
        if (deck.cards) {
            totalCards += deck.cards.length;
            masteredCards += deck.cards.filter(c => c.mastered).length;
            if (deck.cards.some(c => c.correctCount > 0 || c.incorrectCount > 0)) {
                decksStudied++;
            }
        }
    });

    const masteryRate = totalCards > 0 ? Math.round((masteredCards / totalCards) * 100) : 0;

    return {
        totalCards,
        totalDecks: decks.length,
        masteredCards,
        masteryRate,
        decksStudied
    };
}

/* ===== UI Rendering ===== */
function escHtml(val) {
    const d = document.createElement('div');
    d.textContent = String(val);
    return d.innerHTML;
}

function renderStats(stats) {
    animateNumber('totalCards', stats.totalCards);
    animateNumber('totalDecks', stats.totalDecks);
    animateNumber('masteredCards', stats.masteredCards);
    document.getElementById('masteryRate').textContent = stats.masteryRate + '%';
}

function animateNumber(elId, target) {
    const el = document.getElementById(elId);
    const start = parseInt(el.textContent) || 0;
    if (start === target) { el.textContent = target; return; }
    const duration = 600;
    const startTime = performance.now();

    function tick(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(start + (target - start) * eased);
        if (progress < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

/* Small radial progress ring used on deck cards */
function ringSvg(pct, uid, size, stroke) {
    size = size || 56;
    stroke = stroke || 5;
    const r = (size - stroke) / 2;
    const c = 2 * Math.PI * r;
    const offset = c - (Math.max(0, Math.min(100, pct)) / 100) * c;
    const gradId = 'ringGrad-' + uid;
    return `
    <div class="ring-wrap">
        <svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="transform:rotate(-90deg);">
            <circle cx="${size/2}" cy="${size/2}" r="${r}" fill="none" stroke="rgba(226,232,240,0.08)" stroke-width="${stroke}"/>
            <circle cx="${size/2}" cy="${size/2}" r="${r}" fill="none" stroke="url(#${gradId})" stroke-width="${stroke}" stroke-linecap="round"
                stroke-dasharray="${c}" stroke-dashoffset="${offset}" style="transition:stroke-dashoffset 0.8s cubic-bezier(0.16,1,0.3,1);"/>
            <defs>
                <linearGradient id="${gradId}" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="var(--ring-1)"/>
                    <stop offset="100%" stop-color="var(--ring-2)"/>
                </linearGradient>
            </defs>
        </svg>
        <span class="ring-pct">${pct}%</span>
    </div>`;
}

function renderProgress(stats, streakData) {
    const total = stats.totalCards;
    const mastered = stats.masteredCards;
    const studied = stats.decksStudied;
    const totalDecks = stats.totalDecks;

    document.getElementById('progMasteredLabel').textContent = mastered + ' / ' + total;
    document.getElementById('progMasteredBar').style.width = (total > 0 ? Math.round((mastered / total) * 100) : 0) + '%';

    document.getElementById('progDecksLabel').textContent = studied + ' / ' + totalDecks;
    document.getElementById('progDecksBar').style.width = (totalDecks > 0 ? Math.round((studied / totalDecks) * 100) : 0) + '%';

    const studyDays = streakData.studyDays || [];
    const weekDays = getWeekDays();
    const activeDays = weekDays.filter(d => studyDays.includes(d.dateStr)).length;
    document.getElementById('progWeekLabel').textContent = activeDays + ' / 7 days';
    document.getElementById('progWeekBar').style.width = Math.round((activeDays / 7) * 100) + '%';
}

function renderWeekMap(streakData) {
    const container = document.getElementById('weekMap');
    const studyDays = streakData.studyDays || [];
    const weekDays = getWeekDays();
    const todayStr = new Date().toDateString();
    const dayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    container.innerHTML = weekDays.map((d, i) => {
        const isActive = studyDays.includes(d.dateStr);
        const isToday = d.dateStr === todayStr;
        let cls = isToday ? 'today' : (isActive ? 'active' : 'inactive');
        return `<div class="week-dot ${cls}" title="${d.dateStr}">${dayLabels[i]}</div>`;
    }).join('');
}

function getWeekDays() {
    const now = new Date();
    const dayOfWeek = now.getDay();
    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    const days = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(now);
        d.setDate(now.getDate() + mondayOffset + i);
        days.push({ dateStr: d.toDateString(), date: d });
    }
    return days;
}

function renderActivity() {
    const activities = deriveActivitiesFromDecks();
    const container = document.getElementById('activitySection');

    if (activities.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon-badge"><i class="fa-solid fa-history"></i></div>
                <p>No activity yet</p>
                <p style="margin-top:4px">Start studying to see your activity here.</p>
            </div>`;
        return;
    }

    container.innerHTML = `<div class="activity-feed">` + activities.map(a => {
        const iconBg = a.type === 'create'
            ? 'background:rgba(45,212,220,0.12);color:var(--accent);'
            : 'background:rgba(52,211,153,0.12);color:var(--emerald);';
        const timeAgo = formatTimeAgo(a.timestamp);

        return `
        <div class="activity-item">
            <div class="activity-dot" style="${iconBg}">
                <i class="fa-solid fa-${a.icon}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate">${escHtml(a.title)}</p>
                <p class="text-xs mt-0.5" style="color:var(--text-muted)">${escHtml(a.description)}</p>
            </div>
            <span class="text-[10px] font-semibold whitespace-nowrap" style="color:var(--text-dim)">${timeAgo}</span>
        </div>`;
    }).join('') + `</div>`;
}

function formatTimeAgo(timestamp) {
    const now = Date.now();
    const diff = now - timestamp;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return minutes + 'm ago';
    if (hours < 24) return hours + 'h ago';
    if (days < 7) return days + 'd ago';
    return new Date(timestamp).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

/* ===== Date & Time ===== */
function updateDateTime() {
    const now = new Date();
    const hour = now.getHours();
    const greeting = hour < 12 ? 'morning' : hour < 18 ? 'afternoon' : 'evening';
    document.getElementById('timeGreeting').textContent = greeting;

    document.getElementById('currentDate').textContent =
        now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    document.getElementById('currentTime').textContent =
        now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
}

/* ===== Toast ===== */
function showToast(message, type) {
    type = type || 'success';
    const container = document.getElementById('toasts');
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.innerHTML = '<i class="fa-solid fa-' + (type === 'success' ? 'check-circle' : 'info-circle') + '"></i><span>' + message + '</span>';
    container.appendChild(toast);
    requestAnimationFrame(function() { toast.classList.add('show'); });
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3500);
}

/* ===== Subtitle Message ===== */
function updateSubtitle(stats, streakData) {
    const el = document.getElementById('subtitleText');
    const streak = streakData.current || 0;

    if (stats.totalDecks === 0) {
        el.textContent = 'Ready to start your learning journey? Create your first deck!';
    } else if (streak >= 7) {
        el.textContent = 'Amazing ' + streak + '-day streak! Keep the momentum going!';
    } else if (streak >= 3) {
        el.textContent = 'Great ' + streak + '-day streak! You\'re building a habit.';
    } else if (stats.masteryRate >= 80) {
        el.textContent = 'Incredible! ' + stats.masteryRate + '% mastery rate. Keep reviewing!';
    } else if (stats.totalDecks > 0 && stats.decksStudied === 0) {
        el.textContent = 'You have ' + stats.totalDecks + ' deck' + (stats.totalDecks > 1 ? 's' : '') + ' ready to study!';
    } else {
        el.textContent = 'Ready to continue your learning journey?';
    }
}

/* ===== Initialize ===== */
function initDashboard() {
    updateDateTime();
    setInterval(updateDateTime, 30000);

    const stats = computeStats();
    const streakData = computeStreak();

    renderStats(stats);
    renderProgress(stats, streakData);
    renderWeekMap(streakData);
    renderActivity();
    updateSubtitle(stats, streakData);

    document.getElementById('streakCount').textContent = (streakData.current || 0) + ' days';

    // Welcome toast on first visit
    const hasVisited = localStorage.getItem('fc_dashboard_visited');
    if (!hasVisited) {
        localStorage.setItem('fc_dashboard_visited', '1');
        showToast('Welcome to SHEELEARN! Start by creating a flashcard deck.', 'success');
    }
}

document.addEventListener('DOMContentLoaded', initDashboard);
</script>
@endsection