<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin | SHEELEARN')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        :root {
            --ad-bg: rgba(10, 15, 30, 0.72);
            --ad-bg-deep: rgba(6, 10, 22, 0.88);
            --ad-sidebar: rgba(4, 8, 18, 0.92);
            --ad-border: rgba(148, 163, 184, 0.07);
            --ad-border-hover: rgba(34, 211, 238, 0.14);
            --ad-surface: rgba(15, 23, 42, 0.45);
            --ad-cyan: #22d3ee;
            --ad-indigo: #818cf8;
            --ad-emerald: #34d399;
            --ad-amber: #fbbf24;
            --ad-rose: #fb7185;
            --ad-orange: #fb923c;
            --ad-t1: #f1f5f9;
            --ad-t2: rgba(203, 213, 225, 0.68);
            --ad-t3: rgba(148, 163, 184, 0.4);
            --ad-r: 16px;
            --ad-r-sm: 10px;
            --ad-r-xs: 6px;
        }
        html, body { height: 100%; overflow: hidden; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #020617; color: var(--ad-t1); margin: 0; }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at 10% 10%, rgba(34,211,238,0.06) 0%, transparent 50%),
                        radial-gradient(ellipse at 90% 90%, rgba(129,140,248,0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* Sidebar */
        .ad-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 16rem;
            display: none;
            flex-direction: column;
            background: var(--ad-sidebar);
            border-right: 1px solid var(--ad-border);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 9999;
            overflow-y: auto;
            overflow-x: hidden;
            pointer-events: auto;
        }
        .ad-sidebar.open {
            display: flex !important;
            transform: translateX(0) !important;
        }
        @media (min-width: 1024px) {
            .ad-sidebar { display: flex !important; transform: translateX(0) !important; z-index: auto; }
        }
        .ad-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: var(--ad-r-sm);
            font-size: 13px;
            font-weight: 500;
            color: var(--ad-t3);
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            position: relative;
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            pointer-events: auto;
            min-height: 44px;
        }
        .ad-nav-item:hover {
            background: rgba(34, 211, 238, 0.05);
            color: var(--ad-t2);
        }
        .ad-nav-item:active {
            background: rgba(34, 211, 238, 0.08);
        }
        .ad-nav-item.active {
            background: rgba(34, 211, 238, 0.08);
            color: var(--ad-t1);
            border-color: rgba(34, 211, 238, 0.1);
        }
        .ad-nav-item.active::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            border-radius: 0 3px 3px 0;
            background: var(--ad-cyan);
        }
        .ad-nav-item i { width: 18px; text-align: center; font-size: 13px; }

        /* Header */
        .ad-header {
            background: rgba(4, 8, 18, 0.7);
            border-bottom: 1px solid var(--ad-border);
            backdrop-filter: blur(32px);
            -webkit-backdrop-filter: blur(32px);
        }

        /* Cards */
        .ad-card {
            border-radius: var(--ad-r);
            border: 1px solid var(--ad-border);
            background: var(--ad-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .ad-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            background: radial-gradient(400px circle at var(--mx, 50%) var(--my, 50%),
                rgba(34, 211, 238, 0.03), transparent 40%);
        }
        .ad-card:hover::before { opacity: 1; }
        .ad-card-lift:hover {
            transform: translateY(-2px);
            border-color: var(--ad-border-hover);
            box-shadow: 0 12px 36px -8px rgba(34, 211, 238, 0.06);
        }

        /* Stat card */
        .ad-stat { padding: 20px; transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease; }
        .ad-stat-icon {
            width: 40px; height: 40px; border-radius: var(--ad-r-sm);
            display: grid; place-items: center; font-size: 0.9rem; flex-shrink: 0;
        }
        .ad-stat-label { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ad-t3); }
        .ad-stat-value { font-size: 1.7rem; font-weight: 800; line-height: 1; color: var(--ad-t1); letter-spacing: -0.02em; margin-top: 8px; }
        .ad-stat-trend { font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 6px; margin-top: 10px; }
        .ad-stat-trend.up { color: var(--ad-emerald); background: rgba(52,211,153,0.08); }
        .ad-stat-trend.neutral { color: var(--ad-t3); background: rgba(148,163,184,0.06); }

        /* Section header */
        .ad-eyebrow { font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ad-t3); }
        .ad-heading { font-size: 1rem; font-weight: 700; color: var(--ad-t1); letter-spacing: -0.01em; margin-top: 4px; }

        /* Table */
        .ad-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
        .ad-table thead th {
            padding: 10px 16px; text-align: left; font-weight: 600; font-size: 11px;
            letter-spacing: 0.06em; text-transform: uppercase; color: var(--ad-t3);
            background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid var(--ad-border);
        }
        .ad-table thead th:first-child { border-radius: var(--ad-r-sm) 0 0 0; }
        .ad-table thead th:last-child { border-radius: 0 var(--ad-r-sm) 0 0; }
        .ad-table tbody td {
            padding: 12px 16px; color: var(--ad-t2); border-bottom: 1px solid var(--ad-border);
            background: transparent;
        }
        .ad-table tbody tr { transition: background 0.15s ease; }
        .ad-table tbody tr:hover td { background: rgba(34, 211, 238, 0.02); }
        .ad-table tbody tr:last-child td { border-bottom: none; }

        /* Badge */
        .ad-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; }
        .ad-badge-green { color: #6ee7b7; background: rgba(52,211,153,0.08); }
        .ad-badge-amber { color: #fcd34d; background: rgba(251,191,36,0.08); }
        .ad-badge-rose { color: #fda4af; background: rgba(251,113,133,0.08); }
        .ad-badge-cyan { color: #67e8f9; background: rgba(34,211,238,0.08); }
        .ad-badge-indigo { color: #a5b4fc; background: rgba(129,140,248,0.08); }

        /* Inputs */
        .ad-input {
            width: 100%; border-radius: var(--ad-r-sm); background: var(--ad-bg-deep);
            border: 1px solid var(--ad-border); color: var(--ad-t1); padding: 10px 14px;
            font-size: 13px; outline: none; transition: border-color 0.25s ease;
        }
        .ad-input::placeholder { color: var(--ad-t3); }
        .ad-input:focus { border-color: rgba(34, 211, 238, 0.3); }
        .ad-input-select {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px;
        }
        .ad-input-select option { background: #0f172a; color: #e2e8f0; }
        .ad-label { display: block; font-size: 12px; font-weight: 600; color: var(--ad-t2); margin-bottom: 5px; }

        /* Buttons */
        .ad-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            padding: 9px 18px; border-radius: var(--ad-r-sm); font-size: 13px; font-weight: 600;
            border: none; cursor: pointer; transition: all 0.2s ease; text-decoration: none;
        }
        .ad-btn:active { transform: scale(0.97); }
        .ad-btn-primary { background: var(--ad-cyan); color: #020617; }
        .ad-btn-primary:hover { background: #67e8f9; }
        .ad-btn-ghost { background: transparent; color: var(--ad-t2); border: 1px solid var(--ad-border); }
        .ad-btn-ghost:hover { border-color: rgba(34,211,238,0.2); color: var(--ad-t1); background: rgba(34,211,238,0.03); }
        .ad-btn-danger { background: rgba(251,113,133,0.1); color: var(--ad-rose); border: 1px solid rgba(251,113,133,0.15); }
        .ad-btn-danger:hover { background: rgba(251,113,133,0.18); }
        .ad-btn-sm { padding: 6px 12px; font-size: 12px; }
        .ad-btn:disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }

        /* Tabs */
        .ad-tabs { display: flex; gap: 2px; padding: 3px; border-radius: var(--ad-r-sm); background: rgba(15,23,42,0.5); border: 1px solid var(--ad-border); }
        .ad-tab { flex: 1; text-align: center; padding: 8px; border: none; border-radius: var(--ad-r-xs); background: transparent; font-size: 12px; font-weight: 600; color: var(--ad-t3); cursor: pointer; transition: all 0.2s ease; white-space: nowrap; }
        .ad-tab:hover { color: var(--ad-t2); }
        .ad-tab.active { background: rgba(34,211,238,0.08); color: var(--ad-cyan); }
        .ad-tab-panel { display: none; }
        .ad-tab-panel.active { display: block; }

        /* Status dot */
        .ad-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

        /* Toast */
        .ad-toast {
            padding: 12px 18px; border-radius: var(--ad-r-sm); font-size: 13px; font-weight: 600;
            display: flex; align-items: center; gap: 10px;
            transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.22, 1, 0.36, 1);
            box-shadow: 0 16px 40px rgba(0,0,0,0.3); max-width: 360px; backdrop-filter: blur(16px);
        }
        .ad-toast.show { transform: translateX(0); }
        .ad-toast-success { background: rgba(16,60,42,0.92); border: 1px solid rgba(52,211,153,0.15); color: #a7f3d0; }
        .ad-toast-error { background: rgba(70,14,14,0.92); border: 1px solid rgba(248,113,113,0.15); color: #fecaca; }
        .ad-toast-info { background: rgba(12,36,64,0.92); border: 1px solid rgba(34,211,238,0.15); color: #bae6fd; }

        /* Modal */
        .ad-modal-overlay {
            position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);
            opacity: 0; pointer-events: none; transition: opacity 0.25s ease; padding: 16px;
        }
        .ad-modal-overlay.open { opacity: 1; pointer-events: auto; }
        .ad-modal {
            width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
            border-radius: var(--ad-r); border: 1px solid var(--ad-border); background: var(--ad-bg-deep);
            transform: scale(0.95) translateY(10px); transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .ad-modal-overlay.open .ad-modal { transform: scale(1) translateY(0); }

        /* Scrollbar */
        .ad-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .ad-scroll::-webkit-scrollbar-track { background: transparent; }
        .ad-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.1); border-radius: 999px; }

        /* Quick action */
        .ad-action {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 14px; border-radius: var(--ad-r-sm); border: 1px solid var(--ad-border);
            background: var(--ad-surface); cursor: pointer; transition: all 0.2s ease;
            text-decoration: none; color: var(--ad-t2); font-size: 13px; font-weight: 500;
        }
        .ad-action:hover { border-color: var(--ad-border-hover); color: var(--ad-t1); background: rgba(15,23,42,0.65); transform: translateX(2px); }
        .ad-action i.arrow { font-size: 10px; color: var(--ad-t3); transition: transform 0.2s ease; }
        .ad-action:hover i.arrow { transform: translateX(3px); color: var(--ad-cyan); }

        /* Tile */
        .ad-tile { padding: 14px 16px; border-radius: var(--ad-r-sm); border: 1px solid var(--ad-border); background: var(--ad-surface); transition: border-color 0.2s ease; }
        .ad-tile:hover { border-color: rgba(148,163,184,0.12); }

        /* Mobile overlay */
        .ad-mobile-overlay {
            position: fixed; top: 0; bottom: 0; left: 0; right: 0; 
            background: transparent; opacity: 0; pointer-events: none; 
            transition: opacity 0.3s ease; z-index: 9998;
        }
        .ad-mobile-overlay.open { opacity: 1; }

        /* Search highlight */
        .ad-search-wrap { position: relative; }
        .ad-search-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--ad-t3); }
        .ad-search-wrap input { padding-left: 36px; }

        /* Pagination */
        .ad-page-btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 32px; height: 32px; padding: 0 8px; border-radius: var(--ad-r-xs);
            border: 1px solid var(--ad-border); background: transparent; color: var(--ad-t3);
            font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;
        }
        .ad-page-btn:hover { border-color: rgba(148,163,184,0.15); color: var(--ad-t2); }
        .ad-page-btn.active { background: rgba(34,211,238,0.08); border-color: rgba(34,211,238,0.15); color: var(--ad-cyan); }

        /* Progress */
        .ad-progress-track { height: 6px; border-radius: 999px; background: rgba(148,163,184,0.08); overflow: hidden; }
        .ad-progress-fill { height: 100%; border-radius: 999px; transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1); }

        /* Toggle */
        .ad-toggle { position: relative; width: 40px; height: 22px; border-radius: 999px; background: rgba(148,163,184,0.15); cursor: pointer; transition: background 0.2s ease; border: none; padding: 0; }
        .ad-toggle::after { content: ''; position: absolute; left: 3px; top: 3px; width: 16px; height: 16px; border-radius: 50%; background: #94a3b8; transition: all 0.2s ease; }
        .ad-toggle.on { background: rgba(34,211,238,0.25); }
        .ad-toggle.on::after { left: 21px; background: var(--ad-cyan); }

        @media (max-width: 1023px) {
            .ad-sidebar { transform: translateX(-100%); z-index: 9999 !important; }
            .ad-sidebar.open { transform: translateX(0) !important; }
            .ad-mobile-overlay { z-index: 9998; }
        }
    </style>
</head>
<body>
    <div id="adToasts" class="fixed bottom-5 right-5 z-50 flex flex-col gap-3"></div>
    <div id="adModalOverlay" class="ad-modal-overlay" onclick="if(event.target===this)closeModal()">
        <div class="ad-modal" id="adModal"></div>
    </div>
    <div id="adMobileOverlay" class="ad-mobile-overlay"></div>

    <div class="flex min-h-screen relative" style="z-index:1;">
        {{-- Sidebar --}}
        <aside class="ad-sidebar w-64 flex-col p-4 flex-shrink-0" id="adSidebar">
            <div class="flex items-center gap-3 px-2 mb-8 mt-1">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-600 text-slate-950 text-sm">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[0.25em]" style="color:var(--ad-cyan)">SHEELEARN</p>
                    <p class="text-sm font-bold" style="color:var(--ad-t1)">Admin</p>
                </div>
            </div>
            <nav class="space-y-1 flex-1 overflow-y-auto ad-scroll pr-1" id="adNav">
                @php
                    $menu = [
                        ['label'=>'Dashboard','icon'=>'fa-grid-2','route'=>'admin.dashboard.home','section'=>'main'],
                        ['label'=>'Users','icon'=>'fa-users','route'=>'admin.users','section'=>'main'],
                        ['label'=>'AI Usage','icon'=>'fa-robot','route'=>'admin.ai-usage','section'=>'main'],
                        ['label'=>'Learning Content','icon'=>'fa-book-open','route'=>'admin.learning-content','section'=>'content'],
                        ['label'=>'Flashcards','icon'=>'fa-clone','route'=>'admin.flashcards','section'=>'content'],
                        ['label'=>'Quizzes','icon'=>'fa-circle-question','route'=>'admin.quizzes','section'=>'content'],
                        ['label'=>'Documents','icon'=>'fa-file-lines','route'=>'admin.documents','section'=>'content'],
                        ['label'=>'Analytics','icon'=>'fa-chart-line','route'=>'admin.analytics','section'=>'insights'],
                        ['label'=>'Announcements','icon'=>'fa-bullhorn','route'=>'admin.announcements','section'=>'insights'],
                        ['label'=>'Reports','icon'=>'fa-flag','route'=>'admin.reports','section'=>'insights'],
                        ['label'=>'Settings','icon'=>'fa-gear','route'=>'admin.settings','section'=>'system'],
                        ['label'=>'Activity Logs','icon'=>'fa-list','route'=>'admin.logs','section'=>'system'],
                        ['label'=>'Profile','icon'=>'fa-user','route'=>'admin.profile','section'=>'system'],
                    ];
                @endphp
                @foreach($menu as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" class="ad-nav-item {{ $isActive ? 'active' : '' }}" data-section="{{ $item['section'] }}">
                        <i class="fa-solid {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
                <div class="pt-3 mt-3" style="border-top:1px solid var(--ad-border)">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="ad-nav-item w-full" style="color:var(--ad-rose)">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
            <header class="ad-header px-4 py-3 sm:px-6 lg:px-8 flex-shrink-0">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden ad-btn ad-btn-ghost !p-2 !px-3 relative z-50" onclick="toggleMobile()" aria-label="Menu">
                            <i class="fa-solid fa-bars text-sm"></i>
                        </button>
                        <div>
                            <p class="ad-eyebrow">@yield('page_breadcrumb', 'Overview')</p>
                            <h1 class="text-lg font-bold" style="color:var(--ad-t1)">@yield('page_title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="ad-search-wrap hidden md:block">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input class="ad-input !py-2 !text-xs w-48" placeholder="Search pages…" id="adGlobalSearch" oninput="handleGlobalSearch(this.value)">
                        </div>
                        <button class="ad-btn ad-btn-ghost !p-2 !px-3 relative" id="adNotifBtn" onclick="toggleNotifPanel()">
                            <i class="fa-regular fa-bell text-sm"></i>
                            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full" style="background:var(--ad-rose); border:2px solid #020617;"></span>
                        </button>
                        <div class="flex items-center gap-2.5 rounded-xl px-3 py-1.5" style="border:1px solid var(--ad-border); background:var(--ad-surface);">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold" style="background:linear-gradient(135deg,#22d3ee,#818cf8); color:#020617;">{{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}</div>
                            <div class="hidden sm:block">
                                <p class="text-xs font-semibold" style="color:var(--ad-t1)">{{ auth('admin')->user()->name ?? 'Admin' }}</p>
                                <p class="text-[10px]" style="color:var(--ad-t3)">{{ auth('admin')->user()->email ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 min-h-0 px-4 py-5 sm:px-6 lg:px-8 overflow-y-auto overflow-x-hidden max-h-screen">
                @if(session('success'))
                <div class="mb-4 ad-badge-green ad-badge !py-2.5 !px-4 !rounded-xl" style="background:rgba(52,211,153,0.06); border:1px solid rgba(52,211,153,0.12);">
                    <i class="fa-solid fa-circle-check text-xs"></i> {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mb-4 ad-badge-rose ad-badge !py-2.5 !px-4 !rounded-xl" style="background:rgba(251,113,133,0.06); border:1px solid rgba(251,113,133,0.12);">
                    <i class="fa-solid fa-circle-xmark text-xs"></i> {{ session('error') }}
                </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    /* ── Shared utilities ── */
    const $ = id => document.getElementById(id);
    const $$ = sel => document.querySelectorAll(sel);
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#64748b';

    /* Card glow */
    document.addEventListener('mousemove', e => {
        $$('.ad-card').forEach(c => {
            const r = c.getBoundingClientRect();
            c.style.setProperty('--mx', ((e.clientX - r.left) / r.width * 100) + '%');
            c.style.setProperty('--my', ((e.clientY - r.top) / r.height * 100) + '%');
        });
    });

    /* Toast */
    function toast(msg, type = 'info') {
        const el = document.createElement('div');
        el.className = `ad-toast ad-toast-${type}`;
        const icons = { success: 'circle-check', error: 'circle-xmark', info: 'circle-info' };
        el.innerHTML = `<i class="fa-solid fa-${icons[type]||'circle-info'} text-sm"></i><span>${msg}</span>`;
        $('adToasts').appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => { el.classList.remove('show'); setTimeout(() => el.remove(), 400); }, 3800);
    }

    /* Modal */
    function openModal(html) { $('adModal').innerHTML = html; $('adModalOverlay').classList.add('open'); }
    function closeModal() { $('adModalOverlay').classList.remove('open'); }

    /* Mobile sidebar */
    function toggleMobile() {
        const sidebar = $('adSidebar');
        if (!sidebar) return;
        if (sidebar.classList.contains('open')) {
            closeMobile();
        } else {
            openMobile();
        }
    }

    function openMobile() {
        const sidebar = $('adSidebar');
        const overlay = $('adMobileOverlay');
        if (!sidebar || !overlay) return;
        sidebar.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden'; // Prevent body scroll
    }

    function closeMobile() {
        const sidebar = $('adSidebar');
        const overlay = $('adMobileOverlay');
        if (!sidebar || !overlay) return;
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = ''; // Restore body scroll
    }

    // Close sidebar when clicking on overlay content area or a menu item
    document.addEventListener('DOMContentLoaded', () => {
        const overlay = $('adMobileOverlay');
        const sidebar = $('adSidebar');
        
        // Close on overlay click (outside sidebar)
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay && window.innerWidth < 1024) {
                    closeMobile();
                }
            });
        }
        
        // Close on menu item click
        const sidebarItems = document.querySelectorAll('.ad-nav-item:not(form .ad-nav-item)');
        sidebarItems.forEach(item => {
            item.addEventListener('click', (e) => {
                // Skip if it's a form button (logout)
                if (item.type === 'submit' || item.querySelector('form')) {
                    return;
                }
                // Close sidebar on small screens after clicking a link
                if (window.innerWidth < 1024) {
                    // Use setTimeout to allow navigation to start before closing
                    setTimeout(() => closeMobile(), 100);
                }
            });
        });
    });

    /* Global search */
    function handleGlobalSearch(q) {
        const items = $$('.ad-nav-item[data-section]');
        items.forEach(item => {
            const label = item.querySelector('span')?.textContent.toLowerCase() || '';
            item.style.display = !q || label.includes(q.toLowerCase()) ? '' : 'none';
        });
    }

    /* Notification panel */
    let notifOpen = false;
    function toggleNotifPanel() {
        notifOpen = !notifOpen;
        let panel = $('adNotifPanel');
        if (notifOpen) {
            if (!panel) {
                panel = document.createElement('div');
                panel.id = 'adNotifPanel';
                panel.style.cssText = 'position:fixed;top:56px;right:16px;z-index:45;width:320px;border-radius:var(--ad-r);border:1px solid var(--ad-border);background:var(--ad-bg-deep);box-shadow:0 20px 48px rgba(0,0,0,0.4);';
                panel.innerHTML = `<div style="padding:14px 16px;border-bottom:1px solid var(--ad-border)"><p style="font-size:13px;font-weight:700;color:var(--ad-t1)">Notifications</p></div><div style="padding:12px;text-align:center;font-size:12px;color:var(--ad-t3)">No new notifications</div>`;
                document.body.appendChild(panel);
            }
            panel.style.display = 'block';
        } else if (panel) {
            panel.style.display = 'none';
        }
    }
    document.addEventListener('click', e => {
        if (notifOpen && !$('adNotifBtn').contains(e.target) && !$('adNotifPanel')?.contains(e.target)) {
            notifOpen = false;
            const p = $('adNotifPanel');
            if (p) p.style.display = 'none';
        }
    });

    /* Animate number */
    function animNum(el, target, suffix = '') {
        if (!el) return;
        const start = parseFloat(el.dataset.v || '0') || 0;
        const dur = 600, t0 = performance.now();
        el.dataset.v = target;
        (function step(now) {
            const p = Math.min((now - t0) / dur, 1);
            const e = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(start + (target - start) * e).toLocaleString() + suffix;
            if (p < 1) requestAnimationFrame(step);
        })(t0);
    }

    /* Form submit helper */
    async function adSubmit(form, url, btn, clear = false) {
        const orig = btn.innerHTML;
        const method = (form.getAttribute('method') || 'POST').toUpperCase();
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Processing…';
        try {
            const fd = new FormData(form);
            const res = await fetch(url, {
                method,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: fd, credentials: 'same-origin'
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed');
            toast(json.message || 'Success', 'success');
            if (clear) form.reset();
            if (json.redirect) setTimeout(() => window.location.href = json.redirect, 600);
            return json;
        } catch (e) { toast(e.message || 'Something went wrong', 'error'); }
        finally { btn.disabled = false; btn.innerHTML = orig; }
    }

    /* Table search */
    function adTableSearch(inputId, tableId) {
        const input = $(inputId), table = $(tableId);
        if (!input || !table) return;
        input.addEventListener('input', () => {
            const q = input.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    /* Init table searches on load */
    document.addEventListener('DOMContentLoaded', () => {
        $$('[data-table-search]').forEach(input => {
            adTableSearch(input.id, input.dataset.tableSearch);
        });
    });
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>