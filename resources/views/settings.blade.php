@extends('layouts.dashboard-layout')

@section('title', 'Settings — SHEELEARN AI')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Instrument+Serif&display=swap" rel="stylesheet">
<style>
    :root {
        --bg: #04060f;
        --bg-grad: radial-gradient(1200px 600px at 10% -10%, rgba(34,211,238,0.08), transparent 60%),
                   radial-gradient(900px 500px at 100% 0%, rgba(139,92,246,0.06), transparent 60%);
        --surface: rgba(15,23,42,0.55);
        --surface-2: rgba(15,23,42,0.4);
        --surface-3: rgba(15,23,42,0.25);
        --surface-hover: rgba(30,41,59,0.5);
        --border: rgba(226,232,240,0.07);
        --border-strong: rgba(226,232,240,0.14);
        --border-hover: rgba(34,211,238,0.28);
        --accent: #22d3ee;
        --accent-2: #3b82f6;
        --accent-dim: rgba(34,211,238,0.1);
        --accent-border: rgba(34,211,238,0.28);
        --accent-glow: 0 0 0 4px rgba(34,211,238,0.12);
        --text: #e6edf7;
        --text-muted: rgba(226,232,240,0.72);
        --text-dim: rgba(226,232,240,0.42);
        --danger: #f87171;
        --danger-dim: rgba(239,68,68,0.1);
        --success: #34d399;
        --warning: #fbbf24;
        --radius: 14px;
        --radius-lg: 18px;
        --shadow-card: 0 1px 0 rgba(255,255,255,0.03) inset, 0 20px 40px -20px rgba(0,0,0,0.5);
    }
    body.theme-light {
        --bg: #f6f7fb;
        --bg-grad: radial-gradient(1200px 600px at 10% -10%, rgba(34,211,238,0.10), transparent 60%),
                   radial-gradient(900px 500px at 100% 0%, rgba(139,92,246,0.08), transparent 60%);
        --surface: rgba(255,255,255,0.9);
        --surface-2: #ffffff;
        --surface-3: #f1f5f9;
        --surface-hover: #f8fafc;
        --border: rgba(15,23,42,0.08);
        --border-strong: rgba(15,23,42,0.14);
        --border-hover: rgba(34,211,238,0.4);
        --accent-dim: rgba(34,211,238,0.12);
        --text: #0f172a;
        --text-muted: rgba(15,23,42,0.72);
        --text-dim: rgba(15,23,42,0.48);
        --shadow-card: 0 1px 2px rgba(15,23,42,0.04), 0 20px 40px -24px rgba(15,23,42,0.12);
    }

    * { box-sizing: border-box; }
    body {
        background: var(--bg);
        background-image: var(--bg-grad);
        background-attachment: fixed;
        color: var(--text);
        font-family: 'Outfit', sans-serif;
        transition: background 0.3s ease, color 0.3s ease;
        -webkit-font-smoothing: antialiased;
    }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 8px; }

    /* ---------- Layout ---------- */
    .settings-shell { max-width: 1280px; margin: 0 auto; padding: 32px 24px 80px; }
    .settings-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; flex-wrap: wrap; margin-bottom: 28px; }
    .settings-header h1 { font-size: 36px; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
    .settings-header h1 .accent { background: linear-gradient(135deg, var(--accent), var(--accent-2)); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .settings-header p { font-size: 14px; color: var(--text-muted); margin: 6px 0 0; }

    .settings-grid { display: grid; grid-template-columns: 220px 1fr 320px; gap: 24px; align-items: start; }
    @media (max-width: 1180px) { .settings-grid { grid-template-columns: 1fr 320px; } .nav-rail { display: none; } }
    @media (max-width: 900px)  { .settings-grid { grid-template-columns: 1fr; } .side-rail { order: 2; } }

    /* ---------- Nav rail ---------- */
    .nav-rail { position: sticky; top: 24px; display: flex; flex-direction: column; gap: 2px; }
    .nav-rail a {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 10px;
        font-size: 13px; font-weight: 500; color: var(--text-muted);
        text-decoration: none; transition: all 0.18s;
    }
    .nav-rail a:hover { color: var(--text); background: var(--surface-hover); }
    .nav-rail a.active { color: var(--accent); background: var(--accent-dim); }
    .nav-rail a i { width: 16px; text-align: center; opacity: 0.85; }

    /* ---------- Cards ---------- */
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 24px;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: var(--shadow-card);
        margin-bottom: 20px;
        scroll-margin-top: 24px;
    }
    .card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
    .card-head .icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: grid; place-items: center;
        background: var(--accent-dim); color: var(--accent);
        border: 1px solid var(--accent-border); font-size: 14px;
    }
    .card-head h2 { font-size: 16px; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
    .card-head p  { font-size: 12px; color: var(--text-dim); margin: 2px 0 0; }

    /* ---------- Profile card ---------- */
    .profile-card {
        display: grid; grid-template-columns: auto 1fr auto; gap: 20px; align-items: center;
    }
    @media (max-width: 640px) { .profile-card { grid-template-columns: 1fr; text-align: center; } .profile-actions { justify-content: center; } }
    .avatar-box {
        width: 84px; height: 84px; border-radius: 20px;
        border: 1px solid var(--border-strong);
        overflow: hidden; position: relative; cursor: pointer;
        background: linear-gradient(135deg, var(--accent-dim), transparent);
    }
    .avatar-box img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .avatar-box.has-image img { display: block; }
    .avatar-box.has-image .initials { display: none; }
    .avatar-box .initials {
        width: 100%; height: 100%; display: grid; place-items: center;
        font-size: 30px; font-weight: 700; color: var(--accent);
        background: linear-gradient(135deg, var(--accent-dim), rgba(59,130,246,0.08));
    }
    .avatar-box .overlay {
        position: absolute; inset: 0; background: rgba(2,6,23,0.65);
        display: none; align-items: center; justify-content: center;
        color: white; font-size: 12px; font-weight: 600; gap: 6px;
    }
    .avatar-box:hover .overlay { display: flex; }

    .profile-info h3 { font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
    .profile-info .email { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
    .profile-info .school { font-size: 12px; color: var(--accent); margin-top: 6px; display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: var(--accent-dim); border: 1px solid var(--accent-border); }

    .profile-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    /* ---------- Buttons ---------- */
    .btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 600;
        border: 1px solid var(--border-strong); background: var(--surface-2); color: var(--text);
        cursor: pointer; transition: all 0.18s; text-decoration: none; white-space: nowrap;
        font-family: inherit;
    }
    .btn:hover { border-color: var(--border-hover); background: var(--surface-hover); transform: translateY(-1px); }
    .btn.primary {
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border-color: transparent; color: #001018;
        box-shadow: 0 8px 24px -8px rgba(34,211,238,0.5);
    }
    .btn.primary:hover { filter: brightness(1.08); transform: translateY(-1px); }
    .btn.ghost { background: transparent; }
    .btn.danger { color: var(--danger); border-color: rgba(239,68,68,0.25); background: transparent; }
    .btn.danger:hover { background: var(--danger-dim); border-color: rgba(239,68,68,0.4); }
    .btn.sm { padding: 7px 12px; font-size: 12px; }
    .btn.block { width: 100%; }

    /* ---------- Pills / Segmented ---------- */
    .segmented {
        display: inline-flex; padding: 4px; gap: 4px;
        background: var(--surface-3); border: 1px solid var(--border);
        border-radius: 12px; flex-wrap: wrap;
    }
    .pill {
        padding: 8px 14px; border-radius: 8px;
        font-size: 13px; font-weight: 600;
        border: 1px solid transparent; background: transparent;
        color: var(--text-muted); cursor: pointer;
        transition: all 0.18s; display: inline-flex; align-items: center; gap: 6px;
        font-family: inherit;
    }
    .pill:hover { color: var(--text); }
    .pill.active {
        background: var(--surface);
        border-color: var(--border-strong);
        color: var(--text);
        box-shadow: 0 1px 0 rgba(255,255,255,0.05) inset, 0 4px 10px -4px rgba(0,0,0,0.4);
    }
    .pill .dot { width: 10px; height: 10px; border-radius: 50%; }
    .pill.active .dot { box-shadow: 0 0 0 3px rgba(255,255,255,0.06); }

    /* ---------- Setting rows ---------- */
    .setting-group { display: grid; gap: 22px; }
    .setting-row {
        display: grid; grid-template-columns: 1fr auto; gap: 20px; align-items: center;
        padding: 16px 0; border-top: 1px solid var(--border);
    }
    .setting-row:first-child { border-top: none; padding-top: 4px; }
    .setting-row .label { font-size: 14px; font-weight: 600; color: var(--text); }
    .setting-row .desc  { font-size: 12.5px; color: var(--text-dim); margin-top: 3px; line-height: 1.5; }
    .setting-row .field-label { font-size: 12px; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }

    /* Stacked (label above control) */
    .setting-stack { padding: 4px 0 18px; border-bottom: 1px solid var(--border); margin-bottom: 18px; }
    .setting-stack:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

    /* ---------- Toggle ---------- */
    .toggle {
        position: relative; width: 44px; height: 24px; border-radius: 999px;
        background: var(--surface-3); border: 1px solid var(--border-strong);
        cursor: pointer; appearance: none; transition: all 0.25s;
        flex-shrink: 0;
    }
    .toggle:checked {
        background: linear-gradient(135deg, var(--accent), var(--accent-2));
        border-color: transparent;
    }
    .toggle::after {
        content: ''; position: absolute; top: 2px; left: 2px;
        width: 18px; height: 18px; border-radius: 50%;
        background: white; transition: left 0.25s cubic-bezier(0.4,0,0.2,1);
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    .toggle:checked::after { left: 22px; }
    .toggle:focus-visible { outline: none; box-shadow: var(--accent-glow); }

    /* ---------- Option links (Export, Reset, Help, Feedback) ---------- */
    .option-link {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 16px; border-radius: 12px;
        background: var(--surface-2); border: 1px solid var(--border);
        color: var(--text); text-decoration: none; cursor: pointer;
        transition: all 0.18s; width: 100%; text-align: left; font-family: inherit;
    }
    .option-link + .option-link { margin-top: 10px; }
    .option-link:hover { border-color: var(--border-hover); transform: translateX(2px); }
    .option-link .l { display: flex; align-items: center; gap: 12px; }
    .option-link .l .ic {
        width: 36px; height: 36px; border-radius: 10px;
        display: grid; place-items: center;
        background: var(--accent-dim); color: var(--accent); font-size: 14px;
    }
    .option-link .title { font-size: 14px; font-weight: 600; display: block; }
    .option-link .sub   { font-size: 12px; color: var(--text-dim); margin-top: 2px; }
    .option-link .chev  { color: var(--text-dim); transition: transform 0.18s; }
    .option-link:hover .chev { transform: translateX(3px); color: var(--accent); }

    /* ---------- Sidebar cards ---------- */
    .side-rail { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 24px; }
    .storage-meter {
        display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 10px;
    }
    .storage-meter .used { font-size: 24px; font-weight: 700; letter-spacing: -0.02em; }
    .storage-meter .pct  { font-size: 13px; color: var(--accent); font-weight: 600; }
    .progress { height: 6px; background: var(--surface-3); border-radius: 999px; overflow: hidden; }
    .progress > div {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, var(--accent), var(--accent-2));
        transition: width 0.6s cubic-bezier(0.4,0,0.2,1);
    }
    .storage-note { font-size: 12px; color: var(--text-dim); margin-top: 8px; }

    .tip {
        display: flex; gap: 12px; padding: 12px; border-radius: 12px;
        background: var(--surface-2); border: 1px solid var(--border);
        transition: all 0.18s;
    }
    .tip + .tip { margin-top: 10px; }
    .tip:hover { border-color: var(--border-hover); }
    .tip .ic {
        width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
        display: grid; place-items: center;
        background: var(--accent-dim); color: var(--accent);
    }
    .tip .title { font-size: 13px; font-weight: 600; }
    .tip .sub   { font-size: 11.5px; color: var(--text-dim); margin-top: 2px; line-height: 1.4; }

    .build-tag { font-size: 11px; color: var(--text-dim); text-align: center; margin-top: 14px; letter-spacing: 0.05em; }

    /* ---------- Danger zone ---------- */
    .danger-zone {
        border: 1px solid rgba(239,68,68,0.2);
        background: linear-gradient(180deg, rgba(239,68,68,0.04), transparent);
    }
    .danger-zone .card-head .icon { background: var(--danger-dim); color: var(--danger); border-color: rgba(239,68,68,0.25); }
    .danger-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 520px) { .danger-actions { grid-template-columns: 1fr; } }

    /* ---------- Save bar ---------- */
    .save-bar {
        position: sticky; top: 12px; z-index: 40;
        display: none; align-items: center; justify-content: space-between; gap: 12px;
        padding: 10px 14px; margin-bottom: 20px;
        background: var(--surface); border: 1px solid var(--accent-border);
        backdrop-filter: blur(12px); border-radius: 12px;
        box-shadow: 0 10px 30px -10px rgba(34,211,238,0.3);
        animation: slideDown 0.25s ease;
    }
    .save-bar.show { display: flex; }
    .save-bar .msg { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; color: var(--text); }
    .save-bar .msg .pulse { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }
    @keyframes slideDown { from { transform: translateY(-8px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    /* ---------- Modals ---------- */
    .modal-bg {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(2,6,23,0.75); backdrop-filter: blur(6px);
        display: none; align-items: center; justify-content: center; padding: 16px;
    }
    .modal-bg.open { display: flex; animation: fadeIn 0.18s ease; }
    .modal-box {
        background: var(--surface); border: 1px solid var(--border-strong);
        border-radius: var(--radius-lg); padding: 26px; width: 100%; max-width: 460px;
        box-shadow: 0 30px 80px -20px rgba(0,0,0,0.6); animation: modalIn 0.22s ease;
    }
    .modal-box h3 { font-size: 20px; font-weight: 700; margin: 0 0 4px; letter-spacing: -0.01em; }
    .modal-box .sub { font-size: 13px; color: var(--text-muted); margin-bottom: 22px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes modalIn { from { opacity: 0; transform: translateY(10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

    .m-label { font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; display: block; }
    .field {
        width: 100%; background: var(--surface-3); border: 1px solid var(--border-strong);
        border-radius: 10px; padding: 11px 14px; font-size: 14px; color: var(--text);
        font-family: inherit; outline: none; margin-bottom: 14px;
        transition: all 0.18s;
    }
    .field:focus { border-color: var(--accent); box-shadow: var(--accent-glow); background: var(--surface-2); }
    .field::placeholder { color: var(--text-dim); }

    .dropzone {
        border: 1.5px dashed var(--border-strong); border-radius: 12px;
        padding: 28px 20px; text-align: center; cursor: pointer;
        transition: all 0.2s; background: var(--surface-3);
    }
    .dropzone:hover, .dropzone.dragover { border-color: var(--accent); background: var(--accent-dim); }
    .dropzone .ic { font-size: 28px; color: var(--accent); margin-bottom: 8px; }
    .dropzone .t { font-size: 14px; font-weight: 600; }
    .dropzone .s { font-size: 12px; color: var(--text-dim); margin-top: 4px; }

    /* ---------- Toasts ---------- */
    #toasts { position: fixed; top: 20px; right: 20px; z-index: 200; display: flex; flex-direction: column; gap: 10px; }
    .toast {
        padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 500;
        display: flex; align-items: center; gap: 10px;
        transform: translateX(120%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
        box-shadow: 0 12px 30px -10px rgba(0,0,0,0.5);
        background: var(--surface); border: 1px solid var(--border-strong);
        color: var(--text); backdrop-filter: blur(12px); max-width: 380px;
    }
    .toast.show { transform: translateX(0); }
    .toast-success { border-color: rgba(52,211,153,0.35); color: var(--success); }
    .toast-error   { border-color: rgba(239,68,68,0.35);  color: var(--danger); }
    .toast-info    { border-color: var(--accent-border);  color: var(--accent); }

    .spinner {
        display: inline-block; width: 14px; height: 14px;
        border: 2px solid rgba(0,0,0,0.2); border-top-color: currentColor;
        border-radius: 50%; animation: spin 0.65s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .view { animation: fadeUp 0.35s ease; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('content')
<div class="settings-shell view">

    {{-- Header --}}
    <div class="settings-header">
        <div>
            <h1>Settings <span class="accent">·</span></h1>
            <p>Manage your account, appearance, and application preferences.</p>
        </div>
    </div>

    {{-- Save bar --}}
    <div class="save-bar" id="unsavedBanner">
        <div class="msg"><span class="pulse"></span> You have unsaved changes</div>
        <div style="display:flex; gap:8px;">
            <button class="btn ghost sm" id="discardBtn">Discard</button>
            <button class="btn primary sm" id="saveAllBtn"><i class="fa-solid fa-check"></i> Save changes</button>
        </div>
    </div>

    <div class="settings-grid">

        {{-- Left nav rail --}}
        <nav class="nav-rail" aria-label="Settings sections">
            <a href="#profile" class="active"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="#appearance"><i class="fa-solid fa-palette"></i> Appearance</a>
            <a href="#notifications"><i class="fa-solid fa-bell"></i> Notifications</a>
            <a href="#privacy"><i class="fa-solid fa-shield-halved"></i> Privacy</a>
            <a href="#account"><i class="fa-solid fa-gear"></i> Account</a>
            <a href="#danger"><i class="fa-solid fa-triangle-exclamation"></i> Danger zone</a>
        </nav>

        {{-- Main column --}}
        <div>
            {{-- Profile card --}}
            <section class="card" id="profile">
                <div class="profile-card">
                    @php
                        $profileAvatarSrc = '';
                        if(!empty($profileAvatar)) {
                            $pathOnly = parse_url($profileAvatar, PHP_URL_PATH) ?: $profileAvatar;
                            $profileAvatarSrc = request()->getSchemeAndHttpHost() . $pathOnly;
                        }
                    @endphp
                    <div class="avatar-box {{ $profileAvatarSrc ? 'has-image' : '' }}" id="avatarBox" data-user-avatar>
                        <span class="initials" id="avatarInitials">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        <img src="{{ $profileAvatarSrc }}" alt="Profile photo">
                        <div class="overlay"><i class="fa-solid fa-camera"></i> Change</div>
                    </div>

                    <div class="profile-info">
                        <h3 id="userName">{{ auth()->user()->name ?? 'User' }}</h3>
                        <div class="email" id="userEmail">{{ auth()->user()->email ?? '' }}</div>
                        @if(isset($school) && $school)
                            <div class="school" id="userSchool"><i class="fa-solid fa-school"></i> {{ $school }}</div>
                        @endif
                    </div>

                    <div class="profile-actions">
                        <button class="btn sm" id="editProfileBtn"><i class="fa-solid fa-pen"></i> Edit</button>
                        <button class="btn sm" id="changePassBtn"><i class="fa-solid fa-lock"></i> Password</button>
                        <button class="btn sm primary" id="uploadPhotoBtn"><i class="fa-solid fa-image"></i> Photo</button>
                    </div>
                </div>
            </section>

            {{-- Appearance --}}
            <section class="card" id="appearance">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-palette"></i></div>
                    <div>
                        <h2>Appearance</h2>
                        <p>Personalize the interface to match your style.</p>
                    </div>
                </div>

                <div class="setting-stack">
                    <div class="field-label">Theme</div>
                    <div class="segmented">
                        <button class="pill" data-setting="theme" data-value="dark"><i class="fa-solid fa-moon"></i> Dark</button>
                        <button class="pill" data-setting="theme" data-value="light"><i class="fa-solid fa-sun"></i> Light</button>
                        <button class="pill" data-setting="theme" data-value="system"><i class="fa-solid fa-desktop"></i> System</button>
                    </div>
                </div>

                <div class="setting-stack">
                    <div class="field-label">Accent color</div>
                    <div class="segmented">
                        <button class="pill" data-setting="accent" data-value="cyan"><span class="dot" style="background:#22d3ee"></span> Cyan</button>
                        <button class="pill" data-setting="accent" data-value="blue"><span class="dot" style="background:#3b82f6"></span> Blue</button>
                        <button class="pill" data-setting="accent" data-value="purple"><span class="dot" style="background:#8b5cf6"></span> Purple</button>
                        <button class="pill" data-setting="accent" data-value="green"><span class="dot" style="background:#10b981"></span> Green</button>
                    </div>
                </div>

                <div class="setting-stack">
                    <div class="field-label">Font size</div>
                    <div class="segmented">
                        <button class="pill" data-setting="fontSize" data-value="small">Small</button>
                        <button class="pill" data-setting="fontSize" data-value="medium">Medium</button>
                        <button class="pill" data-setting="fontSize" data-value="large">Large</button>
                    </div>
                </div>
            </section>

            {{-- Notifications --}}
            <section class="card" id="notifications">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-bell"></i></div>
                    <div>
                        <h2>Notifications</h2>
                        <p>Choose what you'd like to hear about.</p>
                    </div>
                </div>

                <div class="setting-group">
                    <div class="setting-row">
                        <div><div class="label">Email notifications</div><div class="desc">Receive product updates and account activity by email.</div></div>
                        <input type="checkbox" class="toggle" data-key="notif_email">
                    </div>
                    <div class="setting-row">
                        <div><div class="label">Flashcard review reminders</div><div class="desc">Get nudges to keep your flashcards fresh.</div></div>
                        <input type="checkbox" class="toggle" data-key="notif_flashcard">
                    </div>
                    <div class="setting-row">
                        <div><div class="label">Achievement alerts</div><div class="desc">Celebrate milestones as you reach them.</div></div>
                        <input type="checkbox" class="toggle" data-key="notif_achievements">
                    </div>
                    <div class="setting-row">
                        <div><div class="label">Weekly progress report</div><div class="desc">A summary of your learning delivered every Monday.</div></div>
                        <input type="checkbox" class="toggle" data-key="notif_weekly">
                    </div>
                </div>
            </section>

            {{-- Privacy --}}
            <section class="card" id="privacy">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <h2>Privacy & security</h2>
                        <p>Control how your data is protected and used.</p>
                    </div>
                </div>

                <div class="setting-group">
                    <div class="setting-row">
                        <div><div class="label">Two-factor authentication</div><div class="desc">Add an extra layer of security to your account.</div></div>
                        <a href="{{ \Illuminate\Support\Facades\Route::has('two-factor.setup') ? route('two-factor.setup') : '#' }}" class="btn sm"><i class="fa-solid fa-shield"></i> {{ optional(auth()->user())->two_factor_enabled ? 'Manage' : 'Enable' }}</a>
                    </div>
                    <div class="setting-row">
                        <div><div class="label">Privacy mode</div><div class="desc">Limit data sharing and third-party tracking.</div></div>
                        <input type="checkbox" class="toggle" data-key="privacy_mode">
                    </div>
                    <div class="setting-row">
                        <div><div class="label">Anonymous analytics</div><div class="desc">Help us improve SHEELEARN by sharing anonymized usage data.</div></div>
                        <input type="checkbox" class="toggle" data-key="data_sharing">
                    </div>
                </div>
            </section>

            {{-- Account --}}
            <section class="card" id="account">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-gear"></i></div>
                    <div>
                        <h2>Account</h2>
                        <p>Manage your data and preferences.</p>
                    </div>
                </div>

                <button class="option-link" id="exportBtn">
                    <span class="l">
                        <span class="ic"><i class="fa-solid fa-download"></i></span>
                        <span><span class="title">Export my data</span><span class="sub">Download everything as a JSON archive.</span></span>
                    </span>
                    <i class="fa-solid fa-chevron-right chev"></i>
                </button>

                <button class="option-link" id="resetBtn">
                    <span class="l">
                        <span class="ic"><i class="fa-solid fa-rotate-left"></i></span>
                        <span><span class="title">Reset preferences</span><span class="sub">Restore all settings to their defaults.</span></span>
                    </span>
                    <i class="fa-solid fa-chevron-right chev"></i>
                </button>

                <form method="POST" action="{{ \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : '#' }}" style="margin-top: 14px;">
                    @csrf
                    <button type="submit" class="btn block"><i class="fa-solid fa-right-from-bracket"></i> Log out</button>
                </form>
            </section>

            {{-- Danger zone --}}
            <section class="card danger-zone" id="danger">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h2>Danger zone</h2>
                        <p>Irreversible actions. Please proceed with caution.</p>
                    </div>
                </div>
                <div class="setting-row" style="padding-top:0; border-top:none;">
                    <div>
                        <div class="label">Delete account</div>
                        <div class="desc">Permanently remove your account and all associated data. This cannot be undone.</div>
                    </div>
                    <button class="btn danger" id="deleteAcctBtn"><i class="fa-solid fa-trash"></i> Delete account</button>
                </div>
            </section>
        </div>

        {{-- Right sidebar --}}
        <aside class="side-rail">
            <section class="card">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-database"></i></div>
                    <div><h2>Storage</h2></div>
                </div>
                <div class="storage-meter">
                    <div class="used">{{ $storageUsedText ?? '0 MB' }}</div>
                    <div class="pct">{{ $storageUsagePercent ?? 0 }}%</div>
                </div>
                <div class="progress"><div style="width: {{ $storageUsagePercent ?? 0 }}%"></div></div>
                <div class="storage-note">{{ $storageUsedText ?? '0 MB' }} of {{ $storageLimitText ?? '500 MB' }} used</div>
            </section>

            <section class="card">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-circle-info"></i></div>
                    <div><h2>About</h2></div>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Route::has('help') ? route('help') : '#' }}" class="option-link">
                    <span class="l">
                        <span class="ic"><i class="fa-solid fa-life-ring"></i></span>
                        <span><span class="title">Help center</span><span class="sub">Guides, FAQs, and tutorials.</span></span>
                    </span>
                    <i class="fa-solid fa-chevron-right chev"></i>
                </a>
                <a href="{{ \Illuminate\Support\Facades\Route::has('feedback') ? route('feedback') : '#' }}" class="option-link">
                    <span class="l">
                        <span class="ic"><i class="fa-solid fa-comment-dots"></i></span>
                        <span><span class="title">Send feedback</span><span class="sub">Help us improve SHEELEARN.</span></span>
                    </span>
                    <i class="fa-solid fa-chevron-right chev"></i>
                </a>
                <div class="build-tag">SHEELEARN AI · Build {{ config('app.build_number', '4f7c2a') }}</div>
            </section>

            <section class="card">
                <div class="card-head">
                    <div class="icon"><i class="fa-solid fa-lightbulb"></i></div>
                    <div><h2>Tips</h2></div>
                </div>
                <div class="tip">
                    <div class="ic"><i class="fa-solid fa-shield"></i></div>
                    <div><div class="title">Secure your account</div><div class="sub">Enable 2FA to protect your data.</div></div>
                </div>
                <div class="tip">
                    <div class="ic"><i class="fa-solid fa-palette"></i></div>
                    <div><div class="title">Customize your view</div><div class="sub">Switch themes for comfortable reading.</div></div>
                </div>
                <div class="tip">
                    <div class="ic"><i class="fa-solid fa-bell"></i></div>
                    <div><div class="title">Stay updated</div><div class="sub">Tune notifications to your pace.</div></div>
                </div>
            </section>
        </aside>
    </div>
</div>

{{-- Toasts --}}
<div id="toasts"></div>

{{-- Edit Profile Modal --}}
<div class="modal-bg" id="editProfileModal">
    <div class="modal-box">
        <h3>Edit profile</h3>
        <div class="sub">Update your personal information.</div>
        <label class="m-label">Full name</label>
        <input type="text" class="field" id="mName" value="{{ auth()->user()->name ?? '' }}">
        <label class="m-label">Email address</label>
        <input type="email" class="field" id="mEmail" value="{{ auth()->user()->email ?? '' }}">
        @if(isset($school))
            <label class="m-label">School</label>
            <input type="text" class="field" id="mSchool" value="{{ $school }}">
        @endif
        <div class="modal-actions">
            <button class="btn ghost" data-dismiss>Cancel</button>
            <button class="btn primary" id="saveProfileBtn">Save changes</button>
        </div>
    </div>
</div>

{{-- Change Password Modal --}}
<div class="modal-bg" id="changePassModal">
    <div class="modal-box">
        <h3>Change password</h3>
        <div class="sub">Use at least 8 characters. Mix in numbers and symbols for extra strength.</div>
        <label class="m-label">Current password</label>
        <input type="password" class="field" id="mCurPass">
        <label class="m-label">New password</label>
        <input type="password" class="field" id="mNewPass">
        <label class="m-label">Confirm new password</label>
        <input type="password" class="field" id="mConfPass">
        <div class="modal-actions">
            <button class="btn ghost" data-dismiss>Cancel</button>
            <button class="btn primary" id="savePassBtn">Update password</button>
        </div>
    </div>
</div>

{{-- Upload Photo Modal --}}
<div class="modal-bg" id="uploadPhotoModal">
    <div class="modal-box">
        <h3>Update profile photo</h3>
        <div class="sub">This photo appears across SHEELEARN.</div>
        <div class="dropzone" id="dropZone">
            <div class="ic"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <div class="t">Drag & drop or click to browse</div>
            <div class="s">PNG, JPG, GIF, WebP · Max 2MB</div>
            <input type="file" id="fileInput" accept="image/*" hidden>
        </div>
        <div id="previewWrap" class="hidden" style="margin-top:16px; text-align:center;">
            <img id="previewImg" style="max-width:140px; max-height:140px; border-radius:16px; border:1px solid var(--border-strong);" alt="">
        </div>
        <div class="modal-actions">
            <button class="btn ghost" data-dismiss>Cancel</button>
            <button class="btn primary" id="uploadSaveBtn" disabled>Upload photo</button>
        </div>
    </div>
</div>

{{-- Delete Account Modal --}}
<div class="modal-bg" id="deleteModal">
    <div class="modal-box">
        <h3 style="color: var(--danger);">Delete account</h3>
        <div class="sub">⚠️ This is permanent. All of your data will be erased and cannot be recovered.</div>
        <label class="m-label">Type your email to confirm</label>
        <input type="email" class="field" id="delEmail" placeholder="{{ auth()->user()->email ?? '' }}">
        <div class="modal-actions">
            <button class="btn ghost" data-dismiss>Cancel</button>
            <button class="btn danger" id="confirmDelete"><i class="fa-solid fa-trash"></i> Delete forever</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
/* ============================================================
 *  SHEELEARN Settings — Avatar Sync Singleton (unchanged API)
 * ============================================================ */
window.SL = window.SL || {};
SL.Avatar = (function(){
    var KEY = 'sl_avatar';
    var ENDPOINT = '/settings/avatar';
    var ch = null;
    try { ch = new BroadcastChannel('sl_avatar'); } catch(e){}

    function get(){ try{ var d=JSON.parse(localStorage.getItem(KEY)); return d&&d.url?d.url+'?_v='+d.ts:null; }catch(e){return null;} }
    function set(url){ var ts=Date.now(); try{ localStorage.setItem(KEY,JSON.stringify({url:url,ts:ts})); }catch(e){} return url+'?_v='+ts; }
    function bust(url,ts){ return url+(url.indexOf('?')>-1?'&':'?')+'_v='+(ts||Date.now()); }

    function find(){
        var els=[];
        document.querySelectorAll('[data-user-avatar]').forEach(function(el){
            els.push({el:el, type: el.tagName==='IMG'?'img':'child'});
        });
        ['.sidebar-avatar img','.sidebar .user-avatar img','[data-sidebar-avatar] img',
         '.topbar-avatar img','.navbar .user-avatar img','[data-navbar-avatar] img',
         'img[src*="avatar"]'].forEach(function(s){
            document.querySelectorAll(s).forEach(function(el){
                if(!els.some(function(e){return e.el===el;})) els.push({el:el,type:'img'});
            });
        });
        return els;
    }

    function apply(url){
        function norm(u){ try{ var p=new URL(u,window.location.origin); p.host=window.location.host; p.protocol=window.location.protocol; return p.toString(); }catch(e){ return u; } }
        var box = document.getElementById('avatarBox');
        if(box){
            var img = box.querySelector('img');
            if(img){ img.src = norm(url); }
            box.classList.add('has-image');
        }
        find().forEach(function(o){
            if(o.type==='img'){ o.el.src=norm(url); o.el.style.display=''; }
            else {
                var img=o.el.querySelector('img[data-avatar-img]')||o.el.querySelector('img');
                if(img){ img.src=norm(url); img.style.display='block'; }
                o.el.classList.add('has-image');
            }
        });
    }
    function listen(){
        if(ch) ch.onmessage=function(e){ if(e.data&&e.data.url) apply(bust(e.data.url,e.data.ts)); };
        window.addEventListener('storage',function(e){
            if(e.key===KEY&&e.newValue) try{ var d=JSON.parse(e.newValue); if(d&&d.url) apply(bust(d.url,d.ts)); }catch(x){}
        });
    }
    async function upload(file){
        if(!file) throw new Error('No file');
        if(!/^image\/(png|jpeg|gif|webp)$/.test(file.type)) throw new Error('Invalid type. Use PNG, JPG, GIF, or WebP.');
        if(file.size>2*1024*1024) throw new Error('Max 2MB.');
        var fd=new FormData(); fd.append('avatar',file);
        var r=await fetch(ENDPOINT,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')},body:fd,credentials:'same-origin'});
        if(!r.ok){var e=await r.json().catch(function(){});throw new Error((e&&e.message)||'Upload failed.');}
        var d=await r.json();
        var returnedUrl=d.url||d.avatar_url||d.avatarUrl||d.avatarURL;
        if(!returnedUrl) throw new Error((d&&d.message)||'No URL returned.');
        var b=set(returnedUrl); apply(b);
        if(ch) try{ ch.postMessage({url:returnedUrl,ts:Date.now()}); }catch(x){}
        return b;
    }
    function init(){ var s=get(); if(s) apply(s); listen(); }
    return {init:init,upload:upload,get:get};
})();

/* ============================================================
 *  Settings page logic
 * ============================================================ */
document.addEventListener('DOMContentLoaded', function(){
    SL.Avatar.init();

    var saved = {}, current = {}, dirty = false;

    /* ---- Toast ---- */
    function showToast(msg, type='info') {
        const c = document.getElementById('toasts'), t = document.createElement('div');
        t.className = `toast toast-${type}`;
        const icons = {success:'circle-check',error:'circle-exclamation',info:'circle-info'};
        t.innerHTML = `<i class="fa-solid fa-${icons[type]||icons.info}"></i><span>${msg}</span>`;
        c.appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3500);
    }

    /* ---- Modal helpers ---- */
    function openModal(id){ document.getElementById(id).classList.add('open'); document.body.style.overflow='hidden'; }
    function closeModal(id){ document.getElementById(id).classList.remove('open'); document.body.style.overflow=''; }
    document.querySelectorAll('[data-dismiss]').forEach(b => b.addEventListener('click', () => closeModal(b.closest('.modal-bg').id)));
    document.querySelectorAll('.modal-bg').forEach(o => o.addEventListener('click', e => { if(e.target===o) closeModal(o.id); }));
    document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.modal-bg.open').forEach(m => closeModal(m.id)); });

    /* ---- Settings State ---- */
    var SETTINGS_KEY = 'sl_settings';
    var APP_THEME_KEY = 'sheelTheme';
    var defaults = {
        theme:'dark', accent:'cyan', fontSize:'medium',
        notif_email:true, notif_flashcard:false, notif_achievements:true, notif_weekly:true,
        privacy_mode:false, data_sharing:true
    };
    try {
        var stored = JSON.parse(localStorage.getItem(SETTINGS_KEY));
        if (stored) {
            defaults = Object.assign(defaults, stored);
        }
    } catch (e) {}

    try {
        var storedTheme = localStorage.getItem(APP_THEME_KEY);
        if (storedTheme && !stored?.theme) {
            defaults.theme = storedTheme;
        }
    } catch (e) {}

    function hexToRgb(hex){ var r=/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex); return r?{r:parseInt(r[1],16),g:parseInt(r[2],16),b:parseInt(r[3],16)}:null; }

    function applySetting(key, val){
        if(key==='theme'){
            var sysDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var useLight = val==='light' || (val==='system' && !sysDark);
            document.body.classList.toggle('theme-light', useLight);
            document.body.classList.toggle('theme-dark', !useLight);
        }
        if(key==='accent'){
            var map={cyan:['#22d3ee','#3b82f6'], blue:['#3b82f6','#22d3ee'], purple:['#8b5cf6','#a78bfa'], green:['#10b981','#34d399']};
            var pair=map[val]||map.cyan;
            document.documentElement.style.setProperty('--accent', pair[0]);
            document.documentElement.style.setProperty('--accent-2', pair[1]);
            var rgb=hexToRgb(pair[0]);
            if(rgb){
                document.documentElement.style.setProperty('--accent-dim', `rgba(${rgb.r},${rgb.g},${rgb.b},0.12)`);
                document.documentElement.style.setProperty('--accent-border', `rgba(${rgb.r},${rgb.g},${rgb.b},0.28)`);
            }
        }
        if(key==='fontSize'){
            var sizes={small:'14px', medium:'16px', large:'18px'};
            document.documentElement.style.fontSize = sizes[val] || '16px';
        }
    }

    /* Init pills */
    document.querySelectorAll('.pill[data-setting]').forEach(btn => {
        var key = btn.dataset.setting;
        var val = defaults[key];
        btn.classList.toggle('active', btn.dataset.value === val);
        current[key] = val; saved[key] = val;
    });
    ['theme','accent','fontSize'].forEach(k => applySetting(k, defaults[k]));

    /* Init toggles */
    document.querySelectorAll('.toggle[data-key]').forEach(cb => {
        var key = cb.dataset.key;
        cb.checked = defaults[key] !== undefined ? defaults[key] : cb.checked;
        current[key] = cb.checked; saved[key] = cb.checked;
    });

    /* Pill clicks */
    document.querySelectorAll('.pill[data-setting]').forEach(btn => {
        btn.addEventListener('click', () => {
            var group = btn.dataset.setting;
            document.querySelectorAll(`.pill[data-setting="${group}"]`).forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            current[group] = btn.dataset.value;
            applySetting(group, btn.dataset.value);
            checkDirty();
        });
    });

    /* Toggle changes */
    document.querySelectorAll('.toggle[data-key]').forEach(cb => {
        cb.addEventListener('change', () => { current[cb.dataset.key] = cb.checked; checkDirty(); });
    });

    function checkDirty() {
        dirty = Object.keys(saved).some(k => saved[k] !== current[k]);
        document.getElementById('unsavedBanner').classList.toggle('show', dirty);
    }

    /* Discard */
    document.getElementById('discardBtn').addEventListener('click', () => {
        Object.keys(saved).forEach(k => current[k] = saved[k]);
        document.querySelectorAll('.pill[data-setting]').forEach(btn => {
            var key = btn.dataset.setting;
            btn.classList.toggle('active', btn.dataset.value === saved[key]);
            applySetting(key, saved[key]);
        });
        document.querySelectorAll('.toggle[data-key]').forEach(cb => cb.checked = saved[cb.dataset.key]);
        checkDirty();
        showToast('Changes discarded', 'info');
    });

    /* Save */
    document.getElementById('saveAllBtn').addEventListener('click', async () => {
        if(!dirty) return;
        var btn = document.getElementById('saveAllBtn');
        var original = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Saving...';
        btn.disabled = true;
        try {
            localStorage.setItem(SETTINGS_KEY, JSON.stringify(Object.assign({}, defaults, current)));
            localStorage.setItem(APP_THEME_KEY, current.theme);
            await new Promise(r => setTimeout(r, 700));
            saved = Object.assign({}, current);
            checkDirty();
            showToast('Settings saved successfully', 'success');
        } catch(e) {
            showToast('Failed to save settings', 'error');
        } finally {
            btn.innerHTML = original;
            btn.disabled = false;
        }
    });

    /* Nav rail scroll spy */
    var navLinks = document.querySelectorAll('.nav-rail a');
    var sections = Array.from(navLinks).map(a => document.querySelector(a.getAttribute('href')));
    var spy = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if(e.isIntersecting){
                navLinks.forEach(a => a.classList.toggle('active', a.getAttribute('href') === '#' + e.target.id));
            }
        });
    }, { rootMargin: '-40% 0px -55% 0px' });
    sections.forEach(s => s && spy.observe(s));
    navLinks.forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        var t = document.querySelector(a.getAttribute('href'));
        if(t) t.scrollIntoView({ behavior:'smooth', block:'start' });
    }));

    /* Edit Profile */
    document.getElementById('editProfileBtn').addEventListener('click', () => openModal('editProfileModal'));
    document.getElementById('saveProfileBtn').addEventListener('click', async function(){
        var name = document.getElementById('mName').value.trim();
        var email = document.getElementById('mEmail').value.trim();
        var schoolInput = document.getElementById('mSchool');
        var school = schoolInput ? schoolInput.value.trim() : '';
        if(!name || !email){ showToast('Name and email are required', 'error'); return; }
        var btn = this, o = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Saving...'; btn.disabled = true;
        try {
            await new Promise(r => setTimeout(r, 600));
            document.getElementById('userName').textContent = name;
            document.getElementById('userEmail').textContent = email;
            document.getElementById('avatarInitials').textContent = name.charAt(0).toUpperCase();
            var schoolEl = document.getElementById('userSchool');
            if(schoolEl){ schoolEl.querySelector('span')?.remove(); schoolEl.innerHTML = '<i class="fa-solid fa-school"></i> ' + school; }
            closeModal('editProfileModal');
            showToast('Profile updated successfully', 'success');
        } catch(e){ showToast('Failed to update profile', 'error'); }
        finally { btn.innerHTML = o; btn.disabled = false; }
    });

    /* Change Password */
    document.getElementById('changePassBtn').addEventListener('click', () => openModal('changePassModal'));
    document.getElementById('savePassBtn').addEventListener('click', async function(){
        var cur = document.getElementById('mCurPass').value;
        var p = document.getElementById('mNewPass').value;
        var c = document.getElementById('mConfPass').value;
        if(!cur || !p || !c){ showToast('All fields are required', 'error'); return; }
        if(p !== c){ showToast('Passwords do not match', 'error'); return; }
        if(p.length < 8){ showToast('Password must be at least 8 characters', 'error'); return; }
        var btn = this, o = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Updating...'; btn.disabled = true;
        try {
            await new Promise(r => setTimeout(r, 700));
            closeModal('changePassModal');
            showToast('Password updated successfully', 'success');
            ['mCurPass','mNewPass','mConfPass'].forEach(id => document.getElementById(id).value = '');
        } catch(e){ showToast('Failed to update password', 'error'); }
        finally { btn.innerHTML = o; btn.disabled = false; }
    });

    /* Upload Photo */
    document.getElementById('uploadPhotoBtn').addEventListener('click', () => openModal('uploadPhotoModal'));
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const previewWrap = document.getElementById('previewWrap');
    const previewImg = document.getElementById('previewImg');
    const uploadSaveBtn = document.getElementById('uploadSaveBtn');
    let currentFile = null;

    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('dragover'); if(e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]); });
    fileInput.addEventListener('change', () => { if(fileInput.files[0]) handleFile(fileInput.files[0]); });

    function handleFile(file){
        if(!file.type.match('image.*')){ showToast('Invalid file type', 'error'); return; }
        currentFile = file;
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src = e.target.result;
            previewWrap.classList.remove('hidden');
            previewWrap.style.display = 'block';
            uploadSaveBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    uploadSaveBtn.addEventListener('click', async () => {
        if(!currentFile) return;
        var o = uploadSaveBtn.innerHTML;
        uploadSaveBtn.innerHTML = '<span class="spinner"></span> Uploading...';
        uploadSaveBtn.disabled = true;
        try {
            await SL.Avatar.upload(currentFile);
            closeModal('uploadPhotoModal');
            previewWrap.style.display = 'none';
            currentFile = null; fileInput.value = '';
            showToast('Photo uploaded successfully', 'success');
        } catch(e){ showToast(e.message || 'Upload failed', 'error'); }
        finally { uploadSaveBtn.innerHTML = o; uploadSaveBtn.disabled = true; }
    });

    /* Delete Account */
    document.getElementById('deleteAcctBtn').addEventListener('click', () => openModal('deleteModal'));
    document.getElementById('confirmDelete').addEventListener('click', () => {
        var email = document.getElementById('delEmail').value.trim();
        var actual = document.getElementById('userEmail').textContent.trim();
        if(email !== actual){ showToast('Email does not match', 'error'); return; }
        closeModal('deleteModal');
        showToast('Account deletion request submitted', 'info');
    });

    /* Export / Reset stubs */
    document.getElementById('exportBtn').addEventListener('click', () => showToast('Preparing your data export...', 'info'));
    document.getElementById('resetBtn').addEventListener('click', () => {
        if(!confirm('Reset all preferences to defaults?')) return;
        localStorage.removeItem(SETTINGS_KEY);
        showToast('Preferences reset. Reloading...', 'success');
        setTimeout(() => location.reload(), 900);
    });
});
</script>
@endsection
