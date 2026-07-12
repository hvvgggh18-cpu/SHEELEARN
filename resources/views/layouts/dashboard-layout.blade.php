<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SHEELEARN Dashboard">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SHEELEARN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] },
                    colors: {
                        n: { DEFAULT:'#020617', 1:'#0a0f1e', 2:'#0f172a', 3:'#162033' },
                        c: { DEFAULT:'#e2e8f0', 60:'rgba(226,232,240,0.6)', 40:'rgba(226,232,240,0.4)', 25:'rgba(226,232,240,0.25)', 15:'rgba(226,232,240,0.15)', 10:'rgba(226,232,240,0.10)', 8:'rgba(226,232,240,0.08)', 5:'rgba(226,232,240,0.05)', 3:'rgba(226,232,240,0.03)' },
                        cy: { DEFAULT:'#22d3ee', dim:'rgba(34,211,238,0.15)', glow:'rgba(34,211,238,0.08)' },
                        ac: { DEFAULT:'#818cf8', dim:'rgba(129,140,248,0.15)' },
                        gn: { DEFAULT:'#34d399', dim:'rgba(52,211,153,0.15)' },
                    }
                }
            }
        }
    </script>
    <style>
        *{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
        body{font-family:'Inter',system-ui,sans-serif;background:#020617;color:#e2e8f0;overflow-x:hidden;--bg:#020617;--surface:rgba(15,23,42,0.6);--surface-2:rgba(15,23,42,0.7);--surface-3:rgba(15,23,42,0.8);--border:rgba(226,232,240,0.08);--border-hover:rgba(34,211,238,0.14);--accent:#22d3ee;--accent-hover:#38bdf8;--accent-dim:rgba(34,211,238,0.12);--accent-border:rgba(34,211,238,0.28);--text:#e2e8f0;--text-muted:rgba(226,232,240,0.75);--text-dim:rgba(226,232,240,0.55);}
        ::-webkit-scrollbar{width:4px}
        ::-webkit-scrollbar-track{background:#020617}
        ::-webkit-scrollbar-thumb{background:rgba(34,211,238,0.12);border-radius:99px}
        ::-webkit-scrollbar-thumb:hover{background:rgba(34,211,238,0.25)}

        @property --ba{syntax:'<angle>';initial-value:0deg;inherits:false}
        @property --ba2{syntax:'<angle>';initial-value:180deg;inherits:false}

        .g1{background:rgba(15,23,42,0.6);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(34,211,238,0.06)}
        .g2{background:rgba(15,23,42,0.7);backdrop-filter:blur(28px);-webkit-backdrop-filter:blur(28px);border:1px solid rgba(34,211,238,0.08)}
        .g3{background:rgba(15,23,42,0.8);backdrop-filter:blur(32px);-webkit-backdrop-filter:blur(32px);border:1px solid rgba(34,211,238,0.1)}

        .cyber-text{background:linear-gradient(135deg,#22d3ee 0%,#818cf8 50%,#34d399 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .cyber-text-flow{background:linear-gradient(270deg,#22d3ee,#818cf8,#34d399,#22d3ee);background-size:300% 100%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:flowGrad 5s ease infinite}
        @keyframes flowGrad{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}

        .sidebar{transition:width .35s cubic-bezier(.16,1,.3,1)}
        .sidebar.collapsed{width:72px!important}
        .sidebar.collapsed .sidebar-label{opacity:0;width:0;overflow:hidden;white-space:nowrap}
        .sidebar.collapsed .sidebar-section-title{opacity:0;height:0;margin:0;padding:0;overflow:hidden}
        .sidebar.collapsed .sidebar-logo-text{opacity:0;width:0;overflow:hidden}
        .sidebar-link{transition:all .2s ease;position:relative}
        .sidebar-link::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:2px;height:0;background:#22d3ee;border-radius:0 2px 2px 0;transition:height .2s ease}
        .sidebar-link:hover::before,.sidebar-link.active::before{height:60%}
        .sidebar-link:hover,.sidebar-link.active{background:rgba(34,211,238,0.06);color:#22d3ee}

        body.theme-light{
            background:#f8fafc;
            color:#0f172a;
            --bg:#f8fafc;
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
        body.theme-light ::-webkit-scrollbar-track{background:#f8fafc}
        body.theme-light ::-webkit-scrollbar-thumb{background:rgba(15,23,42,0.25)}
        body.theme-light .g1{background:#ffffff;border-color:rgba(15,23,42,0.12)}
        body.theme-light .g2{background:#f8fafc;border-color:rgba(15,23,42,0.14)}
        body.theme-light .g3{background:#eef2ff;border-color:rgba(15,23,42,0.16)}
        body.theme-light .sidebar{background:#ffffff;border-color:rgba(15,23,42,0.12)}
        body.theme-light .sidebar-link:hover, body.theme-light .sidebar-link.active{background:rgba(59,130,246,0.14);color:#1e3a8a}
        body.theme-light .border-c-5{border-color:rgba(15,23,42,0.18)}
        body.theme-light .text-c-15{color:rgba(15,23,42,0.55)}
        body.theme-light .text-c-25{color:#334155}
        body.theme-light .text-c-40{color:#475569}
        body.theme-light .text-c-60{color:#64748b}
        body.theme-light .text-c{color:#0f172a}
        body.theme-light .text-cy{color:#0c4a6e}
        body.theme-light .bg-c-3{background:rgba(15,23,42,0.06)}
        body.theme-light .bg-c-5{background:rgba(15,23,42,0.1)}
        body.theme-light .bg-cy\/8{background:rgba(34,211,238,0.15)}
        body.theme-light .bg-gn\/5{background:rgba(16,185,129,0.15)}
        body.theme-light .border-cy\/5{border-color:rgba(34,211,238,0.4)}
        body.theme-light .gnav{background:rgba(255,255,255,0.96)}

        @media(max-width:1024px){
            .sidebar{transform:translateX(-100%);transition:transform .35s cubic-bezier(.16,1,.3,1)}
            .sidebar.mobile-open{transform:translateX(0)}
            .sidebar-desktop{display:none!important}
            .main-content{margin-left:0!important}
        }
        .profile-menu{min-width:12rem;}
        .profile-menu.hidden{display:none;}
        .profile-menu.visible{display:block;}
            /* Ensure Light Mode overrides the sidebar and related elements for consistent styling */
            body.theme-light .sidebar {
                background: #ffffff !important;
                border-color: rgba(15,23,42,0.08) !important;
                color: #0f172a !important;
            }
            body.theme-light .sidebar .sidebar-link { color: rgba(15,23,42,0.6); }
            body.theme-light .sidebar .sidebar-link.active,
            body.theme-light .sidebar .sidebar-link:hover { background: rgba(59,130,246,0.06); color: #1e3a8a; }
            body.theme-light .sidebar .sidebar-section-title { color: rgba(15,23,42,0.35); }
            body.theme-light .sidebar .sidebar-user { background: transparent; }
            body.theme-light .sidebar .user-avatar-sidebar { border-color: rgba(15,23,42,0.08); }

            body.theme-light .gnav { background: rgba(255,255,255,0.96); border-color: rgba(15,23,42,0.06); }
            body.theme-light .main-content { background: var(--bg); color: var(--text); }

            /* Panels & cards */
            body.theme-light .g1, body.theme-light .g2, body.theme-light .g3 { background: #ffffff; border-color: rgba(15,23,42,0.06); }
            body.theme-light .panel, body.theme-light .form-panel, body.theme-light .stat-card, body.theme-light .action-card, body.theme-light .deck-item { background: var(--surface); border-color: var(--border); color: var(--text); }

            /* Chat sidebar in light */
            body.theme-light .chat-sidebar { background: rgba(255,255,255,0.96); border-left-color: rgba(15,23,42,0.06); }
            body.theme-light .chat-main { background: var(--bg); }
    </style>
    @yield('styles')
</head>
<body class="font-inter">

<!-- Background -->
<div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
    <div class="absolute -top-32 -right-32 w-[400px] h-[400px] rounded-full blur-[120px]" style="background:radial-gradient(circle,rgba(34,211,238,.06),transparent 60%);"></div>
    <div class="absolute -bottom-48 -left-48 w-[450px] h-[450px] rounded-full blur-[140px]" style="background:radial-gradient(circle,rgba(129,140,248,.04),transparent 60%);"></div>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar sidebar-desktop fixed left-0 top-0 bottom-0 z-40 w-[250px] g3 border-r border-cy/6 flex flex-col overflow-hidden">
    <div class="flex items-center gap-2.5 px-5 py-5 flex-shrink-0">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cy to-cyan-700 flex items-center justify-center shadow-lg shadow-cy/20 flex-shrink-0">
            <i class="fa-solid fa-brain text-n text-xs font-bold"></i>
        </div>
        <span class="sidebar-logo-text text-sm font-bold tracking-tight text-c transition-all duration-300">SHEE<span class="cyber-text">LEARN</span></span>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 space-y-1">
        <p class="sidebar-section-title text-[9px] font-bold text-c-15 uppercase tracking-[.2em] font-mono px-3 pt-3 pb-1.5">Main</p>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium">
            <i class="fa-solid fa-house-chimney text-c-25 text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Dashboard</span>
        </a>
        <a href="{{ route('ai-chat') }}" class="sidebar-link {{ request()->routeIs('ai-chat') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-robot text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">AI Chat</span>
        </a>
        <a href="{{ route('summarizer') }}" class="sidebar-link {{ request()->routeIs('summarizer') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-wand-magic-sparkles text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Summarizer</span>
        </a>
        <a href="{{ route('flashcards') }}" class="sidebar-link {{ request()->routeIs('flashcards') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-layer-group text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Flashcards</span>
            <span class="sidebar-label ml-auto text-[9px] font-bold text-cy bg-cy/8 px-1.5 py-0.5 rounded-md font-mono">{{ Auth::user()->flashcards()->count() }}</span>
        </a>
        <a href="{{ route('quizzes') }}" class="sidebar-link {{ request()->routeIs('quizzes') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-clipboard-question text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Quizzes</span>
        </a>
        <a href="{{ route('documents') }}" class="sidebar-link {{ request()->routeIs('documents') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-file-pdf text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">My Documents</span>
        </a>

        <p class="sidebar-section-title text-[9px] font-bold text-c-15 uppercase tracking-[.2em] font-mono px-3 pt-5 pb-1.5">Tools</p>
        <a href="{{ route('study-planner') }}" class="sidebar-link {{ request()->routeIs('study-planner') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-calendar-check text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Study Planner</span>
        </a>
        <a href="{{ route('analytics') }}" class="sidebar-link {{ request()->routeIs('analytics') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-chart-line text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Analytics</span>
        </a>
        <a href="{{ route('notes') }}" class="sidebar-link {{ request()->routeIs('notes') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-note-sticky text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Notes</span>
        </a>

        <p class="sidebar-section-title text-[9px] font-bold text-c-15 uppercase tracking-[.2em] font-mono px-3 pt-5 pb-1.5">Account</p>
        <a href="{{ route('settings') }}" class="sidebar-link {{ request()->routeIs('settings') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-gear text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Settings</span>
        </a>
        <a href="{{ route('help') }}" class="sidebar-link {{ request()->routeIs('help') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
            <i class="fa-solid fa-circle-question text-sm w-5 text-center flex-shrink-0"></i>
            <span class="sidebar-label">Help & Support</span>
        </a>
        <form id="sidebarLogoutForm" method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="sidebar-link w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-xl text-c-25 text-xs font-medium">
                <i class="fa-solid fa-right-from-bracket text-sm w-5 text-center flex-shrink-0"></i>
                <span class="sidebar-label">Logout</span>
            </button>
        </form>
    </nav>

    <div class="px-3 py-4 border-t border-c-5 flex-shrink-0">
        @php
            $sidebarAvatarRaw = data_get(Auth::user()->settings, 'profile_avatar');
            $sidebarAvatarSrc = '';
            if(!empty($sidebarAvatarRaw)){
                $pathOnly = parse_url($sidebarAvatarRaw, PHP_URL_PATH) ?: $sidebarAvatarRaw;
                $sidebarAvatarSrc = request()->getSchemeAndHttpHost() . $pathOnly;
            }
        @endphp
        <div class="sidebar-user flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-c-3 transition-all cursor-pointer mb-2">
            <div class="sidebar-avatar" data-user-avatar="sidebar">
                @if($sidebarAvatarSrc)
                    <img src="{{ $sidebarAvatarSrc }}" alt="{{ Auth::user()->name }}" class="user-avatar user-avatar-sidebar w-8 h-8 rounded-lg object-cover border border-c-5 flex-shrink-0" data-avatar-img>
                @else
                    <img src="" alt="{{ Auth::user()->name }}" class="user-avatar user-avatar-sidebar w-8 h-8 rounded-lg object-cover border border-c-5 flex-shrink-0" data-avatar-img style="display:none;">
                    <span class="avatar-initials">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                @endif
            </div>
            <div class="sidebar-label flex-1 min-w-0">
                <p class="text-xs font-semibold text-c-60 truncate">{{ Auth::user()->name }}</p>
                <p class="text-[10px] text-c-15 font-mono truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>

<div id="mobileSidebarOverlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/60 z-30 hidden lg:hidden backdrop-blur-sm"></div>

<!-- Main Content -->
<main class="main-content ml-[250px] min-h-screen relative z-10 transition-all duration-[350ms]">
    <header class="sticky top-0 z-30 gnav border-b border-cy/5 px-6 lg:px-8 py-3.5 flex items-center gap-4">
        <button class="lg:hidden w-9 h-9 rounded-xl flex items-center justify-center text-c-40 hover:text-cy hover:bg-cy/5 transition-all" onclick="toggleMobileSidebar()" aria-label="Menu">
            <i class="fa-solid fa-bars text-sm"></i>
        </button>
        <div class="flex-1"></div>

        <!-- Live indicator -->
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gn/5 border border-gn/8">
            <div class="w-1.5 h-1.5 rounded-full bg-gn live-dot"></div>
            <span class="text-[10px] text-gn/70 font-mono font-medium uppercase tracking-wider">Live</span>
        </div>

        <!-- Notifications -->
        <div class="relative">
            <button id="notificationsBtn" class="relative w-9 h-9 rounded-xl flex items-center justify-center text-c-25 hover:text-c-40 hover:bg-c-3 transition-all" aria-label="Notifications">
                <i class="fa-solid fa-bell text-sm"></i>
                <span id="notifBadge" class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full" style="display:none"></span>
            </button>

            <div id="notificationsDropdown" class="absolute right-0 mt-2 w-80 rounded-2xl bg-[#0f172a]/95 border border-cy/12 shadow-2xl shadow-black/20 backdrop-blur-xl z-50 hidden overflow-hidden">
                <div class="p-3 border-b border-cy/6 flex items-center justify-between">
                    <div class="text-sm font-semibold text-c-60">Notifications</div>
                    <button id="markAllReadBtn" class="text-xs text-c-25 hover:text-cy">Mark all read</button>
                </div>
                <div id="notificationsList" class="max-h-64 overflow-auto"></div>
                <div class="p-3 text-center text-xs text-c-25 border-t border-cy/6"><a href="{{ route('settings') }}#notifications">Notification settings</a></div>
            </div>
        </div>

        <!-- Theme toggle -->
        <button id="themeToggleBtn" class="w-9 h-9 rounded-xl flex items-center justify-center text-c-25 hover:text-c-40 hover:bg-c-3 transition-all" onclick="toggleTheme()" aria-label="Toggle theme">
            <i id="themeToggleIcon" class="fa-solid fa-moon text-sm"></i>
        </button>

        <!-- Profile -->
        <div class="navbar-user relative">
            @php
                $navAvatarRaw = data_get(Auth::user()->settings, 'profile_avatar');
                $navAvatarSrc = '';
                if(!empty($navAvatarRaw)){
                    $pathOnly = parse_url($navAvatarRaw, PHP_URL_PATH) ?: $navAvatarRaw;
                    $navAvatarSrc = request()->getSchemeAndHttpHost() . $pathOnly;
                }
            @endphp
            <button id="profileToggleBtn" type="button" class="flex items-center gap-2.5 pl-3 border-l border-c-5 rounded-2xl hover:bg-c-3 transition-all" onclick="toggleProfileMenu()" aria-expanded="false" aria-haspopup="true">
                <div class="navbar-avatar" data-user-avatar="navbar">
                    @if($navAvatarSrc)
                        <img src="{{ $navAvatarSrc }}" alt="{{ Auth::user()->name }}" class="user-avatar user-avatar-navbar w-8 h-8 rounded-lg object-cover border border-c-5" data-avatar-img>
                    @else
                        <img src="" alt="{{ Auth::user()->name }}" class="user-avatar user-avatar-navbar w-8 h-8 rounded-lg object-cover border border-c-5" data-avatar-img style="display:none;">
                        <span class="avatar-initials">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-semibold text-c-60 leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-c-15 font-mono">{{ Auth::user()->email }}</p>
                </div>
                <i class="fa-solid fa-chevron-down text-c-25 text-[10px] hidden sm:block"></i>
            </button>
            <div id="profileMenu" class="profile-menu hidden absolute right-0 top-full mt-2 w-44 rounded-3xl bg-[#0f172a]/95 border border-cy/12 shadow-2xl shadow-black/20 backdrop-blur-xl z-50 overflow-hidden">
                <form id="headerLogoutForm" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 text-xs font-semibold text-c-25 hover:text-cy hover:bg-cy/5 transition-all">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="p-6 lg:p-8">
        @yield('content')
    </div>
</main>

<script>
    const themeStorageKey = 'sheelTheme';
    const settingsStorageKey = 'sl_settings';

    function setThemeIcon(theme) {
        const icon = document.getElementById('themeToggleIcon');
        const btn = document.getElementById('themeToggleBtn');
        if (!icon || !btn) return;
        if (theme === 'light') {
            icon.className = 'fa-solid fa-sun text-sm';
            btn.setAttribute('aria-label', 'Switch to dark mode');
        } else if (theme === 'dark') {
            icon.className = 'fa-solid fa-moon text-sm';
            btn.setAttribute('aria-label', 'Switch to light mode');
        } else {
            icon.className = 'fa-solid fa-desktop text-sm';
            btn.setAttribute('aria-label', 'Switch to light mode');
        }
    }

    function resolveTheme(rawTheme) {
        if (rawTheme === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return rawTheme === 'light' ? 'light' : 'dark';
    }

    function getStoredTheme() {
        let theme = localStorage.getItem(themeStorageKey);
        if (theme) {
            return theme;
        }

        // ── Notifications dropdown behavior ──
        const notificationsBtn = document.getElementById('notificationsBtn');
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        const notificationsList = document.getElementById('notificationsList');
        const notifBadge = document.getElementById('notifBadge');
        const markAllReadBtn = document.getElementById('markAllReadBtn');

        async function fetchNotifications() {
            try {
                const res = await fetch('/notifications');
                const data = await res.json();
                if (!data.success) return;
                renderNotifications(data.notifications || []);
                updateBadge(data.unread || 0);
            } catch (e) {
                console.error('Failed to load notifications', e);
            }
        }

        function updateBadge(count) {
            if (!notifBadge) return;
            if (count > 0) {
                notifBadge.style.display = 'block';
            } else {
                notifBadge.style.display = 'none';
            }
        }

        function renderNotifications(list) {
            if (!notificationsList) return;
            if (!list || list.length === 0) {
                notificationsList.innerHTML = '<div class="p-4 text-xs text-c-25">No notifications</div>';
                return;
            }

            notificationsList.innerHTML = list.map(n => {
                const unread = !n.read_at ? 'bg-c-3' : '';
                const title = (n.data && (n.data.title || n.data.message || n.data.body)) ? (n.data.title || n.data.message || n.data.body) : (n.type || 'Notification');
                const body = n.data && (n.data.body || n.data.message) ? (n.data.body || n.data.message) : '';
                return `
                    <div data-id="${n.id}" class="p-3 hover:bg-c-3 cursor-pointer ${unread} border-b border-cy/6">
                        <div class="text-sm font-semibold text-c-60">${escapeHtml(title)}</div>
                        <div class="text-xs text-c-25 mt-1">${escapeHtml(body)}</div>
                        <div class="text-[10px] text-c-15 mt-2">${escapeHtml(n.created_at)}</div>
                    </div>
                `;
            }).join('');

            // attach click handlers
            notificationsList.querySelectorAll('[data-id]').forEach(el => {
                el.addEventListener('click', () => {
                    const id = el.getAttribute('data-id');
                    markNotificationRead(id, () => {
                        el.classList.remove('bg-c-3');
                    });
                });
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"]/g, function (s) {
                return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]);
            });
        }

        async function markNotificationRead(id, cb) {
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('/notifications/mark-read', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ id }),
                });
                const data = await res.json();
                updateBadge(data.unread || 0);
                if (typeof cb === 'function') cb();
            } catch (e) {
                console.error('Failed to mark notification read', e);
            }
        }

        // toggle dropdown
        if (notificationsBtn) {
            notificationsBtn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const open = !notificationsDropdown.classList.contains('hidden');
                if (open) {
                    notificationsDropdown.classList.add('hidden');
                } else {
                    notificationsDropdown.classList.remove('hidden');
                    await fetchNotifications();
                }
            });
        }

        // mark all read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch('/notifications/mark-read', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ all: true }) });
                    const data = await res.json();
                    updateBadge(0);
                    if (notificationsList) notificationsList.querySelectorAll('[data-id]').forEach(n => n.classList.remove('bg-c-3'));
                } catch (e) { console.error(e); }
            });
        }

        // close when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationsDropdown) return;
            if (!notificationsDropdown.classList.contains('hidden') && !notificationsDropdown.contains(e.target) && !notificationsBtn.contains(e.target)) {
                notificationsDropdown.classList.add('hidden');
            }
        });

        try {
            const settings = JSON.parse(localStorage.getItem(settingsStorageKey));
            if (settings && typeof settings.theme === 'string') {
                return settings.theme;
            }
        } catch (e) {
            // ignore malformed JSON
        }

        return null;
    }

    function persistTheme(rawTheme) {
        localStorage.setItem(themeStorageKey, rawTheme);

        try {
            const settings = JSON.parse(localStorage.getItem(settingsStorageKey)) || {};
            settings.theme = rawTheme;
            localStorage.setItem(settingsStorageKey, JSON.stringify(settings));
        } catch (e) {
            localStorage.setItem(settingsStorageKey, JSON.stringify({ theme: rawTheme }));
        }
    }

    function applyTheme(rawTheme) {
        const theme = resolveTheme(rawTheme);
        document.body.classList.toggle('theme-light', theme === 'light');
        document.body.classList.toggle('theme-dark', theme === 'dark');
        setThemeIcon(rawTheme);
        persistTheme(rawTheme);
    }

    function initTheme() {
        const storedTheme = getStoredTheme();
        const defaultTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        applyTheme(storedTheme || defaultTheme);
    }

    function toggleTheme() {
        const currentRaw = getStoredTheme() || (document.body.classList.contains('theme-light') ? 'light' : 'dark');
        const nextRaw = currentRaw === 'light' ? 'dark' : 'light';
        applyTheme(nextRaw);
    }

    initTheme();

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileSidebarOverlay');
        const isOpen = sidebar.classList.contains('mobile-open');

        if (isOpen) {
            sidebar.classList.remove('mobile-open');
            sidebar.classList.add('sidebar-desktop');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove('sidebar-desktop');
            sidebar.classList.add('mobile-open');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function toggleProfileMenu() {
        const menu = document.getElementById('profileMenu');
        const btn = document.getElementById('profileToggleBtn');
        if (!menu || !btn) return;
        const isVisible = menu.classList.contains('visible');
        menu.classList.toggle('visible', !isVisible);
        menu.classList.toggle('hidden', isVisible);
        btn.setAttribute('aria-expanded', String(!isVisible));
    }

    document.addEventListener('click', (event) => {
        const menu = document.getElementById('profileMenu');
        const btn = document.getElementById('profileToggleBtn');
        if (!menu || !btn) return;
        if (!menu.classList.contains('visible')) return;
        if (btn.contains(event.target) || menu.contains(event.target)) return;
        menu.classList.remove('visible');
        menu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
    });

    /* ============ GLOBAL AVATAR SYNC SYSTEM ============ */
    const AvatarSync = {
        storageKey: 'sheelearn_user_avatar',
        fallbackImages: {},
        
        // Generate fallback placeholder based on user initial
        generateFallback(userName) {
            const initial = userName.charAt(0).toUpperCase();
            const colors = ['#22d3ee', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b'];
            const colorIndex = userName.charCodeAt(0) % colors.length;
            const bgColor = colors[colorIndex];
            
            const canvas = document.createElement('canvas');
            canvas.width = 40;
            canvas.height = 40;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = bgColor;
            ctx.fillRect(0, 0, 40, 40);
            ctx.fillStyle = 'white';
            ctx.font = 'bold 20px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(initial, 20, 20);
            
            return canvas.toDataURL();
        },

        // Store avatar URL in cache
        cache(avatarUrl) {
            if (avatarUrl) {
                try {
                    localStorage.setItem(this.storageKey, avatarUrl);
                } catch(e) { console.warn('Failed to cache avatar:', e); }
            }
        },

        // Get cached avatar URL
        getCached() {
            try {
                return localStorage.getItem(this.storageKey) || null;
            } catch(e) { 
                console.warn('Failed to retrieve cached avatar:', e);
                return null;
            }
        },

        // Update all avatar elements in the DOM (images and divs)
        updateAll(avatarUrl, userName = '{{ Auth::user()->name }}') {
            const fallback = this.generateFallback(userName);

            // Helper: produce a cache-busted URL for DOM usage (skip data: URLs)
            const makeDomUrl = (url) => {
                try {
                    if (!url || url.startsWith('data:')) return url;
                    const sep = url.includes('?') ? '&' : '?';
                    return `${url}${sep}v=${Date.now()}`;
                } catch (e) { return url; }
            };

            // Update <img> elements
            document.querySelectorAll('.user-avatar').forEach(el => {
                if (el.tagName === 'IMG') {
                    if (avatarUrl) {
                        el.src = makeDomUrl(avatarUrl);
                        el.onerror = () => {
                            el.src = fallback;
                            console.warn('Failed to load avatar image, using fallback');
                        };
                    } else {
                        el.src = fallback;
                    }
                }
            });

            // Update div or other elements used as background avatars (e.g. .profile-avatar)
            document.querySelectorAll('.profile-avatar, .user-avatar-bg').forEach(el => {
                if (avatarUrl) {
                    const domUrl = makeDomUrl(avatarUrl);
                    el.style.backgroundImage = `url(${domUrl})`;
                    el.style.backgroundSize = 'cover';
                    el.style.backgroundPosition = 'center';

                    if (el.classList.contains('profile-avatar')) {
                        el.innerHTML = '<div class="avatar-edit-overlay"><i class="fa-solid fa-camera"></i></div>';
                    }
                } else {
                    el.style.backgroundImage = `url(${fallback})`;
                    el.style.backgroundSize = 'cover';
                    el.style.backgroundPosition = 'center';

                    if (el.classList.contains('profile-avatar')) {
                        el.textContent = userName.charAt(0).toUpperCase();
                        const overlay = document.createElement('div');
                        overlay.className = 'avatar-edit-overlay';
                        overlay.innerHTML = '<i class="fa-solid fa-camera"></i>';
                        el.appendChild(overlay);
                    }
                }
            });

            // Persist canonical URL (without cache buster) so future fetches can compare
            if (avatarUrl) {
                this.cache(avatarUrl);
            }
        },

        // Fetch avatar from server
        async fetchFromServer() {
            try {
                const response = await fetch('{{ route("user.avatar") }}');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.avatar_url) {
                        console.log('Fetched avatar from server:', data.avatar_url);
                        this.updateAll(data.avatar_url);
                        return data.avatar_url;
                    }
                }
            } catch(e) { 
                console.warn('Failed to fetch avatar from server:', e);
            }
            return null;
        },

        // Initialize avatar on page load
        async init() {
            const cached = this.getCached();
            if (cached) {
                console.log('Using cached avatar:', cached);
                this.updateAll(cached);
            } else {
                // Use placeholder while loading
                this.updateAll(null);
            }

            // Always sync from server for freshness
            setTimeout(async () => {
                console.log('Syncing avatar from server...');
                const fetched = await this.fetchFromServer();
                if (fetched && fetched !== cached) {
                    this.cache(fetched);
                }
            }, 100);
        },

        // Listen for avatar changes from other tabs/windows
        startSync() {
            try {
                const channel = new BroadcastChannel('avatar-sync');
                channel.onmessage = (event) => {
                    if (event.data.type === 'avatar-updated' && event.data.url) {
                        console.log('Received avatar update from another tab:', event.data.url);
                        this.updateAll(event.data.url);
                    }
                };
                console.log('BroadcastChannel listener started');
            } catch(e) { 
                console.warn('BroadcastChannel not supported, using fallback polling');
                // Fallback: check for changes periodically
                setInterval(() => this.fetchFromServer(), 30000);
            }
        },

        // Broadcast avatar change to other tabs
        broadcast(avatarUrl) {
            try {
                const channel = new BroadcastChannel('avatar-sync');
                channel.postMessage({ type: 'avatar-updated', url: avatarUrl });
                console.log('Broadcasted avatar update:', avatarUrl);
            } catch(e) { 
                console.warn('BroadcastChannel not supported for broadcasting');
            }
            this.updateAll(avatarUrl);
        }
    };

    // Initialize avatar sync
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Initializing AvatarSync...');
            AvatarSync.init();
        });
    } else {
        console.log('Document already loaded, initializing AvatarSync immediately...');
        AvatarSync.init();
    }
    
    AvatarSync.startSync();
</script>

@yield('scripts')

</body>
</html>
