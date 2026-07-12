@extends('admin.layout')
@section('title', 'AI Usage | SHEELEARN')
@section('page_title', 'AI Usage')
@section('page_breadcrumb', 'Monitoring')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($aiUsage['stats'] as $stat)
            <div class="ad-card ad-stat ad-card-lift">
                <div class="ad-stat-label">{{ $stat['label'] }}</div>
                <div class="ad-stat-value">{{ $stat['value'] }}</div>
                <div class="ad-stat-trend {{ $stat['trend']['direction'] }}"><i class="fa-solid fa-arrow-up text-[9px]"></i> {{ $stat['trend']['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="ad-card p-5">
            <div class="ad-eyebrow">Trend</div>
            <div class="ad-heading mb-4">Request Volume (7 Days)</div>
            <div style="height:260px;"><canvas id="aiVolumeChart"></canvas></div>
        </div>
        <div class="ad-card p-5">
            <div class="ad-eyebrow">Breakdown</div>
            <div class="ad-heading mb-4">Feature Usage</div>
            <div style="height:260px;"><canvas id="aiFeatureChart"></canvas></div>
        </div>
    </div>

    <div class="ad-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="ad-eyebrow">Models</div>
                <div class="ad-heading">Recent AI Activity</div>
            </div>
        </div>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach($aiUsage['models'] as $model)
                <div class="ad-tile">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold" style="color:var(--ad-t1)">{{ $model['name'] }}</span>
                        <span class="ad-badge ad-badge-green"><span class="ad-dot" style="background:var(--ad-emerald)"></span> {{ $model['status'] }}</span>
                    </div>
                    <div class="ad-progress-track"><div class="ad-progress-fill" style="width:{{ min(100, $model['usage'] * 5) }}%; background:var(--ad-cyan);"></div></div>
                    <p class="text-[11px] mt-2" style="color:var(--ad-t3)">{{ $model['usage'] }} requests · {{ $model['allowance'] === '∞' ? 'Unlimited' : $model['allowance'] . ' cap' }} · {{ $model['user'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">Usage Log</div>
        <div class="ad-heading mb-4">Recent AI Usage</div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table">
                <thead><tr><th>User</th><th>Plan</th><th>Used</th><th>Allowed</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($aiUsage['rows'] as $row)
                        <tr>
                            <td class="font-medium" style="color:var(--ad-t1)">{{ $row['user'] }}</td>
                            <td><span class="ad-badge ad-badge-indigo">{{ $row['plan'] }}</span></td>
                            <td>{{ $row['used'] }}</td>
                            <td>{{ $row['allowed'] }}</td>
                            <td><span class="ad-badge {{ $row['status'] === 'Unlimited' ? 'ad-badge-green' : 'ad-badge-amber' }}">{{ $row['status'] }}</span></td>
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
document.addEventListener('DOMContentLoaded', () => {
    const grid = 'rgba(148,163,184,0.05)';
    const tooltipStyle = { backgroundColor:'rgba(6,10,22,0.92)', titleColor:'#f1f5f9', bodyColor:'#cbd5e1', borderColor:'rgba(148,163,184,0.1)', borderWidth:1, padding:10, cornerRadius:8 };

    new Chart(document.getElementById('aiVolumeChart'), {
        type: 'bar',
        data: {
            labels: @json($aiUsage['volume']['labels']),
            datasets: [{ label:'Requests', data: @json($aiUsage['volume']['values']), backgroundColor:'rgba(34,211,238,0.5)', hoverBackgroundColor:'rgba(34,211,238,0.8)', borderRadius:8, maxBarThickness:40, borderSkipped:false }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false}, tooltip:tooltipStyle }, scales:{ x:{grid:{display:false},ticks:{font:{size:11}}}, y:{beginAtZero:true,grid:{color:grid},ticks:{font:{size:10}}} } }
    });

    new Chart(document.getElementById('aiFeatureChart'), {
        type: 'doughnut',
        data: {
            labels: @json($aiUsage['feature_breakdown']['labels']),
            datasets: [{ data: @json($aiUsage['feature_breakdown']['values']), backgroundColor:['#22d3ee','#818cf8','#34d399','#fbbf24','#fb7185'], borderWidth:0, spacing:3 }]
        },
        options: { responsive:true, cutout:'70%', plugins:{ legend:{ position:'bottom', labels:{ boxWidth:8, boxHeight:8, usePointStyle:true, pointStyle:'circle', padding:16, font:{size:11,weight:'600'} } }, tooltip:tooltipStyle } }
    });
});
</script>
@endsection