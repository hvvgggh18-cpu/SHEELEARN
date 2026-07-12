@extends('layouts.dashboard-layout')

@section('title', 'Analytics — SHEELEARN AI')

@section('styles')
<style>
    :root {
        --ax-bg: rgba(10, 15, 30, 0.72);
        --ax-bg-deep: rgba(6, 10, 22, 0.85);
        --ax-border: rgba(148, 163, 184, 0.07);
        --ax-border-hover: rgba(34, 211, 238, 0.15);
        --ax-surface: rgba(15, 23, 42, 0.5);
        --ax-cyan: #22d3ee;
        --ax-indigo: #818cf8;
        --ax-emerald: #34d399;
        --ax-amber: #fbbf24;
        --ax-rose: #fb7185;
        --ax-text-primary: #f1f5f9;
        --ax-text-secondary: rgba(203, 213, 225, 0.7);
        --ax-text-muted: rgba(148, 163, 184, 0.45);
        --ax-radius: 20px;
        --ax-radius-sm: 14px;
        --ax-radius-xs: 10px;
    }

    /* ── Header ── */
    .ax-header {
        border-radius: var(--ax-radius);
        border: 1px solid var(--ax-border);
        background: var(--ax-bg);
        backdrop-filter: blur(40px);
        -webkit-backdrop-filter: blur(40px);
    }

    /* ── Cards ── */
    .ax-card {
        border-radius: var(--ax-radius);
        border: 1px solid var(--ax-border);
        background: var(--ax-bg);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                    border-color 0.35s ease,
                    box-shadow 0.35s ease;
        position: relative;
        overflow: hidden;
    }
    .ax-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.35s ease;
        pointer-events: none;
        background: radial-gradient(600px circle at var(--mx, 50%) var(--my, 50%),
            rgba(34, 211, 238, 0.04), transparent 40%);
    }
    .ax-card:hover::before { opacity: 1; }
    .ax-card:hover {
        transform: translateY(-3px);
        border-color: var(--ax-border-hover);
        box-shadow: 0 20px 50px -12px rgba(34, 211, 238, 0.08),
                    0 0 0 1px rgba(34, 211, 238, 0.04);
    }

    /* ── Stat cards ── */
    .ax-stat {
        padding: 22px 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ax-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--ax-radius-xs);
        display: grid;
        place-items: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .ax-stat-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--ax-text-muted);
    }
    .ax-stat-value {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1;
        color: var(--ax-text-primary);
        letter-spacing: -0.02em;
    }
    .ax-stat-change {
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 999px;
    }
    .ax-stat-change.up {
        color: var(--ax-emerald);
        background: rgba(52, 211, 153, 0.1);
    }
    .ax-stat-change.down {
        color: var(--ax-rose);
        background: rgba(251, 113, 133, 0.1);
    }
    .ax-stat-change.neutral {
        color: var(--ax-text-muted);
        background: rgba(148, 163, 184, 0.08);
    }
    .ax-sparkline-wrap {
        height: 40px;
        margin: 0 -24px -22px;
        padding: 0 8px;
        opacity: 0.7;
    }

    /* ── Section titles ── */
    .ax-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: var(--ax-text-muted);
    }
    .ax-section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--ax-text-primary);
        letter-spacing: -0.01em;
        margin-top: 4px;
    }

    /* ── Chart switcher ── */
    .ax-switch {
        display: flex;
        gap: 4px;
        padding: 4px;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid var(--ax-border);
    }
    .ax-switch button {
        border: none;
        border-radius: 9px;
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 600;
        color: var(--ax-text-muted);
        background: transparent;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    .ax-switch button:hover { color: var(--ax-text-secondary); }
    .ax-switch button.active {
        background: rgba(34, 211, 238, 0.1);
        color: var(--ax-cyan);
        box-shadow: 0 0 12px rgba(34, 211, 238, 0.08);
    }

    /* ── Inner tiles ── */
    .ax-tile {
        border-radius: var(--ax-radius-sm);
        border: 1px solid var(--ax-border);
        background: var(--ax-bg-deep);
        padding: 16px 18px;
        transition: border-color 0.3s ease;
    }
    .ax-tile:hover { border-color: rgba(148, 163, 184, 0.12); }
    .ax-tile-label {
        font-size: 12px;
        color: var(--ax-text-muted);
        margin-bottom: 8px;
    }
    .ax-tile-value {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--ax-text-primary);
    }

    /* ── Score ring ── */
    .ax-ring { width: 160px; height: 160px; }
    .ax-ring-track { stroke: rgba(148, 163, 184, 0.08); }
    .ax-ring-fill {
        transition: stroke-dashoffset 1s cubic-bezier(0.16, 1, 0.3, 1), stroke 0.4s ease;
    }

    /* ── Progress ── */
    .ax-progress-track {
        height: 8px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.08);
        overflow: hidden;
    }
    .ax-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--ax-cyan), var(--ax-indigo));
        transition: width 0.9s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* ── Heatmap ── */
    .ax-heatmap {
        display: grid;
        grid-template-columns: repeat(8, minmax(0, 1fr));
        gap: 5px;
    }
    .ax-heatmap-cell {
        width: 100%;
        padding-bottom: 100%;
        border-radius: 8px;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: default;
    }
    .ax-heatmap-cell:hover {
        transform: scale(1.12);
        box-shadow: 0 4px 16px rgba(34, 211, 238, 0.12);
        z-index: 2;
    }
    .ax-heatmap-cell span {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 600;
        color: rgba(241, 245, 249, 0.75);
    }

    /* ── Badges ── */
    .ax-badge {
        border-radius: var(--ax-radius-sm);
        border: 1px solid var(--ax-border);
        background: var(--ax-surface);
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        transition: border-color 0.3s ease;
    }
    .ax-badge:hover { border-color: rgba(148, 163, 184, 0.14); }
    .ax-badge.locked { filter: grayscale(0.8) brightness(0.85); opacity: 0.5; }
    .ax-badge-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .ax-badge-title { font-size: 13px; font-weight: 700; color: var(--ax-text-primary); }
    .ax-badge-desc { font-size: 12px; color: var(--ax-text-muted); margin-top: 3px; }
    .ax-badge-meta {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--ax-text-muted);
        margin-top: 10px;
    }

    /* ── Timeline ── */
    .ax-timeline-item {
        border-radius: var(--ax-radius-sm);
        border: 1px solid var(--ax-border);
        background: var(--ax-bg-deep);
        padding: 14px 16px;
        transition: border-color 0.3s ease;
    }
    .ax-timeline-item:hover { border-color: rgba(148, 163, 184, 0.12); }
    .ax-timeline-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 5px;
    }

    /* ── Insight cards ── */
    .ax-insight {
        border-radius: var(--ax-radius-sm);
        border: 1px solid var(--ax-border);
        background: var(--ax-bg-deep);
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: border-color 0.3s ease;
    }
    .ax-insight:hover { border-color: rgba(148, 163, 184, 0.14); }
    .ax-insight-icon {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        display: grid;
        place-items: center;
        font-size: 0.9rem;
        flex-shrink: 0;
        background: rgba(34, 211, 238, 0.08);
        color: var(--ax-cyan);
    }

    /* ── Select ── */
    .ax-select {
        width: 100%;
        border-radius: var(--ax-radius-xs);
        border: 1px solid var(--ax-border);
        background: rgba(15, 23, 42, 0.6);
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 500;
        color: var(--ax-text-primary);
        outline: none;
        cursor: pointer;
        transition: border-color 0.25s ease;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 34px;
    }
    .ax-select:focus { border-color: rgba(34, 211, 238, 0.3); }
    .ax-select option { background: #0f172a; color: #e2e8f0; }

    /* ── Empty state ── */
    .ax-empty {
        border-radius: 28px;
        padding: 72px 32px;
        background: var(--ax-bg);
        border: 1px solid var(--ax-border);
        backdrop-filter: blur(24px);
    }

    /* ── Legend dot ── */
    .ax-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    /* ── Scrollbar ── */
    .ax-card ::-webkit-scrollbar { width: 4px; height: 4px; }
    .ax-card ::-webkit-scrollbar-track { background: transparent; }
    .ax-card ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.15); border-radius: 999px; }

    /* ── Responsive tweaks ── */
    @media (max-width: 640px) {
        .ax-stat-value { font-size: 1.5rem; }
        .ax-header-inner { flex-direction: column; align-items: stretch !important; }
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    {{-- ━━ Header ━━ --}}
    <div class="ax-header px-6 py-5 lg:px-8 lg:py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 ax-header-inner">
            <div>
                <div class="ax-section-label">Overview</div>
                <h1 class="text-2xl lg:text-3xl font-extrabold text-c-60 tracking-tight mt-1">Analytics</h1>
                <p class="text-sm text-c-40 mt-1.5 max-w-md leading-relaxed">Your learning performance, AI usage, and progress insights — all in one place.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="ax-tile !py-3 !px-4 min-w-[160px]">
                    <div class="ax-tile-label !mb-1">Date</div>
                    <div class="text-sm font-bold text-c-60" id="analyticsDate">--</div>
                    <div class="text-[10px] text-c-25 mt-0.5" id="analyticsLastUpdated">Updated just now</div>
                </div>
                <div class="ax-tile !py-3 !px-4">
                    <div class="ax-tile-label !mb-1.5">Period</div>
                    <select id="analyticsFilter" class="ax-select !py-2">
                        <option value="today">Today</option>
                        <option value="last7days" selected>Last 7 Days</option>
                        <option value="last30days">Last 30 Days</option>
                        <option value="last3months">Last 3 Months</option>
                        <option value="alltime">All Time</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ━━ Stats Row ━━ --}}
    <div id="analyticsContent" class="space-y-5">

        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            {{-- Study Hours --}}
            <div class="ax-card ax-stat">
                <div class="flex items-center justify-between">
                    <div class="ax-stat-icon" style="background:rgba(34,211,238,0.1); color:var(--ax-cyan);">⏱</div>
                    <span class="ax-stat-change neutral" id="statStudyTrend">+0%</span>
                </div>
                <div>
                    <div class="ax-stat-label">Study Hours</div>
                    <div class="ax-stat-value" id="statStudyHours">0 hrs</div>
                </div>
                <div class="ax-sparkline-wrap">
                    <canvas id="sparklineStudyHours"></canvas>
                </div>
            </div>

            {{-- AI Chats --}}
            <div class="ax-card ax-stat">
                <div class="flex items-center justify-between">
                    <div class="ax-stat-icon" style="background:rgba(129,140,248,0.1); color:var(--ax-indigo);">✦</div>
                    <span class="ax-stat-change neutral" id="statAiTrend">+0%</span>
                </div>
                <div>
                    <div class="ax-stat-label">AI Chats</div>
                    <div class="ax-stat-value" id="statAiChats">0</div>
                </div>
            </div>

            {{-- Documents --}}
            <div class="ax-card ax-stat">
                <div class="ax-stat-icon" style="background:rgba(52,211,153,0.1); color:var(--ax-emerald);">📄</div>
                <div>
                    <div class="ax-stat-label">Documents</div>
                    <div class="ax-stat-value" id="statDocuments">0</div>
                </div>
                <div class="text-[11px] text-c-25">files uploaded</div>
            </div>

            {{-- Flashcards --}}
            <div class="ax-card ax-stat">
                <div class="ax-stat-icon" style="background:rgba(251,191,36,0.1); color:var(--ax-amber);">acter</div>
                <div>
                    <div class="ax-stat-label">Flashcards</div>
                    <div class="ax-stat-value" id="statFlashcards">0</div>
                </div>
                <div class="text-[11px] text-c-25">cards reviewed</div>
            </div>

            {{-- Quiz Accuracy --}}
            <div class="ax-card ax-stat">
                <div class="ax-stat-icon" style="background:rgba(251,113,133,0.1); color:var(--ax-rose);">◎</div>
                <div>
                    <div class="ax-stat-label">Accuracy</div>
                    <div class="ax-stat-value" id="statQuizAccuracy">0%</div>
                </div>
                <div class="text-[11px]" id="statQuizRating" style="color:var(--ax-text-muted);">—</div>
            </div>

            {{-- Streak --}}
            <div class="ax-card ax-stat">
                <div class="ax-stat-icon" style="background:rgba(251,146,60,0.1); color:#fb923c;">🔥</div>
                <div>
                    <div class="ax-stat-label">Streak</div>
                    <div class="ax-stat-value" id="statStreak">0</div>
                </div>
                <div class="text-[11px] text-c-25">days</div>
            </div>
        </div>

        {{-- ━━ Activity Chart ━━ --}}
        <div class="ax-card p-5 lg:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <div class="ax-section-label">Trends</div>
                    <div class="ax-section-title">Study Activity</div>
                </div>
                <div class="ax-switch">
                    <button type="button" class="active" data-period="daily">Daily</button>
                    <button type="button" data-period="weekly">Weekly</button>
                    <button type="button" data-period="monthly">Monthly</button>
                </div>
            </div>
            <div class="w-full" style="min-height:300px;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        {{-- ━━ Distribution + Day Bar ━━ --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="ax-card p-5 lg:col-span-2">
                <div class="mb-5">
                    <div class="ax-section-label">Breakdown</div>
                    <div class="ax-section-title">Content Mix</div>
                </div>
                <div class="flex flex-col items-center gap-5">
                    <div class="w-full max-w-[200px]">
                        <canvas id="distributionChart"></canvas>
                    </div>
                    <div id="distributionLegend" class="grid grid-cols-2 gap-2 w-full"></div>
                </div>
            </div>
            <div class="ax-card p-5 lg:col-span-3">
                <div class="mb-5">
                    <div class="ax-section-label">Rhythm</div>
                    <div class="ax-section-title">Study Time by Day</div>
                </div>
                <div style="min-height:260px;">
                    <canvas id="dayBarChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ━━ AI Usage + Score Ring ━━ --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <div class="ax-card p-5 lg:col-span-3">
                <div class="mb-5">
                    <div class="ax-section-label">AI</div>
                    <div class="ax-section-title">Usage Insights</div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="ax-tile">
                        <div class="ax-tile-label">Questions Asked</div>
                        <div class="ax-tile-value" id="usageQuestions">0</div>
                    </div>
                    <div class="ax-tile">
                        <div class="ax-tile-label">Avg. Response</div>
                        <div class="ax-tile-value" id="usageResponseTime">0s</div>
                    </div>
                    <div class="ax-tile">
                        <div class="ax-tile-label">Conv. Length</div>
                        <div class="ax-tile-value" id="usageConversationLength">0 msg</div>
                    </div>
                    <div class="ax-tile">
                        <div class="ax-tile-label">Top Feature</div>
                        <div class="ax-tile-value text-base" id="usageFeature">—</div>
                    </div>
                </div>
            </div>
            <div class="ax-card p-5 lg:col-span-2 flex flex-col items-center justify-center text-center">
                <div class="ax-section-label mb-4">Productivity</div>
                <div class="ax-ring relative mb-4">
                    <svg viewBox="0 0 160 160" class="w-full h-full" style="transform:rotate(-90deg);">
                        <circle cx="80" cy="80" r="66" fill="none" class="ax-ring-track" stroke-width="14"/>
                        <circle id="productivityRing" cx="80" cy="80" r="66" fill="none"
                            stroke="var(--ax-cyan)" stroke-width="14" stroke-linecap="round"
                            class="ax-ring-fill"
                            stroke-dasharray="414.7" stroke-dashoffset="414.7"/>
                    </svg>
                    <div class="absolute inset-0 grid place-items-center">
                        <div>
                            <div class="text-3xl font-extrabold text-c-60" id="productivityScore">0</div>
                            <div class="text-[11px] text-c-25 font-medium">out of 100</div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-c-40 max-w-[240px] leading-relaxed" id="productivitySummary">Start learning to see your score.</p>
            </div>
        </div>

        {{-- ━━ Timeline + Achievements ━━ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="ax-card p-5">
                <div class="mb-5">
                    <div class="ax-section-label">Log</div>
                    <div class="ax-section-title">Recent Activity</div>
                </div>
                <div id="timelineList" class="space-y-3 max-h-[380px] overflow-y-auto pr-1"></div>
            </div>
            <div class="ax-card p-5">
                <div class="mb-5">
                    <div class="ax-section-label">Rewards</div>
                    <div class="ax-section-title">Achievements</div>
                </div>
                <div id="achievementsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[380px] overflow-y-auto pr-1"></div>
            </div>
        </div>

        {{-- ━━ Goals ━━ --}}
        <div class="ax-card p-5 lg:p-6">
            <div class="mb-5">
                <div class="ax-section-label">Targets</div>
                <div class="ax-section-title">Learning Goals</div>
            </div>
            <div id="goalsProgress" class="space-y-4"></div>
        </div>

        {{-- ━━ Heatmap ━━ --}}
        <div class="ax-card p-5 lg:p-6">
            <div class="mb-5">
                <div class="ax-section-label">Intensity</div>
                <div class="ax-section-title">Weekly Heatmap</div>
            </div>
            <div class="ax-heatmap" id="heatmapGrid"></div>
        </div>

        {{-- ━━ AI Insights ━━ --}}
        <div class="ax-card p-5 lg:p-6">
            <div class="mb-5">
                <div class="ax-section-label">Smart</div>
                <div class="ax-section-title">AI Insights</div>
            </div>
            <div id="insightsList" class="grid gap-3 sm:grid-cols-2"></div>
        </div>

    </div>

    {{-- ━━ Empty State ━━ --}}
    <div id="analyticsEmptyState" class="ax-empty hidden text-center">
        <div class="text-5xl mb-5 opacity-60">📊</div>
        <h2 class="text-xl font-bold text-c-60 mb-2">No learning data yet</h2>
        <p class="max-w-md mx-auto text-sm text-c-40 mb-6 leading-relaxed">Start chatting with AI, uploading documents, or taking quizzes to unlock your personal analytics dashboard.</p>
        <a href="{{ route('ai-chat') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full text-sm font-semibold text-n bg-cy hover:bg-cy/90 transition-colors">
            Start Learning
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(() => {
    /* ── State ── */
    const endpoint = '{{ route("analytics.stats") }}';
    const charts = { spark: null, activity: null, distribution: null, dayBar: null };
    let filter = 'last7days', period = 'daily';

    /* ── Utilities ── */
    const $ = id => document.getElementById(id);
    const fmt = v => typeof v === 'number' ? v.toLocaleString() : v;

    function animateValue(id, target, suffix = '') {
        const el = $(id);
        if (!el) return;
        const start = parseFloat(el.dataset.val || el.textContent.replace(/[^0-9.\-]/g, '')) || 0;
        const dur = 650, t0 = performance.now();
        el.dataset.val = target;
        (function step(now) {
            const p = Math.min((now - t0) / dur, 1);
            const e = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(Math.round(start + (target - start) * e)) + suffix;
            if (p < 1) requestAnimationFrame(step);
        })(t0);
    }

    function setChange(id, text) {
        const el = $(id);
        if (!el) return;
        const num = parseFloat(text.replace(/[^0-9.\-]/g, ''));
        el.textContent = text;
        el.className = 'ax-stat-change ' + (num > 0 ? 'up' : num < 0 ? 'down' : 'neutral');
    }

    /* ── Card mouse glow ── */
    document.addEventListener('mousemove', e => {
        document.querySelectorAll('.ax-card').forEach(card => {
            const r = card.getBoundingClientRect();
            card.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
            card.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    });

    /* ── Chart defaults ── */
    Chart.defaults.font.family = "system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#94a3b8';

    const gridColor = 'rgba(148,163,184,0.06)';

    /* ── Sparkline ── */
    function drawSparkline(labels, data) {
        const ctx = $('sparklineStudyHours');
        if (!ctx) return;
        if (charts.spark) charts.spark.destroy();
        charts.spark = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ data, borderColor: '#22d3ee', borderWidth: 2, fill: { target: 'origin', above: 'rgba(34,211,238,0.06)' }, tension: 0.45, pointRadius: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } }
        });
    }

    /* ── Activity chart ── */
    function drawActivity(labels, study, ai, quiz) {
        const ctx = $('activityChart');
        if (!ctx) return;
        if (charts.activity) charts.activity.destroy();

        const makeDataset = (label, data, color) => ({
            label, data,
            borderColor: color,
            backgroundColor: color.replace(')', ',0.08)').replace('rgb', 'rgba'),
            tension: 0.4, pointRadius: 3, pointHoverRadius: 6,
            pointBackgroundColor: color, pointBorderColor: 'transparent',
            pointHoverBorderColor: '#0f172a', pointHoverBorderWidth: 3,
            borderWidth: 2.5, fill: true
        });

        charts.activity = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [
                makeDataset('Study Hours', study, 'rgb(34,211,238)'),
                makeDataset('AI Chats', ai, 'rgb(129,140,248)'),
                makeDataset('Quizzes', quiz, 'rgb(52,211,153)')
            ]},
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'circle', padding: 20, font: { size: 11, weight: '600' } } },
                    tooltip: { backgroundColor: 'rgba(10,15,30,0.92)', titleColor: '#f1f5f9', bodyColor: '#cbd5e1', borderColor: 'rgba(148,163,184,0.1)', borderWidth: 1, padding: 12, cornerRadius: 10, titleFont: { weight: '700' }, bodyFont: { size: 12 } }
                },
                scales: {
                    x: { ticks: { font: { size: 11 }, maxRotation: 0 }, grid: { color: gridColor, drawBorder: false } },
                    y: { beginAtZero: true, ticks: { font: { size: 11 }, padding: 8 }, grid: { color: gridColor, drawBorder: false } }
                }
            }
        });
    }

    /* ── Distribution doughnut ── */
    function drawDistribution(labels, values, colors) {
        const ctx = $('distributionChart');
        if (!ctx) return;
        if (charts.distribution) charts.distribution.destroy();
        charts.distribution = new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0, spacing: 3 }] },
            options: {
                responsive: true, cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(10,15,30,0.92)', titleColor: '#f1f5f9', bodyColor: '#cbd5e1', borderColor: 'rgba(148,163,184,0.1)', borderWidth: 1, padding: 10, cornerRadius: 8 } }
            }
        });
    }

    function renderLegend(items) {
        const el = $('distributionLegend');
        if (!el) return;
        el.innerHTML = items.map(i => `
            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg" style="background:rgba(15,23,42,0.5); border:1px solid var(--ax-border);">
                <span class="ax-legend-dot" style="background:${i.color}"></span>
                <div>
                    <div class="text-xs font-semibold text-c-60">${i.label}</div>
                    <div class="text-[10px] text-c-25">${i.percentage}%</div>
                </div>
            </div>`).join('');
    }

    /* ── Day bar chart ── */
    function drawDayBar(labels, values) {
        const ctx = $('dayBarChart');
        if (!ctx) return;
        if (charts.dayBar) charts.dayBar.destroy();
        charts.dayBar = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{ data: values, backgroundColor: 'rgba(34,211,238,0.65)', hoverBackgroundColor: 'rgba(34,211,238,0.9)', borderRadius: 8, maxBarThickness: 36, borderSkipped: false }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(10,15,30,0.92)', titleColor: '#f1f5f9', bodyColor: '#cbd5e1', borderColor: 'rgba(148,163,184,0.1)', borderWidth: 1, padding: 10, cornerRadius: 8, callbacks: { label: c => c.parsed.y + ' hrs' } } },
                scales: {
                    x: { ticks: { font: { size: 11, weight: '600' } }, grid: { display: false, drawBorder: false } },
                    y: { beginAtZero: true, ticks: { font: { size: 11 }, padding: 8 }, grid: { color: gridColor, drawBorder: false } }
                }
            }
        });
    }

    /* ── Ring ── */
    function updateRing(score, color) {
        const ring = $('productivityRing');
        if (!ring) return;
        const c = 2 * Math.PI * 66;
        ring.style.strokeDashoffset = c - (Math.min(100, Math.max(0, score)) / 100) * c;
        ring.style.stroke = color;
        animateValue('productivityScore', score);
    }

    const ringColors = { red: '#fb7185', orange: '#fb923c', blue: '#818cf8', green: '#34d399' };

    /* ── Timeline ── */
    function renderTimeline(items) {
        const el = $('timelineList');
        if (!el) return;
        const dotColors = ['#22d3ee', '#818cf8', '#34d399', '#fbbf24', '#fb7185'];
        el.innerHTML = items.map((item, i) => `
            <div class="ax-timeline-item flex gap-3">
                <div class="ax-timeline-dot" style="background:${dotColors[i % dotColors.length]}"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[13px] font-semibold text-c-60 truncate">${item.title}</span>
                        <span class="text-[10px] font-semibold uppercase tracking-widest text-c-25 whitespace-nowrap">${item.label}</span>
                    </div>
                    ${item.subtitle ? `<p class="text-xs text-c-40 mt-1 truncate">${item.subtitle}</p>` : ''}
                    <div class="text-[10px] text-c-25 mt-1.5">${item.time}</div>
                </div>
            </div>`).join('');
    }

    /* ── Achievements ── */
    function renderAchievements(items) {
        const el = $('achievementsGrid');
        if (!el) return;
        const iconBgs = ['rgba(34,211,238,0.1)', 'rgba(129,140,248,0.1)', 'rgba(52,211,153,0.1)', 'rgba(251,191,36,0.1)'];
        const iconFgs = ['#22d3ee', '#818cf8', '#34d399', '#fbbf24'];
        el.innerHTML = items.map((item, i) => `
            <div class="ax-badge ${item.earned ? '' : 'locked'}">
                <div class="ax-badge-icon" style="background:${iconBgs[i % iconBgs.length]}; color:${iconFgs[i % iconFgs.length]};">${item.icon}</div>
                <div class="min-w-0">
                    <div class="ax-badge-title">${item.title}</div>
                    <div class="ax-badge-desc">${item.description}</div>
                    <div class="ax-badge-meta">${item.earned ? 'Earned ' + item.date : 'Locked'}</div>
                </div>
            </div>`).join('');
    }

    /* ── Goals ── */
    function renderGoals(goals) {
        const el = $('goalsProgress');
        if (!el) return;
        el.innerHTML = goals.map(g => {
            const pct = Math.min(100, Math.round((g.current / g.goal) * 100));
            return `
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-c-60">${g.label}</span>
                    <span class="text-xs font-medium text-c-40">${fmt(g.current)} / ${fmt(g.goal)} <span class="text-c-25 ml-1">${pct}%</span></span>
                </div>
                <div class="ax-progress-track"><div class="ax-progress-fill" style="width:${pct}%"></div></div>
            </div>`;
        }).join('');
    }

    /* ── Heatmap ── */
    function renderHeatmap(data) {
        const el = $('heatmapGrid');
        if (!el) return;
        const max = Math.max(...data.map(d => d.value), 1);
        el.innerHTML = data.map(d => {
            const t = Math.min(1, d.value / max);
            const bg = t > 0 ? `rgba(34,211,238,${(0.12 + t * 0.5).toFixed(2)})` : 'rgba(148,163,184,0.06)';
            return `<div class="ax-heatmap-cell" style="background:${bg};" title="${d.date} · ${d.studyHours}h · ${d.chatCount} chats · ${d.quizCount} quizzes"><span>${d.weekday}</span></div>`;
        }).join('');
    }

    /* ── Insights ── */
    function renderInsights(insights) {
        const el = $('insightsList');
        if (!el) return;
        const icons = ['💡', '🎯', '📈', '⚡', '🧠', '🔬'];
        el.innerHTML = insights.map((text, i) => `
            <div class="ax-insight">
                <div class="ax-insight-icon">${icons[i % icons.length]}</div>
                <p class="text-[13px] text-c-40 leading-relaxed">${text}</p>
            </div>`).join('');
    }

    /* ── Render all ── */
    function render(d) {
        if (!d || d.empty) {
            $('analyticsEmptyState').classList.remove('hidden');
            $('analyticsContent').classList.add('hidden');
            return;
        }
        $('analyticsEmptyState').classList.add('hidden');
        $('analyticsContent').classList.remove('hidden');

        $('analyticsDate').textContent = d.currentDate;
        $('analyticsLastUpdated').textContent = d.lastUpdatedLabel;

        animateValue('statStudyHours', d.summary.studyHours, ' hrs');
        setChange('statStudyTrend', d.summary.studyTrend);
        animateValue('statAiChats', d.summary.aiChats);
        setChange('statAiTrend', d.summary.aiTrend);
        animateValue('statDocuments', d.summary.documents);
        animateValue('statFlashcards', d.summary.flashcardsReviewed);
        animateValue('statQuizAccuracy', d.summary.quizAccuracy, '%');
        $('statQuizRating').textContent = d.summary.quizAccuracy >= 90 ? 'Excellent' : d.summary.quizAccuracy >= 75 ? 'Strong' : 'Improving';
        $('statQuizRating').style.color = d.summary.quizAccuracy >= 90 ? '#34d399' : d.summary.quizAccuracy >= 75 ? '#22d3ee' : '#fbbf24';
        animateValue('statStreak', d.summary.currentStreak);

        drawSparkline(d.chart.labels, d.chart.studyHours);
        drawActivity(d.chart.labels, d.chart.studyHours, d.chart.aiChats, d.chart.quizSessions);

        const dColors = ['#22d3ee', '#818cf8', '#34d399', '#fbbf24', '#fb7185'];
        drawDistribution(d.distribution.map(i => i.label), d.distribution.map(i => i.value), dColors);
        renderLegend(d.distribution.map((item, i) => ({ ...item, color: dColors[i] || '#22d3ee' })));

        drawDayBar(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], d.studyByDay);

        $('usageQuestions').textContent = fmt(d.aiUsage.totalQuestions);
        $('usageResponseTime').textContent = d.aiUsage.averageResponseTime + 's';
        $('usageConversationLength').textContent = d.aiUsage.averageConversationLength + ' msg';
        $('usageFeature').textContent = d.aiUsage.mostUsedFeature;

        updateRing(d.productivity.score, ringColors[d.productivity.color] || '#22d3ee');
        $('productivitySummary').textContent = d.productivity.summary;

        renderTimeline(d.timeline);
        renderAchievements(d.achievements);
        renderGoals(d.goals);
        renderHeatmap(d.heatmap);
        renderInsights(d.insights);
    }

    /* ── Fetch ── */
    async function load() {
        try {
            const r = await fetch(`${endpoint}?filter=${filter}&period=${period}`);
            if (!r.ok) throw new Error();
            render(await r.json());
        } catch (e) { console.error('Analytics load failed:', e); }
    }

    /* ── Controls ── */
    function init() {
        document.querySelectorAll('.ax-switch button').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.ax-switch button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                period = btn.dataset.period || 'daily';
                load();
            });
        });
        $('analyticsFilter')?.addEventListener('change', e => { filter = e.target.value; load(); });
        load();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
</script>
@endsection