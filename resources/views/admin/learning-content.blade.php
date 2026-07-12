@extends('admin.layout')
@section('title', 'Learning Content | SHEELEARN')
@section('page_title', 'Learning Content')
@section('page_breadcrumb', 'Content')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($content['stats'] as $stat)
            <div class="ad-card ad-stat ad-card-lift">
                <div class="ad-stat-label">{{ $stat['label'] }}</div>
                <div class="ad-stat-value">{{ $stat['value'] }}</div>
                @if(! empty($stat['trend']))
                    <div class="ad-stat-trend {{ $stat['trend']['direction'] }}"><i class="fa-solid fa-arrow-up text-[9px]"></i> {{ $stat['trend']['label'] }}</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="ad-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div><div class="ad-eyebrow">Library</div><div class="ad-heading">Content Overview</div></div>
            <div class="ad-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input class="ad-input !py-1.5 !text-xs w-44" placeholder="Search content…" id="lcSearch" data-table-search="lcTable"></div>
        </div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table" id="lcTable">
                <thead><tr><th>Title</th><th>Type</th><th>User</th><th>Status</th><th>Size</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($content['items'] as $item)
                    @php
                        $statusClass = $item['status'] === 'processed' ? 'ad-badge-green' : ($item['status'] === 'processing' ? 'ad-badge-amber' : 'ad-badge-rose');
                    @endphp
                    <tr>
                        <td class="font-medium" style="color:var(--ad-t1)">{{ $item['title'] }}</td>
                        <td><span class="ad-badge ad-badge-indigo">{{ $item['type'] }}</span></td>
                        <td>{{ $item['user'] }}</td>
                        <td><span class="ad-badge {{ $statusClass }}">{{ ucfirst($item['status']) }}</span></td>
                        <td class="font-mono text-xs">{{ $item['size'] }}</td>
                        <td style="color:var(--ad-t3)">{{ $item['date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection