@extends('admin.layout')
@section('title', 'Announcements | SHEELEARN')
@section('page_title', 'Announcements')
@section('page_breadcrumb', 'Communication')

@section('content')
<div class="space-y-5">
    <div class="ad-card p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <div class="ad-eyebrow">Broadcast</div>
                <div class="ad-heading">Create Announcement</div>
            </div>
        </div>
        <form id="announcementForm" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div><label class="ad-label">Title</label><input type="text" name="title" class="ad-input" placeholder="Announcement title" required></div>
                <div><label class="ad-label">Priority</label><select name="priority" class="ad-input ad-input-select" required><option value="info">Info</option><option value="warning">Warning</option><option value="urgent">Urgent</option></select></div>
            </div>
            <div><label class="ad-label">Message</label><textarea name="message" rows="4" class="ad-input resize-none" placeholder="Write your announcement…" required></textarea></div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="is_active" checked class="accent-cyan-400 w-4 h-4"><span class="text-xs font-medium" style="color:var(--ad-t2)">Publish immediately</span></label>
            </div>
            <div><button type="submit" class="ad-btn ad-btn-primary"><i class="fa-solid fa-paper-plane text-xs"></i> Publish Announcement</button></div>
        </form>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">History</div>
        <div class="ad-heading mb-4">Recent Announcements</div>
        <div class="space-y-3" id="announcementList">
            @foreach($announcements['items'] as $announcement)
            <div class="ad-tile">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold" style="color:var(--ad-t1)">{{ $announcement['title'] }}</span>
                    <span class="ad-badge {{ $announcement['priority'] === 'Warning' ? 'ad-badge-amber' : 'ad-badge-cyan' }}">{{ $announcement['priority'] }}</span>
                </div>
                <p class="text-xs" style="color:var(--ad-t3)">{{ $announcement['message'] }}</p>
                <p class="text-[10px] mt-2 font-medium" style="color:var(--ad-t3)">{{ $announcement['date'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
 $('announcementForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    await adSubmit(e.target, '{{ route("help.ticket.submit") }}', e.target.querySelector('button[type="submit"]'), true);
});
</script>
@endsection