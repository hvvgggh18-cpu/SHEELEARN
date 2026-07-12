@extends('admin.layout')
@section('title', 'Reports | SHEELEARN')
@section('page_title', 'Reports')
@section('page_breadcrumb', 'Insights')

@section('content')
<div class="space-y-5">
    <div class="ad-card p-5">
        <div class="ad-eyebrow">Generate</div>
        <div class="ad-heading mb-5">Create Report</div>
        <form id="reportForm" class="grid gap-4 md:grid-cols-3 items-end">
            <div><label class="ad-label">Report Type</label><select name="type" class="ad-input ad-input-select" required><option value="users">User Activity</option><option value="ai">AI Usage</option><option value="content">Content Stats</option><option value="financial">Financial</option></select></div>
            <div><label class="ad-label">Date Range</label><select name="range" class="ad-input ad-input-select" required><option value="7d">Last 7 Days</option><option value="30d" selected>Last 30 Days</option><option value="90d">Last 90 Days</option></select></div>
            <div><button type="submit" class="ad-btn ad-btn-primary w-full"><i class="fa-solid fa-download text-xs"></i> Generate & Download</button></div>
        </form>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">History</div>
        <div class="ad-heading mb-4">Generated Reports</div>
        <div class="space-y-2">
            @foreach($reports['items'] as $report)
            <div class="ad-tile flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:rgba(34,211,238,0.08); color:var(--ad-cyan);"><i class="fa-solid fa-file-csv text-sm"></i></div>
                    <div>
                        <p class="text-sm font-semibold" style="color:var(--ad-t1)">{{ $report['name'] }}</p>
                        <p class="text-[11px]" style="color:var(--ad-t3)">{{ $report['range'] }} · {{ $report['generated_at'] }} · {{ $report['size'] }} · {{ $report['meta'] }}</p>
                    </div>
                </div>
                <button class="ad-btn ad-btn-sm ad-btn-ghost"><i class="fa-solid fa-download text-[10px]"></i></button>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
 $('reportForm')?.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Generating…';
    setTimeout(() => { toast('Report generated successfully', 'success'); btn.disabled = false; btn.innerHTML = orig; }, 1500);
});
</script>
@endsection