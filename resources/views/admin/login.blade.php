<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login | SHEELEARN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter','system-ui','sans-serif'] },
                    colors: { n: { DEFAULT:'#020617', 1:'#0a0f1e', 2:'#0f172a', 3:'#162033' }, c: { DEFAULT:'#e2e8f0', 60:'rgba(226,232,240,0.6)', 40:'rgba(226,232,240,0.4)', 25:'rgba(226,232,240,0.25)', 15:'rgba(226,232,240,0.15)', 10:'rgba(226,232,240,0.10)' }, cy: { DEFAULT:'#22d3ee' }, ac: { DEFAULT:'#818cf8' }, gn: { DEFAULT:'#34d399' } }
                }
            }
        }
    </script>
    <style>
        body{font-family:'Inter',sans-serif;background:radial-gradient(circle at top left,#0f172a 0%,#020617 50%,#01040d 100%);color:#e2e8f0}
        .glass{background:rgba(2,6,23,0.72);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(34,211,238,0.12)}
        .input{background:rgba(15,23,42,0.6);border:1px solid rgba(34,211,238,0.12);color:#f8fafc}
        .input:focus{border-color:rgba(34,211,238,0.4);box-shadow:0 0 0 3px rgba(34,211,238,0.12)}
        .btn-cy{background:linear-gradient(135deg,#22d3ee,#0891b2);color:#020617;box-shadow:0 4px 24px -4px rgba(34,211,238,.3);transition:all .35s ease;font-weight:700}
        .btn-cy:hover{transform:translateY(-2px);box-shadow:0 8px 40px -4px rgba(34,211,238,.45)}
    </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-140px] left-[-120px] w-[420px] h-[420px] rounded-full bg-cyan-400/10 blur-[100px]"></div>
        <div class="absolute bottom-[-140px] right-[-120px] w-[420px] h-[420px] rounded-full bg-indigo-500/10 blur-[100px]"></div>
    </div>
    <div class="relative w-full max-w-md glass rounded-3xl p-8 shadow-2xl shadow-black/40">
        <div class="flex items-center justify-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-cyan-700 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                <i class="fa-solid fa-shield-halved text-slate-950 text-xl"></i>
            </div>
        </div>
        <div class="text-center mb-8">
            <h1 class="text-2xl font-black tracking-tight text-c">Admin Access</h1>
            <p class="mt-2 text-sm text-c-40">Secure administrator sign in for SHEELEARN</p>
        </div>
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
                {{ $errors->first('email') ?: 'Invalid administrator credentials.' }}
            </div>
        @endif
        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-2 block text-[10px] font-bold uppercase tracking-[.2em] text-c-40">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="input w-full rounded-xl px-4 py-3 text-sm outline-none" placeholder="admin@example.com">
            </div>
            <div>
                <label class="mb-2 block text-[10px] font-bold uppercase tracking-[.2em] text-c-40">Password</label>
                <input type="password" name="password" required class="input w-full rounded-xl px-4 py-3 text-sm outline-none" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-cy w-full rounded-xl px-4 py-3 text-sm font-bold uppercase tracking-[.2em]">Sign In</button>
        </form>
        <p class="mt-6 text-center text-xs text-c-25">Only authorized administrators may access this area.</p>
    </div>
</body>
</html>
