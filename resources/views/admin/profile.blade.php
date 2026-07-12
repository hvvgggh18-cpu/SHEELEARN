@extends('admin.layout')
@section('title', 'Profile | SHEELEARN')
@section('page_title', 'Profile')
@section('page_breadcrumb', 'Account')

@section('content')
<div class="max-w-2xl space-y-5">
    <div class="ad-card p-5">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-xl font-bold" style="background:linear-gradient(135deg,#22d3ee,#818cf8); color:#020617;">{{ strtoupper(substr(auth('admin')->user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <p class="text-lg font-bold" style="color:var(--ad-t1)">{{ auth('admin')->user()->name ?? 'Administrator' }}</p>
                <p class="text-sm" style="color:var(--ad-t3)">{{ auth('admin')->user()->email ?? '' }}</p>
                <span class="ad-badge ad-badge-green mt-1"><span class="ad-dot" style="background:var(--ad-emerald)"></span> Active</span>
            </div>
        </div>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">Account</div>
        <div class="ad-heading mb-5">Edit Profile</div>
        <form id="profileForm" method="patch" class="space-y-4">
            <div><label class="ad-label">Full Name</label><input type="text" name="name" class="ad-input" value="{{ auth('admin')->user()->name ?? '' }}" required></div>
            <div><label class="ad-label">Email Address</label><input type="email" name="email" class="ad-input" value="{{ auth('admin')->user()->email ?? '' }}" required></div>
            <div><button type="submit" class="ad-btn ad-btn-primary"><i class="fa-solid fa-check text-xs"></i> Save Changes</button></div>
        </form>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">Security</div>
        <div class="ad-heading mb-5">Change Password</div>
        <form id="passwordForm" method="patch" class="space-y-4">
            <div><label class="ad-label">Current Password</label><input type="password" name="current_password" class="ad-input" required></div>
            <div><label class="ad-label">New Password</label><input type="password" name="new_password" class="ad-input" required minlength="8"></div>
            <div><label class="ad-label">Confirm New Password</label><input type="password" name="new_password_confirmation" class="ad-input" required></div>
            <div><button type="submit" class="ad-btn ad-btn-ghost"><i class="fa-solid fa-lock text-xs"></i> Update Password</button></div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
 $('profileForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    await adSubmit(e.target, '{{ route("admin.profile.update") }}', e.target.querySelector('button[type="submit"]'));
});
 $('passwordForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(e.target);
    if (fd.get('new_password') !== fd.get('new_password_confirmation')) return toast('Passwords do not match', 'error');
    await adSubmit(e.target, '{{ route("admin.password.update") }}', e.target.querySelector('button[type="submit"]'), true);
});
</script>
@endsection