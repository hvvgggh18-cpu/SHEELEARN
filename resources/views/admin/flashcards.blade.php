@extends('admin.layout')
@section('title', 'Flashcards | SHEELEARN')
@section('page_title', 'Flashcards')
@section('page_breadcrumb', 'Content')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($flashcards['stats'] as $stat)
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
            <div class="ad-heading mb-4">Cards Created (7 Days)</div>
            <div style="height:240px;"><canvas id="fcTrendChart"></canvas></div>
        </div>
        <div class="ad-card p-5">
            <div class="ad-eyebrow">Sources</div>
            <div class="ad-heading mb-4">Deck Activity</div>
            <div style="height:240px;"><canvas id="fcSourceChart"></canvas></div>
        </div>
    </div>

    <div class="ad-card p-5">
        <div class="ad-eyebrow">Decks</div>
        <div class="ad-heading mb-4">Most Popular Decks</div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table">
                <thead><tr><th>Deck Name</th><th>Creator</th><th>Cards</th><th>Reviews</th><th>Created</th></tr></thead>
                <tbody>
                    @foreach($flashcards['decks'] as $deck)
                    <tr>
                        <td class="font-medium" style="color:var(--ad-t1)"><i class="fa-solid fa-clone mr-2 text-xs" style="color:var(--ad-indigo)"></i>{{ $deck['title'] }}</td>
                        <td>{{ $deck['creator'] }}</td>
                        <td>{{ $deck['cards'] }}</td>
                        <td>{{ $deck['reviews'] }}</td>
                        <td style="color:var(--ad-t3)">{{ $deck['created_at'] }}</td>
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
    new Chart($('fcTrendChart'), { type:'bar', data:{labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],datasets:[{data:[620,780,850,910,870,540,640],backgroundColor:'rgba(129,140,248,0.5)',hoverBackgroundColor:'rgba(129,140,248,0.8)',borderRadius:8,maxBarThickness:36,borderSkipped:false}]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:tt},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,grid:{color:grid},ticks:{font:{size:10}}}}} });
    new Chart($('fcSourceChart'), { type:'doughnut', data:{labels:['AI Generated','Manual','Imported'],datasets:[{data:[67,22,11],backgroundColor:['#818cf8','#22d3ee','#34d399'],borderWidth:0,spacing:3}]}, options:{responsive:true,cutout:'68%',plugins:{legend:{position:'bottom',labels:{boxWidth:8,boxHeight:8,usePointStyle:true,pointStyle:'circle',padding:16,font:{size:11,weight:'600'}}},tooltip:tt}} });
});
</script>
@endsection