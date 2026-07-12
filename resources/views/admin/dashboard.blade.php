@extends('admin.layout')
@section('title', 'Dashboard | SHEELEARN')
@section('page_title', 'Dashboard')
@section('page_breadcrumb', 'Overview')

@section('content')
<div class="space-y-5">
    @php
    $dashboardService = app(\App\Services\AdminDashboardService::class);
    $m = $dashboardService->getMetrics();
    $registrationTrend = $dashboardService->getRegistrationTrend(7);
@endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="ad-card ad-stat ad-card-lift">
            <div class="flex items-start justify-between">
                <div>
                    <div class="ad-stat-label">Total Users</div>
                    <div class="ad-stat-value" data-anim="{{ $m['users'] }}">{{ number_format($m['users']) }}</div>
                </div>
                <div class="ad-stat-icon" style="background:rgba(34,211,238,0.08); color:var(--ad-cyan);"><i class="fa-solid fa-users"></i></div>
            </div>
            <div class="ad-stat-trend up"><i class="fa-solid fa-arrow-up text-[9px]"></i> Active</div>
        </div>
        <div class="ad-card ad-stat ad-card-lift">
            <div class="flex items-start justify-between">
                <div>
                    <div class="ad-stat-label">New Today</div>
                    <div class="ad-stat-value" data-anim="{{ $m['new_users_today'] }}">{{ number_format($m['new_users_today']) }}</div>
                </div>
                <div class="ad-stat-icon" style="background:rgba(52,211,153,0.08); color:var(--ad-emerald);"><i class="fa-solid fa-user-plus"></i></div>
            </div>
            <div class="ad-stat-trend {{ $m['new_users_today'] > 0 ? 'up' : 'neutral' }}"><i class="fa-solid fa-arrow-{{ $m['new_users_today'] > 0 ? 'up' : 'minus' }} text-[9px]"></i> {{ $m['new_users_today'] }} today</div>
        </div>
        <div class="ad-card ad-stat ad-card-lift">
            <div class="flex items-start justify-between">
                <div>
                    <div class="ad-stat-label">AI Chats</div>
                    <div class="ad-stat-value" data-anim="{{ $m['ai_conversations'] }}">{{ number_format($m['ai_conversations']) }}</div>
                </div>
                <div class="ad-stat-icon" style="background:rgba(129,140,248,0.08); color:var(--ad-indigo);"><i class="fa-solid fa-robot"></i></div>
            </div>
            <div class="ad-stat-trend {{ $m['ai_conversations'] > 0 ? 'up' : 'neutral' }}"><i class="fa-solid fa-signal text-[9px]"></i> {{ $m['ai_conversations'] > 0 ? 'Live' : 'No data' }}</div>
        </div>
        <div class="ad-card ad-stat ad-card-lift">
            <div class="flex items-start justify-between">
                <div>
                    <div class="ad-stat-label">Documents</div>
                    <div class="ad-stat-value" data-anim="{{ $m['documents'] }}">{{ number_format($m['documents']) }}</div>
                </div>
                <div class="ad-stat-icon" style="background:rgba(251,191,36,0.08); color:var(--ad-amber);"><i class="fa-solid fa-file-lines"></i></div>
            </div>
            <div class="ad-stat-trend neutral"><i class="fa-solid fa-database text-[9px]"></i> {{ $m['storage_used'] ?? '0 MB' }}</div>
        </div>
    </div>

    <div class="grid gap-5 2xl:grid-cols-[1.4fr_0.8fr]">
        {{-- Chart --}}
        <div class="ad-card p-5">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <div class="ad-eyebrow">Growth</div>
                    <div class="ad-heading">User Registrations</div>
                </div>
                <div class="ad-badge ad-badge-cyan"><span class="ad-dot" style="background:var(--ad-cyan); box-shadow:0 0 6px rgba(34,211,238,0.5);"></span> Live</div>
            </div>
            <div style="height:280px;"><canvas id="growthChart"></canvas></div>
        </div>

        {{-- Right stack --}}
        <div class="space-y-5">
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Actions</div>
                <div class="ad-heading mb-4">Quick Access</div>
                <div class="space-y-2">
                    <a href="{{ route('admin.announcements') }}" class="ad-action"><span class="flex items-center gap-2.5"><i class="fa-solid fa-bullhorn text-xs" style="color:var(--ad-cyan)"></i> Create Announcement</span><i class="fa-solid fa-arrow-right arrow"></i></a>
                    <a href="{{ route('admin.users') }}" class="ad-action"><span class="flex items-center gap-2.5"><i class="fa-solid fa-users text-xs" style="color:var(--ad-indigo)"></i> Manage Users</span><i class="fa-solid fa-arrow-right arrow"></i></a>
                    <a href="{{ route('admin.reports') }}" class="ad-action"><span class="flex items-center gap-2.5"><i class="fa-solid fa-flag text-xs" style="color:var(--ad-emerald)"></i> View Reports</span><i class="fa-solid fa-arrow-right arrow"></i></a>
                    <a href="{{ route('admin.settings') }}" class="ad-action"><span class="flex items-center gap-2.5"><i class="fa-solid fa-gear text-xs" style="color:var(--ad-amber)"></i> System Settings</span><i class="fa-solid fa-arrow-right arrow"></i></a>
                </div>
            </div>
            <div class="ad-card p-5">
                <div class="ad-eyebrow">AI</div>
                <div class="ad-heading mb-4">Monitoring</div>
                <div class="space-y-2">
                    <div class="ad-tile flex items-center justify-between"><span class="text-xs" style="color:var(--ad-t2)">Total Requests</span><span class="text-sm font-bold" style="color:var(--ad-t1)">{{ number_format($m['ai_conversations']) }}</span></div>
                    <div class="ad-tile flex items-center justify-between"><span class="text-xs" style="color:var(--ad-t2)">Avg. Session</span><span class="text-sm font-bold" style="color:var(--ad-t1)">{{ $m['avg_session_time'] ?? '—' }}</span></div>
                    <div class="ad-tile flex items-center justify-between"><span class="text-xs" style="color:var(--ad-t2)">Storage</span><span class="text-sm font-bold" style="color:var(--ad-emerald)">{{ $m['storage_used'] ?? '0 MB' }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 2xl:grid-cols-[1.2fr_0.8fr]">
        {{-- Activity Table --}}
        <div class="ad-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="ad-eyebrow">Log</div>
                    <div class="ad-heading">Recent Activity</div>
                </div>
                <div class="ad-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="ad-input !py-1.5 !text-xs w-40" placeholder="Filter…" id="actSearch" data-table-search="actTable">
                </div>
            </div>
            <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
                <table class="ad-table" id="actTable">
                    <thead><tr><th>User</th><th>Action</th><th>Date</th><th>Status</th><th>IP</th></tr></thead>
                    <tbody>
                        @foreach(app(\App\Services\AdminDashboardService::class)->getRecentActivity() as $a)
                        <tr>
                            <td class="font-medium" style="color:var(--ad-t1)">{{ $a['user'] }}</td>
                            <td>{{ $a['action'] }}</td>
                            <td>{{ $a['date'] }}</td>
                            <td><span class="ad-badge {{ $a['status']==='warning' ? 'ad-badge-amber' : 'ad-badge-green' }}">{{ ucfirst($a['status']) }}</span></td>
                            <td class="font-mono text-xs" style="color:var(--ad-t3)">{{ $a['ip'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Users --}}
        <div class="ad-card p-5">
            <div class="ad-eyebrow">People</div>
            <div class="ad-heading mb-4">Recent Sign-ups</div>
            <div class="space-y-2 max-h-[340px] overflow-y-auto ad-scroll pr-1">
                @php $recentUsers = app(\App\Services\AdminDashboardService::class)->getRecentUsers(); @endphp
                @forelse($recentUsers as $u)
                <div class="ad-tile">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0" style="background:rgba(34,211,238,0.08); color:var(--ad-cyan);">{{ strtoupper(substr($u['name'] ?? '?', 0, 1)) }}</div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate" style="color:var(--ad-t1)">{{ $u['name'] }}</p>
                            <p class="text-[11px] truncate" style="color:var(--ad-t3)">{{ $u['email'] }}</p>
                        </div>
                    </div>
                    <p class="text-[10px] font-semibold uppercase tracking-widest mt-2" style="color:var(--ad-cyan)">{{ $u['created_at'] }}</p>
                </div>
                @empty
                <div class="ad-tile text-center py-8">
                    <i class="fa-regular fa-user text-2xl mb-2" style="color:var(--ad-t3); opacity:0.4;"></i>
                    <p class="text-xs" style="color:var(--ad-t3)">No recent registrations</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* Animate stats */
    document.querySelectorAll('[data-anim]').forEach(el => {
        animNum(el, parseInt(el.dataset.anim));
    });

    /* Growth chart */
    const ctx = document.getElementById('growthChart');
    if (ctx) {
        const grid = 'rgba(148,163,184,0.05)';
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($registrationTrend['labels']),
                datasets: [{
                    label: 'Users',
                    data: @json($registrationTrend['values']),
                    borderColor: '#22d3ee',
                    backgroundColor: 'rgba(34,211,238,0.06)',
                    fill: true, tension: 0.4, pointRadius: 0, pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#22d3ee', pointHoverBorderColor: '#020617', pointHoverBorderWidth: 3,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(6,10,22,0.92)', titleColor: '#f1f5f9', bodyColor: '#cbd5e1', borderColor: 'rgba(148,163,184,0.1)', borderWidth: 1, padding: 10, cornerRadius: 8, titleFont: { weight: '700' } } },
                scales: {
                    x: { ticks: { font: { size: 10 }, maxTicksLimit: 10 }, grid: { color: grid, drawBorder: false } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, padding: 8 }, grid: { color: grid, drawBorder: false } }
                }
            }
        });
    }
});
</script>
@endsection