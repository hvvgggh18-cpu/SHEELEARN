@extends('admin.layout')
@section('title', 'Documents | SHEELEARN')
@section('page_title', 'Documents')
@section('page_breadcrumb', 'Content')

@section('content')
<div class="space-y-5">
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($documents['stats'] as $stat)
            <div class="ad-card ad-stat ad-card-lift">
                <div class="ad-stat-label">{{ $stat['label'] }}</div>
                <div class="ad-stat-value">{{ $stat['value'] }}</div>
                <div class="ad-stat-trend {{ $stat['trend']['direction'] }}"><i class="fa-solid fa-arrow-up text-[9px]"></i> {{ $stat['trend']['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="ad-card p-5">
        <div class="flex items-center justify-between mb-4">
            <div><div class="ad-eyebrow">Files</div><div class="ad-heading">Recent Uploads</div></div>
            <div class="ad-search-wrap"><i class="fa-solid fa-magnifying-glass"></i><input class="ad-input !py-1.5 !text-xs w-44" placeholder="Filter files…" id="docSearch" data-table-search="docTable"></div>
        </div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table" id="docTable">
                <thead><tr><th>File</th><th>User</th><th>Type</th><th>Size</th><th>Status</th><th>Uploaded</th></tr></thead>
                <tbody>
                    @foreach($documents['items'] as $doc)
                    <tr>
                        <td>
                            @php
                                $fileIcon = match (strtoupper($doc['type'])) {
                                    'PDF' => 'fa-file-pdf text-red-400',
                                    'DOCX' => 'fa-file-word text-blue-400',
                                    default => 'fa-file-excel text-green-400',
                                };
                                $statusClass = $doc['status'] === 'Processed' ? 'ad-badge-green' : ($doc['status'] === 'Processing' ? 'ad-badge-amber' : 'ad-badge-rose');
                            @endphp
                            <div class="flex items-center gap-2"><i class="fa-solid {{ $fileIcon }} text-sm"></i><span class="font-medium" style="color:var(--ad-t1)">{{ $doc['name'] }}</span></div>
                        </td>
                        <td>{{ $doc['user'] }}</td>
                        <td><span class="ad-badge ad-badge-indigo">{{ $doc['type'] }}</span></td>
                        <td class="font-mono text-xs">{{ $doc['size'] }}</td>
                        <td><span class="ad-badge {{ $statusClass }}">{{ $doc['status'] }}</span></td>
                        <td style="color:var(--ad-t3)">{{ $doc['time'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
