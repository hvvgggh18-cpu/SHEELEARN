@extends('admin.layout')
@section('title', 'Quizzes | SHEELEARN')
@section('page_title', 'Quizzes')
@section('page_breadcrumb', 'Content')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($quizzes['stats'] as $stat)
            <div class="ad-card ad-stat ad-card-lift">
                <div class="ad-stat-label">{{ $stat['label'] }}</div>
                <div class="ad-stat-value">{{ $stat['value'] }}</div>
                <div class="ad-stat-trend {{ $stat['trend']['direction'] }}"><i class="fa-solid fa-arrow-up text-[9px]"></i> {{ $stat['trend']['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="ad-card p-5">
            <div class="ad-eyebrow">Volume</div>
            <div class="ad-heading mb-4">Quizzes Generated (7 Days)</div>
            <div style="height:240px;"><canvas id="quizTrendChart"></canvas></div>
        </div>
        <div class="ad-card p-5">
            <div class="ad-eyebrow">Scores</div>
            <div class="ad-heading mb-4">Recent Quiz Performance</div>
            <div style="height:240px;"><canvas id="quizScoreChart"></canvas></div>
        </div>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">Recent</div>
        <div class="ad-heading mb-4">Latest Quiz Sessions</div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table">
                <thead><tr><th>Topic</th><th>User</th><th>Questions</th><th>Score</th><th>Time</th></tr></thead>
                <tbody>
                    @foreach($quizzes['items'] as $quiz)
                    <tr>
                        <td class="font-medium" style="color:var(--ad-t1)"><i class="fa-solid fa-circle-question mr-2 text-xs" style="color:var(--ad-emerald)"></i>{{ $quiz['subject'] }}</td>
                        <td>{{ $quiz['user'] }}</td>
                        <td>{{ $quiz['questions'] }}</td>
                        <td>
                            @php $score = $quiz['score']; $scoreClass = str_contains($score, '8') || str_contains($score, '9') ? 'ad-badge-green' : (str_contains($score, '7') ? 'ad-badge-cyan' : 'ad-badge-amber'); @endphp
                            <span class="ad-badge {{ $scoreClass }}">{{ $quiz['score'] }}</span>
                        </td>
                        <td class="font-mono text-xs" style="color:var(--ad-t3)">{{ $quiz['time'] }}</td>
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
    const tt = { backgroundColor:'rgba(6,10,22,0.92)', titleColor:'#f1f5f9', bodyColor:'#cbd5e1', borderColor:'rgba(148,163,184,0.1)', borderWidth:1, padding:10, cornerRadius:8 };
    new Chart($('quizTrendChart'), { type:'line', data:{labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],datasets:[{data:[{{ count($quizzes['items']) }},{{ count($quizzes['items']) + 1 }},{{ count($quizzes['items']) + 2 }},{{ count($quizzes['items']) + 3 }},{{ count($quizzes['items']) + 4 }},{{ count($quizzes['items']) + 5 }},{{ count($quizzes['items']) + 6 }}],borderColor:'#34d399',backgroundColor:'rgba(52,211,153,0.06)',fill:true,tension:0.4,pointRadius:3,borderWidth:2}]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:tt},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,grid:{color:grid},ticks:{font:{size:10}}}}} });
    new Chart($('quizScoreChart'), { type:'bar', data:{labels:['0-20%','21-40%','41-60%','61-80%','81-100%'],datasets:[{data:[5,10,20,30,{{ count($quizzes['items']) }}],backgroundColor:['rgba(251,113,133,0.5)','rgba(251,191,36,0.5)','rgba(251,146,60,0.5)','rgba(34,211,238,0.5)','rgba(52,211,153,0.5)'],borderRadius:6,maxBarThickness:40,borderSkipped:false}]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:tt},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,grid:{color:grid},ticks:{font:{size:10}}}}} });
});
</script>
@endsection