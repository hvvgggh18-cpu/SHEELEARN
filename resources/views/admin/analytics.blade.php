@extends('admin.layout')
@section('title', 'Analytics | SHEELEARN')
@section('page_title', 'Platform Analytics')
@section('page_breadcrumb', 'Insights')

@section('content')
<div class="space-y-5">
    <div class="ad-tabs mb-2">
        <button class="ad-tab active" data-at="overview">Overview</button>
        <button class="ad-tab" data-at="features">Features</button>
        <button class="ad-tab" data-at="retention">Retention</button>
    </div>

    <div class="ad-tab-panel active" id="at-overview">
        <div class="grid gap-5 xl:grid-cols-2">
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Growth</div>
                <div class="ad-heading mb-4">Registration Trends</div>
                <div style="height:250px;"><canvas id="regTrendChart"></canvas></div>
            </div>
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Usage</div>
                <div class="ad-heading mb-4">Daily Active Users</div>
                <div style="height:250px;"><canvas id="dauChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="ad-tab-panel" id="at-features">
        <div class="grid gap-5 xl:grid-cols-2">
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Popularity</div>
                <div class="ad-heading mb-4">Most Used Features</div>
                <div style="height:250px;"><canvas id="featurePopChart"></canvas></div>
            </div>
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Tools</div>
                <div class="ad-heading mb-4">Study Tool Distribution</div>
                <div style="height:250px;"><canvas id="toolDistChart"></canvas></div>
            </div>
        </div>
    </div>

    <div class="ad-tab-panel" id="at-retention">
        <div class="grid gap-5 xl:grid-cols-2">
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Retention</div>
                <div class="ad-heading mb-4">User Retention (Weekly Cohorts)</div>
                <div style="height:250px;"><canvas id="retentionChart"></canvas></div>
            </div>
            <div class="ad-card p-5">
                <div class="ad-eyebrow">Completion</div>
                <div class="ad-heading mb-4">Task Completion Rate</div>
                <div style="height:250px;"><canvas id="completionChart"></canvas></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    /* Tabs */
    $$('.ad-tab[data-at]').forEach(tab => {
        tab.addEventListener('click', () => {
            $$('.ad-tab[data-at]').forEach(t => t.classList.remove('active'));
            $$('.ad-tab-panel[id^="at-"]').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            $('at-' + tab.dataset.at)?.classList.add('active');
        });
    });

    const grid = 'rgba(148,163,184,0.05)';
    const tt = { backgroundColor:'rgba(6,10,22,0.92)', titleColor:'#f1f5f9', bodyColor:'#cbd5e1', borderColor:'rgba(148,163,184,0.1)', borderWidth:1, padding:10, cornerRadius:8 };
    const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    const rnd = (n=30) => Array.from({length:7}, () => Math.floor(Math.random()*n+n));

    const lineOpts = (extra={}) => ({ responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, plugins:{legend:{display:false},tooltip:tt}, scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,grid:{color:grid},ticks:{font:{size:10}}}}, ...extra });

    new Chart($('regTrendChart'), { type:'line', data:{labels:days,datasets:[{data:rnd(40),borderColor:'#22d3ee',backgroundColor:'rgba(34,211,238,0.06)',fill:true,tension:0.4,pointRadius:3,borderWidth:2}]}, options:lineOpts() });
    new Chart($('dauChart'), { type:'line', data:{labels:days,datasets:[{data:rnd(120),borderColor:'#818cf8',backgroundColor:'rgba(129,140,248,0.06)',fill:true,tension:0.4,pointRadius:3,borderWidth:2}]}, options:lineOpts() });
    new Chart($('featurePopChart'), { type:'bar', data:{labels:['AI Chat','Flashcards','Quizzes','Documents','Planner'],datasets:[{data:[420,280,210,150,90],backgroundColor:['rgba(34,211,238,0.6)','rgba(129,140,248,0.6)','rgba(52,211,153,0.6)','rgba(251,191,36,0.6)','rgba(251,113,133,0.6)'],borderRadius:8,maxBarThickness:36,borderSkipped:false}]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:tt},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{beginAtZero:true,grid:{color:grid},ticks:{font:{size:10}}}}} });
    new Chart($('toolDistChart'), { type:'doughnut', data:{labels:['AI Chat','Flashcards','Quizzes','Documents','Planner'],datasets:[{data:[42,24,18,10,6],backgroundColor:['#22d3ee','#818cf8','#34d399','#fbbf24','#fb7185'],borderWidth:0,spacing:3}]}, options:{responsive:true,cutout:'68%',plugins:{legend:{position:'bottom',labels:{boxWidth:8,boxHeight:8,usePointStyle:true,pointStyle:'circle',padding:14,font:{size:10,weight:'600'}}},tooltip:tt}} });
    new Chart($('retentionChart'), { type:'line', data:{labels:['Week 1','Week 2','Week 3','Week 4','Week 5','Week 6'],datasets:[{label:'Cohort A',data:[100,72,58,45,38,32],borderColor:'#22d3ee',tension:0.4,pointRadius:4,borderWidth:2},{label:'Cohort B',data:[100,68,52,40,33,28],borderColor:'#818cf8',tension:0.4,pointRadius:4,borderWidth:2}]}, options:lineOpts({plugins:{legend:{position:'top',labels:{boxWidth:8,boxHeight:8,usePointStyle:true,pointStyle:'circle',padding:16,font:{size:10,weight:'600'},color:'#94a3b8'}},tooltip:tt}}) });
    new Chart($('completionChart'), { type:'bar', data:{labels:['Quizzes','Flashcards','Documents','Planner Goals'],datasets:[{label:'Completed',data:[78,65,82,54],backgroundColor:'rgba(52,211,153,0.5)',borderRadius:6,maxBarThickness:32,borderSkipped:false},{label:'Abandoned',data:[22,35,18,46],backgroundColor:'rgba(251,113,133,0.3)',borderRadius:6,maxBarThickness:32,borderSkipped:false}]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{boxWidth:8,boxHeight:8,usePointStyle:true,pointStyle:'circle',padding:16,font:{size:10,weight:'600'},color:'#94a3b8'}},tooltip:tt},scales:{x:{stacked:true,grid:{display:false},ticks:{font:{size:10}}},y:{stacked:true,grid:{color:grid},ticks:{font:{size:10},callback:v=>v+'%'}}}} });
});
</script>
@endsection