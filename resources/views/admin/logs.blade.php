@extends('admin.layout')
@section('title', 'Activity Logs | SHEELEARN')
@section('page_title', 'Activity Logs')
@section('page_breadcrumb', 'System')

@section('content')
<div class="space-y-5">
    <div class="ad-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div><div class="ad-eyebrow">Audit</div><div class="ad-heading">System Activity</div></div>
            <div class="flex items-center gap-2">
                <div class="ad-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input class="ad-input !py-1.5 !text-xs w-44" placeholder="Filter logs…" id="logSearch" data-table-search="logTable"></div>
                <select class="ad-input ad-input-select !w-36 !py-1.5 !text-xs" id="logTypeFilter">
                    <option value="">All Types</option>
                    <option value="auth">Auth</option>
                    <option value="admin">Admin</option>
                    <option value="system">System</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table" id="logTable">
                <thead><tr><th>Actor</th><th>Action</th><th>Type</th><th>Date</th><th>IP</th></tr></thead>
                <tbody>
                    @foreach($logs['items'] as $log)
                    <tr data-type="{{ $log['type'] }}">
                        <td class="font-medium" style="color:var(--ad-t1)">
                            @php
                                $iconClass = match ($log['type']) {
                                    'auth' => 'fa-key',
                                    'system' => 'fa-server',
                                    default => 'fa-shield-halved',
                                };
                                $iconColor = match ($log['type']) {
                                    'auth' => 'var(--ad-amber)',
                                    'system' => 'var(--ad-indigo)',
                                    default => 'var(--ad-cyan)',
                                };
                                $badgeClass = match ($log['type']) {
                                    'auth' => 'ad-badge-amber',
                                    'system' => 'ad-badge-indigo',
                                    default => 'ad-badge-cyan',
                                };
                            @endphp
                            <i class="fa-solid {{ $iconClass }} mr-2 text-xs" style="color:{{ $iconColor }}"></i>{{ $log['actor'] }}
                        </td>
                        <td>{{ $log['action'] }}</td>
                        <td><span class="ad-badge {{ $badgeClass }}">{{ ucfirst($log['type']) }}</span></td>
                        <td style="color:var(--ad-t3)">{{ $log['date'] }}</td>
                        <td class="font-mono text-xs" style="color:var(--ad-t3)">{{ $log['ip'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
 $('logTypeFilter')?.addEventListener('change', function() {
    const v = this.value;
    document.querySelectorAll('#logTable tbody tr').forEach(row => {
        row.style.display = !v || row.dataset.type === v ? '' : 'none';
    });
});
</script>
@endsection