@extends('admin.layout')
@section('title', 'Users | SHEELEARN')
@section('page_title', 'User Management')
@section('page_breadcrumb', 'Users')

@section('content')
<div class="space-y-5">
    <div class="ad-card p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
            <div>
                <div class="ad-eyebrow">Accounts</div>
                <div class="ad-heading">All Users <span class="ad-badge ad-badge-cyan ml-2">{{ $users->count() }}</span></div>
            </div>
            <div class="flex items-center gap-2">
                <div class="ad-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input class="ad-input !py-2 !text-xs w-52" placeholder="Search by name or email…" id="userSearch" data-table-search="usersTable">
                </div>
            </div>
        </div>
        <div class="overflow-x-auto rounded-xl" style="border:1px solid var(--ad-border);">
            <table class="ad-table" id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold flex-shrink-0" style="background:rgba(34,211,238,0.08); color:var(--ad-cyan);">{{ strtoupper(substr($user->name ?? '?', 0, 1)) }}</div>
                                <span class="font-medium" style="color:var(--ad-t1)">{{ $user->name ?? 'Unnamed' }}</span>
                            </div>
                        </td>
                        <td class="font-mono text-xs">{{ $user->email }}</td>
                        <td><span class="ad-badge {{ ($user->status ?? 'active') === 'active' ? 'ad-badge-green' : 'ad-badge-amber' }}">{{ ucfirst($user->status ?? 'active') }}</span></td>
                        <td>{{ $user->created_at?->diffForHumans() }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                    @csrf
                                    @php
                                        $userStatus = $user->status ?? 'active';
                                        $actionButtonClass = $userStatus === 'active' ? 'ad-btn-danger' : 'ad-btn-ghost';
                                        $actionIcon = $userStatus === 'active' ? 'fa-ban' : 'fa-check';
                                    @endphp
                                    <button type="submit" class="ad-btn ad-btn-sm {{ $actionButtonClass }}">
                                        <i class="fa-solid {{ $actionIcon }} text-[10px]"></i>
                                        {{ $userStatus === 'active' ? 'Suspend' : 'Activate' }}
                                    </button>
                                </form>
                                <button class="ad-btn ad-btn-sm ad-btn-ghost" onclick="openModal(`
                                    <div style='padding:20px;'>
                                        <div class='flex items-center justify-between mb-5'>
                                            <h3 style='font-size:15px;font-weight:700;color:var(--ad-t1)'>User Details</h3>
                                            <button onclick='closeModal()' class='ad-btn ad-btn-ghost !p-1.5 !px-2'><i class='fa-solid fa-xmark text-xs'></i></button>
                                        </div>
                                        <div class='space-y-3'>
                                            <div class='ad-tile'><span class='text-xs' style='color:var(--ad-t3)'>Name</span><p class='text-sm font-semibold mt-1' style='color:var(--ad-t1)'>{{ $user->name ?? '—' }}</p></div>
                                            <div class='ad-tile'><span class='text-xs' style='color:var(--ad-t3)'>Email</span><p class='text-sm font-semibold mt-1 font-mono' style='color:var(--ad-t1)'>{{ $user->email }}</p></div>
                                            <div class='ad-tile'><span class='text-xs' style='color:var(--ad-t3)'>Joined</span><p class='text-sm font-semibold mt-1' style='color:var(--ad-t1)'>{{ $user->created_at?->format('M d, Y') ?? '—' }}</p></div>
                                            <div class='ad-tile'><span class='text-xs' style='color:var(--ad-t3)'>Status</span><p class='mt-1'><span class='ad-badge {{ ($user->status ?? 'active') === 'active' ? 'ad-badge-green' : 'ad-badge-amber' }}'>{{ ucfirst($user->status ?? 'active') }}</span></p></div>
                                        </div>
                                    </div>
                                `)"><i class="fa-solid fa-eye text-xs"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8" style="color:var(--ad-t3)"><i class="fa-regular fa-users text-2xl mb-2 block opacity-40"></i>No users found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection