@extends('layouts.dashboard-layout')

@section('title', 'Study Planner — SHEELEARN AI')

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
    .dashboard-planner { width: 100%; }

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
    select.field { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%234e5a6e' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px; }
    select.field option { background: #111318; color: var(--text); }

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

    .progress-track { height: 6px; border-radius: 99px; background: var(--surface-3); overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; background: var(--accent); transition: width 0.4s ease; }

    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; text-align: center; }
    .stat-value { font-size: 28px; font-weight: 800; line-height: 1; }
    .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; margin-top: 6px; color: var(--text-dim); }

    .sidebar-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 20px; }

    .toast { padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 8px 24px rgba(0,0,0,0.4); max-width: 360px; }
    .toast.show { transform: translateX(0); }
    .toast-success { background: #0f172a; color: #93c5fd; border: 1px solid rgba(34,211,238,0.3); }
    .toast-error { background: #7f1d1d; color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
    .toast-info { background: #1e3a5f; color: #93c5fd; border: 1px solid rgba(59,130,246,0.3); }

    .week-bar { flex: 1; border-radius: 4px 4px 0 0; transition: height 0.4s ease; min-height: 4px; }

    .confirm-overlay { position: absolute; inset: 0; background: rgba(10,12,16,0.94); border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; z-index: 5; }

    /* Task card */
    .task-card {
        background: var(--surface); border: 1px solid var(--border); border-radius: 14px;
        padding: 16px; transition: all 0.25s; cursor: pointer; position: relative;
    }
    .task-card:hover { border-color: var(--border-hover); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }
    .task-card.completed { opacity: 0.6; }
    .task-card.completed .task-title { text-decoration: line-through; }

    .priority-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .priority-dot.high { background: var(--danger); }
    .priority-dot.medium { background: var(--warning); }
    .priority-dot.low { background: var(--accent); }

    /* Timer */
    .timer-display { font-size: 48px; font-weight: 800; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; line-height: 1; }
    .timer-ring { position: relative; width: 180px; height: 180px; margin: 0 auto; }
    .timer-ring svg { transform: rotate(-90deg); }

    .view { animation: fadeUp 0.3s ease; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 1023px) { .main-grid { grid-template-columns: 1fr !important; } }
</style>
@endsection

@section('content')
<div class="dashboard-planner">

<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<!-- Header -->
<header class="pt-6 pb-5 px-1">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-800 tracking-tight">Study Planner</h1>
            <p class="text-sm mt-1" style="color:var(--text-muted)">Organize tasks, track goals, and manage your study time</p>
        </div>
        <nav class="flex gap-2 bg-[var(--surface)] p-1.5 rounded-xl w-fit">
            <button class="tab-btn active" data-tab="overview"><i class="fa-solid fa-calendar-day text-xs"></i> Overview</button>
            <button class="tab-btn" data-tab="tasks"><i class="fa-solid fa-list-check text-xs"></i> Tasks</button>
            <button class="tab-btn" data-tab="timer"><i class="fa-solid fa-stopwatch text-xs"></i> Timer</button>
        </nav>
    </div>
</header>

<!-- Two-Column Grid -->
<div class="main-grid grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-6 pb-8">

    <!-- ====== LEFT: Main Content ====== -->
    <div class="min-w-0">

        <!-- ============ OVERVIEW TAB ============ -->
        <section id="overviewView" class="view space-y-5">

            <!-- Stats Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="stat-card"><p id="stToday" class="stat-value" style="color:var(--accent)">0</p><p class="stat-label">Today's Tasks</p></div>
                <div class="stat-card"><p id="stDeadlines" class="stat-value" style="color:var(--warning)">0</p><p class="stat-label">Due This Week</p></div>
                <div class="stat-card"><p id="stHours" class="stat-value" style="color:#818cf8">0h</p><p class="stat-label">Study Hours</p></div>
                <div class="stat-card"><p id="stScore" class="stat-value" style="color:#f472b6">0%</p><p class="stat-label">Completion</p></div>
            </div>

            <!-- Today's Schedule -->
            <div class="form-panel">
                <div class="flex items-center justify-between mb-4">
                    <p class="section-label" style="margin-bottom:0">Today's Schedule</p>
                    <button onclick="switchTab('tasks')" class="action-btn sm"><i class="fa-solid fa-plus"></i> Add</button>
                </div>
                <div id="todaySchedule" class="space-y-3"></div>
            </div>

            <!-- Weekly Overview -->
            <div class="form-panel">
                <p class="section-label">This Week</p>
                <div id="weekGrid" class="grid grid-cols-7 gap-2"></div>
            </div>

            <!-- Goals -->
            <div class="form-panel">
                <div class="flex items-center justify-between mb-4">
                    <p class="section-label" style="margin-bottom:0">Study Goals</p>
                    <button onclick="showGoalForm()" class="action-btn sm"><i class="fa-solid fa-plus"></i> Add Goal</button>
                </div>
                <div id="goalsList" class="space-y-3"></div>
                <!-- Goal form inline -->
                <div id="goalFormWrap" class="hidden mt-4 pt-4" style="border-top:1px solid var(--border)">
                    <div class="flex gap-3">
                        <input id="goalInput" type="text" class="field" placeholder="Goal title..." style="flex:1">
                        <input id="goalTarget" type="number" class="field" placeholder="Target min" style="width:100px" min="1">
                        <button onclick="addGoal()" class="action-btn primary">Add</button>
                        <button onclick="hideGoalForm()" class="action-btn">Cancel</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ TASKS TAB ============ -->
        <section id="tasksView" class="view hidden space-y-5">

            <!-- Add Task Form -->
            <div class="form-panel">
                <p class="section-label">Add New Task</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div><p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Title</p><input id="taskTitle" type="text" class="field" placeholder="Study Chapter 5"></div>
                    <div><p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Subject</p><input id="taskSubject" type="text" class="field" placeholder="Biology"></div>
                    <div><p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Due Date</p><input id="taskDue" type="date" class="field"></div>
                    <div><p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Priority</p>
                        <select id="taskPriority" class="field"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select>
                    </div>
                    <div class="sm:col-span-2"><p class="text-xs font-600 mb-2" style="color:var(--text-muted)">Notes</p><input id="taskNotes" type="text" class="field" placeholder="Optional notes"></div>
                </div>
                <div class="mt-4"><button onclick="addTask()" class="gen-btn" style="padding:10px 28px;font-size:14px"><i class="fa-solid fa-plus"></i> Add Task</button></div>
            </div>

            <!-- Filter -->
            <div class="flex gap-2 flex-wrap">
                <button class="pill active" data-tfilter="all" onclick="setTaskFilter(this)">All</button>
                <button class="pill" data-tfilter="pending" onclick="setTaskFilter(this)">Pending</button>
                <button class="pill" data-tfilter="completed" onclick="setTaskFilter(this)">Completed</button>
                <button class="pill" data-tfilter="high" onclick="setTaskFilter(this)">High Priority</button>
                <button class="pill" data-tfilter="overdue" onclick="setTaskFilter(this)">Overdue</button>
            </div>

            <!-- Task List -->
            <div id="taskList" class="space-y-3"></div>
            <div id="emptyTasks" class="hidden text-center py-16">
                <i class="fa-solid fa-list-check text-5xl mb-4" style="color:var(--text-dim)"></i>
                <p class="text-lg font-600" style="color:var(--text-muted)">No tasks yet</p>
                <p class="text-sm mt-1" style="color:var(--text-dim)">Create your first study task above.</p>
            </div>
        </section>

        <!-- ============ TIMER TAB ============ -->
        <section id="timerView" class="view hidden space-y-5">
            <div class="form-panel text-center" style="padding:40px">
                <p class="section-label">Focus Timer</p>
                <div class="timer-ring mt-6">
                    <svg width="180" height="180"><circle cx="90" cy="90" r="80" fill="none" stroke="var(--surface-3)" stroke-width="6"/><circle id="timerCircle" cx="90" cy="90" r="80" fill="none" stroke="var(--accent)" stroke-width="6" stroke-linecap="round" stroke-dasharray="503" stroke-dashoffset="0"/></svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <span id="timerDisplay" class="timer-display" style="color:var(--text)">25:00</span>
                        <span id="timerLabel" class="text-xs font-600 mt-2" style="color:var(--text-dim)">Focus Session</span>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button id="timerStartBtn" onclick="toggleTimer()" class="gen-btn" style="padding:12px 32px"><i class="fa-solid fa-play"></i> Start</button>
                    <button onclick="resetTimer()" class="action-btn"><i class="fa-solid fa-rotate-right"></i> Reset</button>
                </div>

                <div class="flex gap-2 justify-center mt-6">
                    <button class="pill active" data-dur="25" onclick="setTimerDur(this)">25 min</button>
                    <button class="pill" data-dur="15" onclick="setTimerDur(this)">15 min</button>
                    <button class="pill" data-dur="45" onclick="setTimerDur(this)">45 min</button>
                    <button class="pill" data-dur="60" onclick="setTimerDur(this)">60 min</button>
                </div>

                <div class="mt-8 pt-6" style="border-top:1px solid var(--border)">
                    <p class="text-xs font-600 mb-3" style="color:var(--text-muted)">Working on</p>
                    <select id="timerTask" class="field" style="max-width:300px;margin:0 auto"><option value="">Select a task...</option></select>
                </div>
            </div>

            <!-- Session History -->
            <div class="form-panel">
                <p class="section-label">Today's Sessions</p>
                <div id="sessionList" class="space-y-2"></div>
                <p id="noSessions" class="text-xs text-center py-4" style="color:var(--text-dim)">No sessions recorded today</p>
            </div>
        </section>
    </div>

    <!-- ====== RIGHT: Sidebar ====== -->
    <aside class="space-y-5">
        <div class="grid grid-cols-2 gap-3">
            <div class="stat-card"><p id="sbTasks" class="stat-value" style="color:var(--accent)">0</p><p class="stat-label">Total Tasks</p></div>
            <div class="stat-card"><p id="sbDone" class="stat-value" style="color:var(--warning)">0</p><p class="stat-label">Completed</p></div>
            <div class="stat-card"><p id="sbSessions" class="stat-value" style="color:#818cf8">0</p><p class="stat-label">Sessions</p></div>
            <div class="stat-card"><p id="sbStreak" class="stat-value" style="color:#f472b6">0</p><p class="stat-label">Day Streak</p></div>
        </div>

        <!-- Weekly Study Chart -->
        <div class="sidebar-panel">
            <p class="section-label">Weekly Study</p>
            <div id="weeklyBars" class="flex items-end gap-2" style="height:80px">
                <div class="week-bar" style="height:10%;background:rgba(34,211,238,0.12)"></div>
                <div class="week-bar" style="height:20%;background:rgba(34,211,238,0.16)"></div>
                <div class="week-bar" style="height:15%;background:rgba(34,211,238,0.14)"></div>
                <div class="week-bar" style="height:40%;background:rgba(34,211,238,0.2)"></div>
                <div class="week-bar" style="height:55%;background:rgba(34,211,238,0.28)"></div>
                <div class="week-bar" style="height:35%;background:rgba(34,211,238,0.22)"></div>
                <div class="week-bar" style="height:70%;background:rgba(34,211,238,0.34)"></div>
            </div>
            <div class="flex justify-between mt-2 text-[10px]" style="color:var(--text-dim)"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="sidebar-panel">
            <p class="section-label">Upcoming Deadlines</p>
            <div id="deadlinesList" class="space-y-2 max-h-[200px] overflow-y-auto pr-1"><p class="text-xs text-center py-4" style="color:var(--text-dim)">No upcoming deadlines</p></div>
        </div>

        <!-- Tips -->
        <div class="sidebar-panel">
            <p class="section-label">Study Tips</p>
            <div class="space-y-3">
                <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:var(--accent-dim)"><i class="fa-solid fa-clock text-xs" style="color:var(--accent)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Pomodoro Technique</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Study 25 min, break 5 min. After 4 rounds, take a longer break.</p></div></div>
                <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:rgba(34,211,238,0.12)"><i class="fa-solid fa-layer-group text-xs" style="color:var(--accent)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Active Recall</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Test yourself instead of re-reading. Use flashcards for best results.</p></div></div>
                <div class="flex gap-3 items-start"><div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:rgba(34,211,238,0.1)"><i class="fa-solid fa-bullseye text-xs" style="color:var(--accent)"></i></div><div><p class="text-xs font-600" style="color:var(--text)">Prioritize</p><p class="text-[11px] leading-relaxed" style="color:var(--text-dim)">Tackle high-priority tasks first when your focus is sharpest.</p></div></div>
            </div>
        </div>
    </aside>
</div>
</div>
@endsection

@section('scripts')
<script>
const state = {
    currentTab: 'overview',
    taskFilter: 'all',
    timerDuration: 25,
    timerRunning: false,
    timerSeconds: 25 * 60,
    timerInterval: null,
    timerTaskId: null,
};

// ── Storage ──
function getTasks() { try { return JSON.parse(localStorage.getItem('sp_tasks') || '[]'); } catch { return []; } }
function saveTasks(t) { try { localStorage.setItem('sp_tasks', JSON.stringify(t)); } catch(e){} }
function getGoals() { try { return JSON.parse(localStorage.getItem('sp_goals') || '[]'); } catch { return []; } }
function saveGoals(g) { try { localStorage.setItem('sp_goals', JSON.stringify(g)); } catch(e){} }
function getSessions() { try { return JSON.parse(localStorage.getItem('sp_sessions') || '[]'); } catch { return []; } }
function saveSessions(s) { try { localStorage.setItem('sp_sessions', JSON.stringify(s)); } catch(e){} }
function getHistory() { try { return JSON.parse(localStorage.getItem('sp_history') || '[]'); } catch { return []; } }
function saveHistory(h) { try { localStorage.setItem('sp_history', JSON.stringify(h)); } catch(e){} }
function uid() { return 't' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function today() { return new Date().toISOString().slice(0, 10); }
function formatDate(d) { if (!d) return '—'; return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' }); }
function daysUntil(d) { if (!d) return null; const diff = new Date(d + 'T00:00:00') - new Date(today() + 'T00:00:00'); return Math.ceil(diff / 86400000); }

// ── Toast ──
function showToast(msg, type='info') {
    const c = document.getElementById('toasts'), t = document.createElement('div');
    t.className = `toast toast-${type}`;
    const icons = {success:'check-circle',error:'exclamation-circle',info:'info-circle'};
    t.innerHTML = `<i class="fa-solid fa-${icons[type]||icons.info}"></i><span>${msg}</span>`;
    c.appendChild(t); requestAnimationFrame(() => t.classList.add('show'));
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
}

// ── Tabs ──
function switchTab(tab) {
    state.currentTab = tab;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    document.getElementById('overviewView').classList.toggle('hidden', tab !== 'overview');
    document.getElementById('tasksView').classList.toggle('hidden', tab !== 'tasks');
    document.getElementById('timerView').classList.toggle('hidden', tab !== 'timer');
    if (tab === 'overview') renderOverview();
    if (tab === 'tasks') renderTasks();
    if (tab === 'timer') renderTimer();
    updateSidebar();
}
document.querySelectorAll('.tab-btn').forEach(btn => { btn.addEventListener('click', () => switchTab(btn.dataset.tab)); });

// ── Tasks ──
function addTask() {
    const title = document.getElementById('taskTitle').value.trim();
    if (!title) { showToast('Enter a task title', 'error'); return; }
    const tasks = getTasks();
    tasks.push({
        id: uid(), title,
        subject: document.getElementById('taskSubject').value.trim() || '',
        dueDate: document.getElementById('taskDue').value || null,
        priority: document.getElementById('taskPriority').value || 'medium',
        notes: document.getElementById('taskNotes').value.trim() || '',
        completed: false, completedAt: null, createdAt: Date.now(),
    });
    saveTasks(tasks);
    document.getElementById('taskTitle').value = '';
    document.getElementById('taskSubject').value = '';
    document.getElementById('taskDue').value = '';
    document.getElementById('taskPriority').value = 'medium';
    document.getElementById('taskNotes').value = '';
    logHistory(`Added task "${title}"`);
    showToast(`Task "${title}" created`, 'success');
    renderTasks(); updateSidebar();
}

function toggleTask(id) {
    const tasks = getTasks(); const task = tasks.find(t => t.id === id); if (!task) return;
    task.completed = !task.completed;
    task.completedAt = task.completed ? Date.now() : null;
    saveTasks(tasks);
    logHistory(`${task.completed ? 'Completed' : 'Reopened'} "${task.title}"`);
    renderTasks(); renderOverview(); updateSidebar();
}

function deleteTask(id) {
    let tasks = getTasks(); const task = tasks.find(t => t.id === id);
    tasks = tasks.filter(t => t.id !== id); saveTasks(tasks);
    logHistory(`Deleted "${task?.title || 'task'}"`);
    renderTasks(); renderOverview(); updateSidebar();
    showToast('Task deleted', 'info');
}

function setTaskFilter(el) {
    document.querySelectorAll('[data-tfilter]').forEach(b => b.classList.remove('active'));
    el.classList.add('active'); state.taskFilter = el.dataset.tfilter; renderTasks();
}

function renderTasks() {
    const tasks = getTasks();
    const filter = state.taskFilter;
    let filtered = tasks;
    if (filter === 'pending') filtered = tasks.filter(t => !t.completed);
    else if (filter === 'completed') filtered = tasks.filter(t => t.completed);
    else if (filter === 'high') filtered = tasks.filter(t => t.priority === 'high' && !t.completed);
    else if (filter === 'overdue') filtered = tasks.filter(t => !t.completed && t.dueDate && daysUntil(t.dueDate) < 0);

    filtered.sort((a, b) => { if (a.completed !== b.completed) return a.completed ? 1 : -1; const pOrder = { high: 0, medium: 1, low: 2 }; if (pOrder[a.priority] !== pOrder[b.priority]) return pOrder[a.priority] - pOrder[b.priority]; return b.createdAt - a.createdAt; });

    const list = document.getElementById('taskList');
    const empty = document.getElementById('emptyTasks');

    if (!filtered.length) { list.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    list.innerHTML = filtered.map(t => {
        const days = daysUntil(t.dueDate);
        const overdue = !t.completed && days !== null && days < 0;
        const dueLabel = !t.dueDate ? '' : days === 0 ? 'Today' : days === 1 ? 'Tomorrow' : days > 0 ? `${days} days left` : `${Math.abs(days)} days overdue`;
        const dueColor = overdue ? 'var(--danger)' : days !== null && days <= 2 ? 'var(--warning)' : 'var(--text-dim)';
        return `<div class="task-card ${t.completed ? 'completed' : ''}" data-id="${t.id}">
            <div class="flex items-start gap-3">
                <button onclick="toggleTask('${t.id}')" class="mt-0.5 w-5 h-5 rounded-md border flex items-center justify-center shrink-0 transition" style="border-color:${t.completed?'var(--accent)':'var(--border)'};background:${t.completed?'var(--accent)':'transparent'};color:${t.completed?'#021a0f':'transparent'}">
                    ${t.completed ? '<i class="fa-solid fa-check text-[10px]"></i>' : ''}
                </button>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <div class="priority-dot ${t.priority}"></div>
                        <p class="task-title text-sm font-600 truncate">${esc(t.title)}</p>
                    </div>
                    <div class="flex items-center gap-3 mt-1.5 text-xs" style="color:var(--text-dim)">
                        ${t.subject ? `<span>${esc(t.subject)}</span>` : ''}
                        ${t.dueDate ? `<span style="color:${dueColor}">${formatDate(t.dueDate)} · ${dueLabel}</span>` : ''}
                        <span class="uppercase" style="color:${t.priority==='high'?'var(--danger)':t.priority==='medium'?'var(--warning)':'var(--accent)'}">${t.priority}</span>
                    </div>
                    ${t.notes ? `<p class="text-xs mt-1" style="color:var(--text-dim)">${esc(t.notes)}</p>` : ''}
                </div>
                <button onclick="deleteTask('${t.id}')" class="action-btn sm danger" style="border:none;background:transparent"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>`;
    }).join('');
}

// ── Goals ──
function showGoalForm() { document.getElementById('goalFormWrap').classList.remove('hidden'); document.getElementById('goalInput').focus(); }
function hideGoalForm() { document.getElementById('goalFormWrap').classList.add('hidden'); document.getElementById('goalInput').value = ''; document.getElementById('goalTarget').value = ''; }
function addGoal() {
    const title = document.getElementById('goalInput').value.trim();
    const target = parseInt(document.getElementById('goalTarget').value) || 60;
    if (!title) { showToast('Enter a goal title', 'error'); return; }
    const goals = getGoals();
    goals.push({ id: uid(), title, target, current: 0, createdAt: Date.now() });
    saveGoals(goals); hideGoalForm(); renderOverview(); logHistory(`Added goal "${title}"`);
    showToast(`Goal "${title}" created`, 'success');
}
function updateGoalProgress(id, delta) {
    const goals = getGoals(); const g = goals.find(g => g.id === id); if (!g) return;
    g.current = Math.max(0, Math.min(g.target, g.current + delta));
    saveGoals(goals); renderOverview(); updateSidebar();
}
function deleteGoal(id) {
    saveGoals(getGoals().filter(g => g.id !== id)); renderOverview(); updateSidebar();
    showToast('Goal deleted', 'info');
}

// ── Timer ──
function setTimerDur(el) {
    if (state.timerRunning) return;
    document.querySelectorAll('[data-dur]').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    state.timerDuration = parseInt(el.dataset.dur);
    state.timerSeconds = state.timerDuration * 60;
    updateTimerDisplay();
}

function toggleTimer() {
    if (state.timerRunning) { pauseTimer(); } else { startTimer(); }
}

function startTimer() {
    state.timerRunning = true;
    document.getElementById('timerStartBtn').innerHTML = '<i class="fa-solid fa-pause"></i> Pause';
    state.timerInterval = setInterval(() => {
        state.timerSeconds--;
        updateTimerDisplay();
        if (state.timerSeconds <= 0) { completeSession(); }
    }, 1000);
}

function pauseTimer() {
    state.timerRunning = false;
    clearInterval(state.timerInterval);
    document.getElementById('timerStartBtn').innerHTML = '<i class="fa-solid fa-play"></i> Resume';
}

function resetTimer() {
    state.timerRunning = false;
    clearInterval(state.timerInterval);
    state.timerSeconds = state.timerDuration * 60;
    document.getElementById('timerStartBtn').innerHTML = '<i class="fa-solid fa-play"></i> Start';
    updateTimerDisplay();
}

function completeSession() {
    state.timerRunning = false;
    clearInterval(state.timerInterval);
    const sessions = getSessions();
    const taskId = document.getElementById('timerTask').value;
    const duration = state.timerDuration;
    sessions.push({ id: uid(), date: today(), duration, taskId: taskId || null, completedAt: Date.now() });
    saveSessions(sessions);
    logHistory(`Completed ${duration}-min focus session`);

    // Update goal if linked
    if (taskId) {
        const goals = getGoals();
        const task = getTasks().find(t => t.id === taskId);
        if (task) {
            const goal = goals.find(g => g.title.toLowerCase().includes(task.subject?.toLowerCase() || '___'));
            if (goal) { goal.current = Math.min(goal.target, goal.current + duration); saveGoals(goals); }
        }
    }

    resetTimer();
    renderTimer(); renderOverview(); updateSidebar();
    showToast(`${duration}-minute session completed!`, 'success');
}

function updateTimerDisplay() {
    const m = Math.floor(state.timerSeconds / 60);
    const s = state.timerSeconds % 60;
    document.getElementById('timerDisplay').textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

    const total = state.timerDuration * 60;
    const pct = total > 0 ? ((total - state.timerSeconds) / total) : 0;
    const offset = 503 - (503 * pct);
    document.getElementById('timerCircle').style.strokeDashoffset = offset;
    document.getElementById('timerCircle').style.stroke = pct > 0.9 ? 'var(--warning)' : 'var(--accent)';
}

function renderTimer() {
    // Populate task dropdown
    const tasks = getTasks().filter(t => !t.completed);
    const sel = document.getElementById('timerTask');
    const current = sel.value;
    sel.innerHTML = '<option value="">Select a task...</option>' + tasks.map(t => `<option value="${t.id}">${esc(t.title)}${t.subject ? ' — ' + esc(t.subject) : ''}</option>`).join('');
    if (current) sel.value = current;

    // Today's sessions
    const sessions = getSessions().filter(s => s.date === today());
    const tasksList = getTasks();
    const sessionEl = document.getElementById('sessionList');
    const noSessions = document.getElementById('noSessions');

    if (!sessions.length) { sessionEl.innerHTML = ''; noSessions.classList.remove('hidden'); }
    else {
        noSessions.classList.add('hidden');
        sessionEl.innerHTML = sessions.map(s => {
            const task = s.taskId ? tasksList.find(t => t.id === s.taskId) : null;
            const time = new Date(s.completedAt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            return `<div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--surface-2)">
                <i class="fa-solid fa-check-circle text-sm" style="color:var(--accent)"></i>
                <div class="flex-1"><p class="text-sm font-600">${s.duration} min session</p><p class="text-xs" style="color:var(--text-dim)">${task ? esc(task.title) : 'No task linked'} · ${time}</p></div>
            </div>`;
        }).join('');
    }
    updateTimerDisplay();
}

// ── Overview ──
function renderOverview() {
    const tasks = getTasks();
    const todayStr = today();
    const todayTasks = tasks.filter(t => t.dueDate === todayStr);
    const weekEnd = new Date(); weekEnd.setDate(weekEnd.getDate() + (7 - weekEnd.getDay()));
    const weekEndStr = weekEnd.toISOString().slice(0, 10);
    const weekTasks = tasks.filter(t => t.dueDate && t.dueDate <= weekEndStr && !t.completed);
    const sessions = getSessions();
    const todaySessions = sessions.filter(s => s.date === todayStr);
    const totalMinutes = todaySessions.reduce((s, se) => s + se.duration, 0);
    const completed = tasks.filter(t => t.completed).length;
    const score = tasks.length > 0 ? Math.round((completed / tasks.length) * 100) : 0;

    document.getElementById('stToday').textContent = todayTasks.length;
    document.getElementById('stDeadlines').textContent = weekTasks.length;
    document.getElementById('stHours').textContent = (totalMinutes / 60).toFixed(1) + 'h';
    document.getElementById('stScore').textContent = score + '%';

    // Today's schedule
    const schedule = document.getElementById('todaySchedule');
    const pendingToday = tasks.filter(t => !t.completed).sort((a, b) => { const p = { high: 0, medium: 1, low: 2 }; return p[a.priority] - p[b.priority]; });
    if (!pendingToday.length) {
        schedule.innerHTML = `<div class="text-center py-8"><p class="text-sm" style="color:var(--text-dim)">All caught up! No pending tasks.</p></div>`;
    } else {
        schedule.innerHTML = pendingToday.slice(0, 6).map((t, i) => {
            const time = new Date(t.createdAt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            return `<div class="flex items-center gap-3 p-3 rounded-xl" style="background:var(--surface-2)">
                <div class="priority-dot ${t.priority}"></div>
                <div class="flex-1 min-w-0"><p class="text-sm font-600 truncate">${esc(t.title)}</p><p class="text-xs" style="color:var(--text-dim)">${t.subject || 'General'}${t.dueDate ? ' · Due ' + formatDate(t.dueDate) : ''}</p></div>
                <button onclick="toggleTask('${t.id}')" class="action-btn sm primary">Done</button>
            </div>`;
        }).join('');
    }

    // Weekly overview
    const weekGrid = document.getElementById('weekGrid');
    const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const now = new Date();
    const todayDayIdx = (now.getDay() + 6) % 7; // Mon=0

    weekGrid.innerHTML = dayNames.map((name, i) => {
        const d = new Date(now); d.setDate(d.getDate() - todayDayIdx + i);
        const ds = d.toISOString().slice(0, 10);
        const isToday = ds === todayStr;
        const dayTasks = tasks.filter(t => t.dueDate === ds);
        const dayDone = dayTasks.filter(t => t.completed).length;
        const dayMins = sessions.filter(s => s.date === ds).reduce((s, se) => s + se.duration, 0);
        return `<div class="text-center p-3 rounded-xl" style="background:${isToday ? 'var(--accent-dim)' : 'var(--surface-2)'};border:1px solid ${isToday ? 'var(--accent-border)' : 'var(--border)'}">
            <p class="text-xs font-600" style="color:${isToday ? 'var(--accent)' : 'var(--text-dim)'}">${name}</p>
            <p class="text-lg font-800 mt-1" style="color:${isToday ? 'var(--accent)' : 'var(--text)'}">${d.getDate()}</p>
            ${dayMins > 0 ? `<p class="text-[10px] mt-1" style="color:var(--text-dim)">${Math.round(dayMins/60*10)/10}h</p>` : ''}
            ${dayTasks.length > 0 ? `<p class="text-[10px]" style="color:${dayDone===dayTasks.length?'var(--accent)':'var(--text-dim)'}">${dayDone}/${dayTasks.length}</p>` : ''}
        </div>`;
    }).join('');

    // Goals
    const goals = getGoals();
    const goalsList = document.getElementById('goalsList');
    if (!goals.length) {
        goalsList.innerHTML = `<div class="text-center py-6"><p class="text-sm" style="color:var(--text-dim)">No goals yet. Add one to track progress.</p></div>`;
    } else {
        goalsList.innerHTML = goals.map(g => {
            const pct = g.target > 0 ? Math.round((g.current / g.target) * 100) : 0;
            return `<div class="flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-sm font-600 truncate">${esc(g.title)}</p>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-600" style="color:var(--text-muted)">${g.current}/${g.target} min</span>
                            <button onclick="updateGoalProgress('${g.id}', ${Math.min(15, g.target - g.current)})" class="text-xs px-2 py-0.5 rounded" style="background:var(--accent-dim);color:var(--accent);border:none;cursor:pointer">+15</button>
                            <button onclick="deleteGoal('${g.id}')" class="text-xs px-2 py-0.5 rounded" style="background:var(--danger-dim);color:var(--danger);border:none;cursor:pointer"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                    <div class="progress-track"><div class="progress-fill" style="width:${pct}%"></div></div>
                </div>
            </div>`;
        }).join('');
    }
}

// ── History ──
function logHistory(text) {
    const h = getHistory(); h.unshift({ text, time: Date.now() }); if (h.length > 30) h.length = 30; saveHistory(h);
}

// ── Sidebar ──
function updateSidebar() {
    const tasks = getTasks();
    const completed = tasks.filter(t => t.completed).length;
    const sessions = getSessions();
    const totalSessions = sessions.length;

    // Streak
    const uniqueDays = [...new Set(sessions.map(s => s.date))].sort().reverse();
    let streak = 0;
    if (uniqueDays.length) {
        const check = new Date();
        for (let i = 0; i < 365; i++) {
            const ds = check.toISOString().slice(0, 10);
            if (uniqueDays.includes(ds)) { streak++; check.setDate(check.getDate() - 1); }
            else if (i === 0) { check.setDate(check.getDate() - 1); continue; }
            else break;
        }
    }

    document.getElementById('sbTasks').textContent = tasks.length;
    document.getElementById('sbDone').textContent = completed;
    document.getElementById('sbSessions').textContent = totalSessions;
    document.getElementById('sbStreak').textContent = streak;

    // Weekly bars
    const bars = document.querySelectorAll('#weeklyBars .week-bar');
    const now = new Date();
    const todayDayIdx = (now.getDay() + 6) % 7;
    let maxMins = 1;
    const weekMins = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(now); d.setDate(d.getDate() - todayDayIdx + i);
        const ds = d.toISOString().slice(0, 10);
        const mins = sessions.filter(s => s.date === ds).reduce((s, se) => s + se.duration, 0);
        weekMins.push(mins); if (mins > maxMins) maxMins = mins;
    }
    bars.forEach((bar, i) => {
        const pct = Math.max(5, Math.round((weekMins[i] / maxMins) * 100));
        bar.style.height = pct + '%';
        bar.style.background = `rgba(34,211,238,${0.18 + (weekMins[i] / maxMins) * 0.42})`;
    });

    // Deadlines
    const deadlines = document.getElementById('deadlinesList');
    const upcoming = tasks.filter(t => !t.completed && t.dueDate).sort((a, b) => a.dueDate.localeCompare(b.dueDate)).slice(0, 5);
    if (!upcoming.length) { deadlines.innerHTML = '<p class="text-xs text-center py-4" style="color:var(--text-dim)">No upcoming deadlines</p>'; }
    else {
        deadlines.innerHTML = upcoming.map(t => {
            const days = daysUntil(t.dueDate);
            const color = days < 0 ? 'var(--danger)' : days <= 2 ? 'var(--warning)' : 'var(--text-dim)';
            return `<div class="flex items-center gap-2 text-xs p-2 rounded-lg" style="background:var(--surface-2)">
                <div class="priority-dot ${t.priority}"></div>
                <div class="flex-1 min-w-0"><p class="truncate font-600" style="color:var(--text)">${esc(t.title)}</p><p style="color:${color}">${formatDate(t.dueDate)}${days === 0 ? ' · Today' : days === 1 ? ' · Tomorrow' : days < 0 ? ` · ${Math.abs(days)}d overdue` : ` · ${days}d left`}</p></div>
            </div>`;
        }).join('');
    }
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    state.timerSeconds = state.timerDuration * 60;
    renderOverview();
    renderTasks();
    renderTimer();
    updateSidebar();
});
</script>
@endsection