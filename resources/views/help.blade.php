@extends('layouts.dashboard-layout')

@section('title', 'Help & Support — SHEELEARN AI')

@section('styles')
<style>
    :root {
        --hs-bg: rgba(10, 15, 30, 0.72);
        --hs-bg-deep: rgba(6, 10, 22, 0.88);
        --hs-border: rgba(148, 163, 184, 0.07);
        --hs-border-hover: rgba(34, 211, 238, 0.14);
        --hs-surface: rgba(15, 23, 42, 0.45);
        --hs-cyan: #22d3ee;
        --hs-indigo: #818cf8;
        --hs-emerald: #34d399;
        --hs-amber: #fbbf24;
        --hs-rose: #fb7185;
        --hs-text-1: #f1f5f9;
        --hs-text-2: rgba(203, 213, 225, 0.68);
        --hs-text-3: rgba(148, 163, 184, 0.4);
        --hs-r: 18px;
        --hs-r-sm: 12px;
        --hs-r-xs: 8px;
    }

    /* ── Card ── */
    .hs-card {
        border-radius: var(--hs-r);
        border: 1px solid var(--hs-border);
        background: var(--hs-bg);
        backdrop-filter: blur(32px);
        -webkit-backdrop-filter: blur(32px);
        position: relative;
        overflow: hidden;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .hs-card::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        background: radial-gradient(500px circle at var(--mx, 50%) var(--my, 50%),
            rgba(34, 211, 238, 0.03), transparent 40%);
    }
    .hs-card:hover::before { opacity: 1; }
    .hs-card:hover {
        border-color: var(--hs-border-hover);
        box-shadow: 0 16px 48px -12px rgba(34, 211, 238, 0.06);
    }

    /* ── Input ── */
    .hs-input {
        width: 100%;
        border-radius: var(--hs-r-sm);
        background: var(--hs-bg-deep);
        border: 1px solid var(--hs-border);
        color: var(--hs-text-1);
        padding: 11px 14px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.25s ease, background 0.25s ease;
    }
    .hs-input::placeholder { color: var(--hs-text-3); }
    .hs-input:focus {
        border-color: rgba(34, 211, 238, 0.3);
        background: rgba(6, 10, 22, 0.95);
    }
    .hs-input-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 34px;
    }
    .hs-input-select option { background: #0f172a; color: #e2e8f0; }

    /* ── Label ── */
    .hs-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--hs-text-2);
        margin-bottom: 6px;
    }

    /* ── Pill / Tag ── */
    .hs-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        padding: 7px 14px;
        font-size: 12px;
        font-weight: 600;
        color: var(--hs-text-2);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .hs-tag:hover, .hs-tag.active {
        border-color: rgba(34, 211, 238, 0.2);
        background: rgba(34, 211, 238, 0.06);
        color: var(--hs-text-1);
    }

    /* ── Section header ── */
    .hs-eyebrow {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--hs-text-3);
        margin-bottom: 6px;
    }
    .hs-heading {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--hs-text-1);
        letter-spacing: -0.01em;
    }

    /* ── Category card ── */
    .hs-cat {
        padding: 20px;
        border-radius: var(--hs-r-sm);
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        cursor: pointer;
        transition: all 0.25s ease;
        text-align: left;
        width: 100%;
    }
    .hs-cat:hover {
        border-color: var(--hs-border-hover);
        background: rgba(15, 23, 42, 0.65);
        transform: translateY(-2px);
    }
    .hs-cat-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--hs-r-xs);
        display: grid;
        place-items: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .hs-cat-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--hs-text-1);
        margin-top: 14px;
    }
    .hs-cat-desc {
        font-size: 12px;
        color: var(--hs-text-3);
        margin-top: 4px;
        line-height: 1.5;
    }

    /* ── Accordion ── */
    .hs-acc {
        border-radius: var(--hs-r-sm);
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        overflow: hidden;
        transition: border-color 0.25s ease;
    }
    .hs-acc.open { border-color: rgba(34, 211, 238, 0.12); }
    .hs-acc-btn {
        width: 100%;
        text-align: left;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: var(--hs-text-1);
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: background 0.2s ease;
    }
    .hs-acc-btn:hover { background: rgba(34, 211, 238, 0.04); }
    .hs-acc-btn i {
        font-size: 10px;
        color: var(--hs-text-3);
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }
    .hs-acc.open .hs-acc-btn i { transform: rotate(180deg); }
    .hs-acc-body {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
    }
    .hs-acc-body-inner {
        padding: 0 16px 14px;
        font-size: 13px;
        line-height: 1.7;
        color: var(--hs-text-2);
    }

    /* ── Video card ── */
    .hs-video {
        border-radius: var(--hs-r-sm);
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        overflow: hidden;
        transition: border-color 0.25s ease, transform 0.25s ease;
        cursor: pointer;
    }
    .hs-video:hover {
        border-color: var(--hs-border-hover);
        transform: translateY(-2px);
    }
    .hs-video-thumb {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
    }
    .hs-video-thumb-bg {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        font-size: 1.6rem;
    }
    .hs-video-play {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background: rgba(0, 0, 0, 0.3);
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .hs-video:hover .hs-video-play { opacity: 1; }
    .hs-video-play-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(34, 211, 238, 0.9);
        color: #020617;
        display: grid;
        place-items: center;
        font-size: 0.8rem;
        box-shadow: 0 8px 24px rgba(34, 211, 238, 0.25);
    }
    .hs-video-dur {
        position: absolute;
        bottom: 8px;
        right: 8px;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.7);
        padding: 2px 7px;
        font-size: 10px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
    }
    .hs-video-info {
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .hs-video-title { font-size: 12px; font-weight: 700; color: var(--hs-text-1); }
    .hs-video-level {
        font-size: 10px;
        font-weight: 600;
        color: var(--hs-text-3);
        background: rgba(148, 163, 184, 0.08);
        padding: 3px 8px;
        border-radius: 6px;
        white-space: nowrap;
    }

    /* ── Tabs ── */
    .hs-tabs {
        display: flex;
        gap: 2px;
        padding: 3px;
        border-radius: var(--hs-r-sm);
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid var(--hs-border);
    }
    .hs-tab {
        flex: 1;
        text-align: center;
        padding: 9px 8px;
        border: none;
        border-radius: var(--hs-r-xs);
        background: transparent;
        font-size: 11px;
        font-weight: 700;
        color: var(--hs-text-3);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .hs-tab:hover { color: var(--hs-text-2); }
    .hs-tab.active {
        background: rgba(34, 211, 238, 0.08);
        color: var(--hs-cyan);
    }
    .hs-tab-panel { display: none; }
    .hs-tab-panel.active { display: block; }

    /* ── Status row ── */
    .hs-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        border-radius: var(--hs-r-xs);
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        transition: border-color 0.2s ease;
    }
    .hs-status:hover { border-color: rgba(148, 163, 184, 0.1); }
    .hs-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .hs-status-label { font-size: 13px; color: var(--hs-text-2); font-weight: 500; }
    .hs-status-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 6px;
    }

    /* ── Contact option ── */
    .hs-contact {
        padding: 14px;
        border-radius: var(--hs-r-sm);
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        transition: border-color 0.2s ease;
    }
    .hs-contact:hover { border-color: rgba(148, 163, 184, 0.12); }
    .hs-contact-title { font-size: 13px; font-weight: 700; color: var(--hs-text-1); }
    .hs-contact-desc { font-size: 12px; color: var(--hs-text-3); margin-top: 3px; line-height: 1.5; }
    .hs-contact-meta { font-size: 10px; color: var(--hs-text-3); margin-top: 6px; }

    /* ── Buttons ── */
    .hs-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--hs-r-sm);
        background: var(--hs-cyan);
        color: #020617;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease;
    }
    .hs-btn-primary:hover { background: #67e8f9; }
    .hs-btn-primary:active { transform: scale(0.97); }
    .hs-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }

    .hs-btn-ghost {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--hs-r-sm);
        background: transparent;
        color: var(--hs-text-1);
        font-size: 13px;
        font-weight: 600;
        border: 1px solid var(--hs-border);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .hs-btn-ghost:hover {
        border-color: rgba(34, 211, 238, 0.2);
        background: rgba(34, 211, 238, 0.04);
    }

    .hs-btn-green {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: var(--hs-r-sm);
        background: var(--hs-emerald);
        color: #020617;
        font-size: 13px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.15s ease;
    }
    .hs-btn-green:hover { background: #6ee7b7; }
    .hs-btn-green:active { transform: scale(0.97); }

    /* ── Loader ── */
    .hs-loader {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        border-radius: var(--hs-r-sm);
        background: rgba(34, 211, 238, 0.06);
        color: var(--hs-cyan);
        font-size: 12px;
        font-weight: 600;
    }
    .hs-loader-dot {
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--hs-cyan);
        animation: hs-bounce 1.2s infinite ease-in-out;
    }
    .hs-loader-dot:nth-child(2) { animation-delay: 0.15s; }
    .hs-loader-dot:nth-child(3) { animation-delay: 0.3s; }
    @keyframes hs-bounce {
        0%, 80%, 100% { opacity: 0.3; transform: scale(0.8); }
        40% { opacity: 1; transform: scale(1); }
    }

    /* ── Toast ── */
    .hs-toast {
        padding: 12px 18px;
        border-radius: var(--hs-r-sm);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.3);
        max-width: 340px;
        backdrop-filter: blur(16px);
    }
    .hs-toast.show { transform: translateX(0); }
    .hs-toast-success {
        background: rgba(16, 60, 42, 0.92);
        border: 1px solid rgba(52, 211, 153, 0.15);
        color: #a7f3d0;
    }
    .hs-toast-error {
        background: rgba(70, 14, 14, 0.92);
        border: 1px solid rgba(248, 113, 113, 0.15);
        color: #fecaca;
    }
    .hs-toast-info {
        background: rgba(12, 36, 64, 0.92);
        border: 1px solid rgba(34, 211, 238, 0.15);
        color: #bae6fd;
    }

    /* ── Search result ── */
    .hs-result {
        padding: 16px;
        border-radius: var(--hs-r-sm);
        border: 1px solid var(--hs-border);
        background: var(--hs-surface);
        transition: border-color 0.2s ease;
    }
    .hs-result:hover { border-color: rgba(148, 163, 184, 0.12); }
    .hs-result-title { font-size: 13px; font-weight: 700; color: var(--hs-text-1); }
    .hs-result-body { font-size: 12px; color: var(--hs-text-3); margin-top: 4px; line-height: 1.6; }
    .hs-result-cat {
        display: inline-block;
        margin-top: 8px;
        font-size: 10px;
        font-weight: 600;
        color: var(--hs-text-3);
        background: rgba(148, 163, 184, 0.06);
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* ── File input ── */
    .hs-file {
        font-size: 12px;
        color: var(--hs-text-3);
        background: transparent;
        border: 1px dashed var(--hs-border);
        border-radius: var(--hs-r-sm);
        padding: 16px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s ease, background 0.2s ease;
        width: 100%;
    }
    .hs-file:hover {
        border-color: rgba(34, 211, 238, 0.2);
        background: rgba(34, 211, 238, 0.02);
    }

    /* ── Feedback ── */
    .hs-fb-btn {
        padding: 8px 16px;
        border-radius: var(--hs-r-xs);
        border: 1px solid var(--hs-border);
        background: transparent;
        font-size: 13px;
        font-weight: 600;
        color: var(--hs-text-2);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .hs-fb-btn:hover {
        border-color: rgba(148, 163, 184, 0.15);
        color: var(--hs-text-1);
        background: rgba(148, 163, 184, 0.04);
    }
    .hs-fb-btn.selected {
        border-color: rgba(34, 211, 238, 0.2);
        background: rgba(34, 211, 238, 0.06);
        color: var(--hs-cyan);
    }

    /* ── Scrollbar ── */
    .hs-scroll::-webkit-scrollbar { width: 3px; }
    .hs-scroll::-webkit-scrollbar-track { background: transparent; }
    .hs-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.12); border-radius: 999px; }

    /* ── Responsive ── */
    @media (max-width: 1024px) {
        .hs-main-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

    {{-- ━━ Hero ━━ --}}
    <div class="hs-card px-6 py-5 lg:px-8 lg:py-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="max-w-xl">
                <div class="hs-eyebrow">Support Center</div>
                <h1 class="text-2xl lg:text-3xl font-extrabold text-c-60 tracking-tight mt-1">Help & Support</h1>
                <p class="text-sm text-c-40 mt-2 leading-relaxed">Search our knowledge base, browse guides, or reach out directly — we're here to help.</p>
            </div>
            <div class="relative w-full lg:w-80 flex-shrink-0">
                <input id="helpSearchInput" type="search" class="hs-input !pl-11 !py-3" placeholder="Search articles, guides, fixes…" aria-label="Search help">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-xs" style="color:var(--hs-text-3)"></i>
            </div>
        </div>
    </div>

    {{-- ━━ Main grid ━━ --}}
    <div class="grid gap-4 hs-main-grid" style="grid-template-columns: 1fr 380px;">

        {{-- ━━ Left column ━━ --}}
        <div class="space-y-4 min-w-0">

            {{-- Quick Tags + Results --}}
            <div class="hs-card p-5">
                <div class="flex flex-wrap gap-2 mb-4" id="helpQuickTags">
                    @foreach($quickTags as $tag)
                    <button type="button" class="hs-tag" data-tag="{{ $tag }}">{{ $tag }}</button>
                    @endforeach
                </div>
                <div id="kbResults" class="space-y-3 max-h-[320px] overflow-y-auto hs-scroll pr-1"></div>
            </div>

            {{-- Categories --}}
            <div class="hs-card p-5">
                <div class="hs-eyebrow">Categories</div>
                <div class="hs-heading">Browse by topic</div>
                <div class="grid gap-3 sm:grid-cols-2 mt-5">
                    @php
                        $cats = [
                            ['icon'=>'fa-robot','color'=>'#22d3ee','bg'=>'rgba(34,211,238,0.08)','title'=>'AI Chat','desc'=>'Tutor usage, prompts, conversations'],
                            ['icon'=>'fa-file-lines','color'=>'#818cf8','bg'=>'rgba(129,140,248,0.08)','title'=>'Documents','desc'=>'Upload, formats, storage limits'],
                            ['icon'=>'fa-clone','color'=>'#34d399','bg'=>'rgba(52,211,153,0.08)','title'=>'Flashcards','desc'=>'Create, study, share decks'],
                            ['icon'=>'fa-square-check','color'=>'#fbbf24','bg'=>'rgba(251,191,36,0.08)','title'=>'Quizzes','desc'=>'Generate, practice, difficulty'],
                            ['icon'=>'fa-user-gear','color'=>'#fb7185','bg'=>'rgba(251,113,133,0.08)','title'=>'Account','desc'=>'Sign-in, password, profile'],
                            ['icon'=>'fa-chart-line','color'=>'#22d3ee','bg'=>'rgba(34,211,238,0.08)','title'=>'Analytics','desc'=>'Progress, insights, statistics'],
                            ['icon'=>'fa-calendar-days','color'=>'#818cf8','bg'=>'rgba(129,140,248,0.08)','title'=>'Study Planner','desc'=>'Schedules, reminders, goals'],
                            ['icon'=>'fa-bug','color'=>'#fb923c','bg'=>'rgba(251,146,60,0.08)','title'=>'Technical Issues','desc'=>'Errors, compatibility, fixes'],
                        ];
                    @endphp
                    @foreach($cats as $cat)
                    <button type="button" class="hs-cat group" data-tag="{{ $cat['title'] }}">
                        <div class="flex items-center justify-between">
                            <div class="hs-cat-icon" style="background:{{ $cat['bg'] }}; color:{{ $cat['color'] }};">
                                <i class="fa-solid {{ $cat['icon'] }}"></i>
                            </div>
                            <i class="fa-solid fa-arrow-right text-[10px] transition group-hover:translate-x-0.5" style="color:var(--hs-text-3)"></i>
                        </div>
                        <div class="hs-cat-title">{{ $cat['title'] }}</div>
                        <div class="hs-cat-desc">{{ $cat['desc'] }}</div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- FAQ --}}
            <div class="hs-card p-5">
                <div class="hs-eyebrow">FAQ</div>
                <div class="hs-heading">Frequently asked questions</div>
                <div class="space-y-2 mt-5" id="faqAccordion">
                    @foreach($faqItems as $index => $item)
                    <div class="hs-acc" data-index="{{ $index }}">
                        <button type="button" class="hs-acc-btn" aria-expanded="false">
                            <span>{{ $item['question'] }}</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="hs-acc-body">
                            <div class="hs-acc-body-inner"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ━━ Right column ━━ --}}
        <div class="space-y-4">

            {{-- Tabbed Forms --}}
            <div class="hs-card p-5">
                <div class="hs-tabs mb-5">
                    <button type="button" class="hs-tab active" data-tab="support">Support</button>
                    <button type="button" class="hs-tab" data-tab="bug">Bug</button>
                    <button type="button" class="hs-tab" data-tab="feature">Idea</button>
                </div>

                {{-- Support Tab --}}
                <div class="hs-tab-panel active" id="tab-support">
                    <form id="supportForm" class="space-y-3" enctype="multipart/form-data">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="hs-label">Name</label>
                                <input type="text" name="name" class="hs-input" value="{{ Auth::user()->name }}" required>
                            </div>
                            <div>
                                <label class="hs-label">Email</label>
                                <input type="email" name="email" class="hs-input" value="{{ Auth::user()->email }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="hs-label">Subject</label>
                            <input type="text" name="subject" class="hs-input" placeholder="Brief description" required>
                        </div>
                        <div>
                            <label class="hs-label">Category</label>
                            <select name="category" class="hs-input hs-input-select" required>
                                <option>Technical Issue</option>
                                <option>Bug Report</option>
                                <option>Feature Request</option>
                                <option>Account</option>
                                <option>Billing</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="hs-label">Message</label>
                            <textarea name="message" rows="4" class="hs-input resize-none" placeholder="What do you need help with?" required></textarea>
                        </div>
                        <label class="hs-file">
                            <input type="file" name="attachment" class="hidden" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.txt">
                            <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Attach file <span class="opacity-50 ml-1">(max 10MB)</span>
                        </label>
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="hs-btn-primary flex-1" id="supportSubmitBtn">
                                <i class="fa-solid fa-paper-plane text-xs"></i> Submit Ticket
                            </button>
                            <span id="supportLoader" class="hs-loader hidden">
                                <span class="hs-loader-dot"></span>
                                <span class="hs-loader-dot"></span>
                                <span class="hs-loader-dot"></span>
                            </span>
                        </div>
                    </form>
                </div>

                {{-- Bug Tab --}}
                <div class="hs-tab-panel" id="tab-bug">
                    <form id="bugForm" class="space-y-3" enctype="multipart/form-data">
                        <div>
                            <label class="hs-label">Page</label>
                            <input type="text" name="page" class="hs-input" placeholder="E.g. Analytics Dashboard" required>
                        </div>
                        <div>
                            <label class="hs-label">What happened?</label>
                            <textarea name="description" rows="3" class="hs-input resize-none" required></textarea>
                        </div>
                        <div>
                            <label class="hs-label">Steps to reproduce</label>
                            <textarea name="reproduction_steps" rows="3" class="hs-input resize-none" placeholder="1. Go to…&#10;2. Click on…&#10;3. Then…" required></textarea>
                        </div>
                        <div>
                            <label class="hs-label">Severity</label>
                            <select name="severity" class="hs-input hs-input-select" required>
                                <option>Low</option>
                                <option>Medium</option>
                                <option>High</option>
                                <option>Critical</option>
                            </select>
                        </div>
                        <label class="hs-file">
                            <input type="file" name="screenshot" class="hidden" accept="image/png,image/jpeg">
                            <i class="fa-solid fa-image mr-1"></i> Attach screenshot
                        </label>
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="hs-btn-ghost flex-1">
                                <i class="fa-solid fa-bug text-xs"></i> Report Bug
                            </button>
                            <span id="bugLoader" class="hs-loader hidden">
                                <span class="hs-loader-dot"></span>
                                <span class="hs-loader-dot"></span>
                                <span class="hs-loader-dot"></span>
                            </span>
                        </div>
                    </form>
                </div>

                {{-- Feature Tab --}}
                <div class="hs-tab-panel" id="tab-feature">
                    <form id="featureForm" class="space-y-3">
                        <div>
                            <label class="hs-label">Title</label>
                            <input type="text" name="title" class="hs-input" placeholder="What should we build?" required>
                        </div>
                        <div>
                            <label class="hs-label">Description</label>
                            <textarea name="description" rows="3" class="hs-input resize-none" required></textarea>
                        </div>
                        <div>
                            <label class="hs-label">Expected benefit</label>
                            <textarea name="expected_benefit" rows="3" class="hs-input resize-none" placeholder="How would this help you?" required></textarea>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="hs-btn-green flex-1">
                                <i class="fa-solid fa-lightbulb text-xs"></i> Submit Idea
                            </button>
                            <span id="featureLoader" class="hs-loader hidden" style="color:var(--hs-emerald); background:rgba(52,211,153,0.06);">
                                <span class="hs-loader-dot" style="background:var(--hs-emerald)"></span>
                                <span class="hs-loader-dot" style="background:var(--hs-emerald)"></span>
                                <span class="hs-loader-dot" style="background:var(--hs-emerald)"></span>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Contact Options --}}
            <div class="hs-card p-5">
                <div class="hs-eyebrow">Reach us</div>
                <div class="hs-heading">Contact options</div>
                <div class="space-y-2 mt-4">
                    <div class="hs-contact">
                        <div class="hs-contact-title"><i class="fa-solid fa-envelope mr-2 text-xs" style="color:var(--hs-cyan)"></i>Email Support</div>
                        <div class="hs-contact-desc">dasinagee2@gmail.com</div>
                        <div class="hs-contact-meta">Avg. response: 24 hours</div>
                    </div>
                    <div class="hs-contact">
                        <div class="hs-contact-title"><i class="fa-solid fa-comments mr-2 text-xs" style="color:var(--hs-indigo)"></i>Live Chat</div>
                        <div class="hs-contact-desc">Mon – Fri, 8 AM – 6 PM</div>
                    </div>
                    <div class="hs-contact">
                        <div class="hs-contact-title"><i class="fa-solid fa-users mr-2 text-xs" style="color:var(--hs-emerald)"></i>Community Forum</div>
                        <div class="hs-contact-desc">Ask, share, and learn from peers</div>
                    </div>
                    <div class="hs-contact">
                        <div class="hs-contact-title"><i class="fa-solid fa-book mr-2 text-xs" style="color:var(--hs-amber)"></i>Documentation</div>
                        <div class="hs-contact-desc">Guides, API docs, and tutorials</div>
                    </div>
                </div>
            </div>

            {{-- System Status --}}
            <div class="hs-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div class="hs-eyebrow">Uptime</div>
                        <div class="hs-heading">System status</div>
                    </div>
                    <button id="refreshStatusBtn" class="hs-btn-ghost !py-2 !px-3 !text-xs" title="Refresh">
                        <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                    </button>
                </div>
                <div class="space-y-2" id="statusGrid">
                    <div class="hs-status">
                        <div class="flex items-center gap-3">
                            <span class="hs-status-dot" style="background:#34d399; box-shadow:0 0 8px rgba(52,211,153,0.4);"></span>
                            <span class="hs-status-label">AI Services</span>
                        </div>
                        <span class="hs-status-badge" style="color:#6ee7b7; background:rgba(52,211,153,0.08);">Online</span>
                    </div>
                    <div class="hs-status">
                        <div class="flex items-center gap-3">
                            <span class="hs-status-dot" style="background:#38bdf8; box-shadow:0 0 8px rgba(56,189,248,0.4);"></span>
                            <span class="hs-status-label">Authentication</span>
                        </div>
                        <span class="hs-status-badge" style="color:#7dd3fc; background:rgba(56,189,248,0.08);">Operational</span>
                    </div>
                    <div class="hs-status">
                        <div class="flex items-center gap-3">
                            <span class="hs-status-dot" style="background:#38bdf8; box-shadow:0 0 8px rgba(56,189,248,0.4);"></span>
                            <span class="hs-status-label">Email Services</span>
                        </div>
                        <span class="hs-status-badge" style="color:#7dd3fc; background:rgba(56,189,248,0.08);">Operational</span>
                    </div>
                    <div class="hs-status">
                        <div class="flex items-center gap-3">
                            <span class="hs-status-dot" style="background:#14b8a6; box-shadow:0 0 8px rgba(20,184,166,0.4);"></span>
                            <span class="hs-status-label">Database</span>
                        </div>
                        <span class="hs-status-badge" style="color:#5eead4; background:rgba(20,184,166,0.08);">Healthy</span>
                    </div>
                </div>
                <div class="mt-4 text-[10px] font-medium" style="color:var(--hs-text-3);">
                    Last checked: <span id="statusUpdatedAt">Just now</span>
                </div>
            </div>

            {{-- Feedback --}}
            <div class="hs-card p-5">
                <div class="hs-eyebrow">Feedback</div>
                <div class="hs-heading">Was this page helpful?</div>
                <div class="flex gap-2 mt-4">
                    <button id="feedbackYes" type="button" class="hs-fb-btn flex-1">
                        <i class="fa-solid fa-thumbs-up mr-1.5 text-xs"></i> Yes
                    </button>
                    <button id="feedbackNo" type="button" class="hs-fb-btn flex-1">
                        <i class="fa-solid fa-thumbs-down mr-1.5 text-xs"></i> No
                    </button>
                </div>
                <div id="feedbackCommentRow" class="mt-3 hidden">
                    <textarea id="feedbackComment" rows="2" class="hs-input resize-none !text-xs" placeholder="How can we improve?"></textarea>
                    <button id="feedbackSubmitBtn" type="button" class="hs-btn-primary !py-2 !px-4 !text-xs mt-2">Send Feedback</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>
@endsection

@section('scripts')
<script>
(() => {
    const $ = id => document.getElementById(id);
    const $$ = sel => document.querySelectorAll(sel);

    /* ── Card mouse glow ── */
    document.addEventListener('mousemove', e => {
        $$('.hs-card').forEach(c => {
            const r = c.getBoundingClientRect();
            c.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
            c.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    });

    /* ── Toast ── */
    function toast(msg, type = 'info') {
        const el = document.createElement('div');
        el.className = `hs-toast hs-toast-${type}`;
        const icons = { success: 'circle-check', error: 'circle-xmark', info: 'circle-info' };
        el.innerHTML = `<i class="fa-solid fa-${icons[type] || 'circle-info'} text-sm"></i><span>${msg}</span>`;
        $('toasts').appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 3800);
    }

    /* ── Search ── */
    const searchInput = $('helpSearchInput');
    const resultsEl = $('kbResults');
    let searchTimeout;

    function renderResults(results) {
        if (!results.length) {
            resultsEl.innerHTML = `<div class="text-center py-8"><div class="text-2xl mb-2 opacity-40">🔍</div><p class="text-xs" style="color:var(--hs-text-3)">No articles found. Try a different term.</p></div>`;
            return;
        }
        resultsEl.innerHTML = results.map(r => `
            <div class="hs-result">
                <div class="hs-result-title">${r.title}</div>
                <div class="hs-result-body">${r.content}</div>
                <span class="hs-result-cat">${r.category}</span>
            </div>`).join('');
    }

    async function doSearch(q) {
        if (!q) { renderResults([]); return; }
        try {
            const res = await fetch(`{{ route('help.search') }}?query=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) return renderResults([]);
            const json = await res.json();
            renderResults(json.results || []);
        } catch { renderResults([]); }
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => doSearch(searchInput.value.trim()), 250);
    });

    /* ── Tags & Category clicks ── */
    $$('[data-tag]').forEach(el => {
        el.addEventListener('click', () => {
            const q = el.dataset.tag;
            searchInput.value = q;
            $$('.hs-tag').forEach(t => t.classList.remove('active'));
            if (el.classList.contains('hs-tag')) el.classList.add('active');
            doSearch(q);
            resultsEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    /* ── FAQ Accordion ── */
    const faqData = @json($faqItems);
    $$('#faqAccordion .hs-acc').forEach((item, i) => {
        const btn = item.querySelector('.hs-acc-btn');
        const body = item.querySelector('.hs-acc-body');
        const inner = item.querySelector('.hs-acc-body-inner');
        btn.addEventListener('click', () => {
            const opening = !item.classList.contains('open');
            $$('#faqAccordion .hs-acc.open').forEach(other => {
                if (other !== item) {
                    other.classList.remove('open');
                    other.querySelector('.hs-acc-body').style.maxHeight = '0';
                    other.querySelector('.hs-acc-body').style.opacity = '0';
                }
            });
            if (opening) {
                item.classList.add('open');
                inner.innerHTML = `<p>${faqData[i].answer}</p>`;
                body.style.maxHeight = body.scrollHeight + 'px';
                body.style.opacity = '1';
                btn.setAttribute('aria-expanded', 'true');
            } else {
                item.classList.remove('open');
                body.style.maxHeight = '0';
                body.style.opacity = '0';
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    });

    /* ── Tabs ── */
    $$('.hs-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            $$('.hs-tab').forEach(t => t.classList.remove('active'));
            $$('.hs-tab-panel').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            $('tab-' + tab.dataset.tab)?.classList.add('active');
        });
    });

    /* ── File input label click-through ── */
    $$('.hs-file').forEach(label => {
        const input = label.querySelector('input[type="file"]');
        if (input) {
            label.addEventListener('click', () => input.click());
            input.addEventListener('change', () => {
                const name = input.files[0]?.name;
                if (name) {
                    const icon = label.querySelector('i');
                    const origHTML = label.innerHTML;
                    label.innerHTML = `<i class="fa-solid fa-check mr-1" style="color:var(--hs-emerald)"></i> ${name}`;
                    setTimeout(() => { label.innerHTML = origHTML; }, 3000);
                }
            });
        }
    });

    /* ── Form submit ── */
    async function submitForm(form, url, loaderId, btn, clear = false) {
        const loader = $(loaderId);
        const fd = new FormData(form);
        btn.disabled = true;
        loader?.classList.remove('hidden');
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
                credentials: 'same-origin',
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Submission failed');
            toast(json.message, 'success');
            if (clear) form.reset();
            return json;
        } catch (e) {
            toast(e.message || 'Something went wrong', 'error');
        } finally {
            btn.disabled = false;
            loader?.classList.add('hidden');
        }
    }

    $('supportForm')?.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = $('supportSubmitBtn');
        const data = await submitForm($('supportForm'), '{{ route('help.ticket.submit') }}', 'supportLoader', btn, true);
        if (data?.reference_id) toast(`Ticket: ${data.reference_id}`, 'success');
    });

    $('bugForm')?.addEventListener('submit', async e => {
        e.preventDefault();
        await submitForm($('bugForm'), '{{ route('help.bug.submit') }}', 'bugLoader', $('bugForm').querySelector('button[type="submit"]'), true);
    });

    $('featureForm')?.addEventListener('submit', async e => {
        e.preventDefault();
        await submitForm($('featureForm'), '{{ route('help.feature.submit') }}', 'featureLoader', $('featureForm').querySelector('button[type="submit"]'), true);
    });

    /* ── Feedback ── */
    let fbState = null;
    $('feedbackYes')?.addEventListener('click', () => {
        fbState = 'yes';
        $('feedbackYes').classList.add('selected');
        $('feedbackNo').classList.remove('selected');
        $('feedbackCommentRow').classList.add('hidden');
        $('feedbackComment').value = '';
        $('feedbackSubmitBtn').click();
    });
    $('feedbackNo')?.addEventListener('click', () => {
        fbState = 'no';
        $('feedbackNo').classList.add('selected');
        $('feedbackYes').classList.remove('selected');
        $('feedbackCommentRow').classList.remove('hidden');
    });
    $('feedbackSubmitBtn')?.addEventListener('click', async () => {
        if (!fbState) return toast('Please choose Yes or No first.', 'error');
        const fd = new FormData();
        fd.append('rating', fbState);
        fd.append('comment', $('feedbackComment').value.trim());
        try {
            const res = await fetch('{{ route('help.feedback.submit') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
                credentials: 'same-origin',
            });
            const json = await res.json();
            if (res.ok) {
                toast(json.message, 'success');
                $('feedbackCommentRow').classList.add('hidden');
                $('feedbackYes').classList.remove('selected');
                $('feedbackNo').classList.remove('selected');
                fbState = null;
                $('feedbackComment').value = '';
            } else toast(json.message || 'Failed', 'error');
        } catch { toast('Failed to submit feedback', 'error'); }
    });

    /* ── Status ── */
    async function refreshStatus() {
        try {
            const res = await fetch('{{ route('help.status') }}', { headers: { Accept: 'application/json' } });
            const json = await res.json();
            $('statusUpdatedAt').textContent = json.updated_at || new Date().toLocaleTimeString();
        } catch { /* silent */ }
    }
    $('refreshStatusBtn')?.addEventListener('click', refreshStatus);
    setInterval(refreshStatus, 30000);

    /* ── Initial load ── */
    doSearch('');
})();
</script>
@endsection