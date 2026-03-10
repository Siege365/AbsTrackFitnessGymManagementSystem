@extends('layouts.admin')

@section('title', 'Notifications - AbsTrack Fitness Gym')

@push('styles')
@vite(['resources/css/notification-bell.css', 'resources/css/notifications-page.css'])
@endpush

@section('content')

<!-- Page Header -->
<div class="card page-header-card">
    <div class="card-body">
        <div>
            <h2 class="page-header-title">Notifications</h2>
            <p class="page-header-subtitle">Stay updated with important activities and alerts.</p>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="notifications-stats">
    <div class="notif-stat-card unread">
        <div class="stat-value">{{ $unreadCount }}</div>
        <div class="stat-label">Unread Notifications</div>
    </div>
    <div class="notif-stat-card">
        <div class="stat-value">{{ $notifications->total() }}</div>
        <div class="stat-label">Total Notifications</div>
    </div>
</div>

<!-- Notifications Card -->
<div class="notifications-card">
    <!-- Toolbar -->
    <div class="notifications-toolbar">
        <div class="notification-filters">
            <a href="{{ route('notifications.page') }}" class="filter-btn {{ !request('filter') && !request('type') ? 'active' : '' }}">All</a>
            <a href="{{ route('notifications.page', ['filter' => 'unread']) }}" class="filter-btn {{ request('filter') === 'unread' ? 'active' : '' }}">Unread</a>
            <a href="{{ route('notifications.page', ['filter' => 'read']) }}" class="filter-btn {{ request('filter') === 'read' ? 'active' : '' }}">Read</a>
            @foreach($types as $type)
                <a href="{{ route('notifications.page', ['type' => $type]) }}" class="filter-btn {{ request('type') === $type ? 'active' : '' }}">{{ ucfirst($type) }}</a>
            @endforeach
        </div>
        @if($unreadCount > 0)
            <button class="btn-mark-all" id="markAllReadPage">
                <i class="mdi mdi-check-all"></i> Mark all as read
            </button>
        @endif
    </div>

    <!-- Notification List -->
    <div class="notifications-page-list">
        @forelse($notifications as $notification)
            <div class="notification-page-item {{ $notification->is_read ? 'read' : 'unread' }}" data-id="{{ $notification->id }}" data-link="{{ $notification->link }}">
                <div class="notification-page-icon">
                    @php
                        $iconBg = match($notification->color) {
                            'success' => 'rgba(0, 210, 91, 0.15)',
                            'info' => 'rgba(136, 98, 224, 0.15)',
                            'warning' => 'rgba(255, 193, 7, 0.15)',
                            'danger' => 'rgba(252, 66, 74, 0.15)',
                            default => 'rgba(136, 98, 224, 0.15)',
                        };
                        $iconColor = match($notification->color) {
                            'success' => '#00d25b',
                            'info' => '#8862e0',
                            'warning' => '#ffc107',
                            'danger' => '#fc424a',
                            default => '#8862e0',
                        };
                    @endphp
                    <div style="background: {{ $iconBg }}; width: 44px; height: 44px; min-width: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                        <i class="mdi {{ $notification->icon }}" style="color: {{ $iconColor }}; font-size: 1.25rem;"></i>
                    </div>
                </div>
                <div class="notification-page-content">
                    <div class="d-flex align-items-center" style="gap: 0.5rem; margin-bottom: 0.125rem;">
                        <span class="notification-page-title">{{ $notification->title }}</span>
                        <span class="type-badge type-{{ $notification->type }}">{{ $notification->type }}</span>
                    </div>
                    <div class="notification-page-message">{{ $notification->message }}</div>
                    <div class="notification-page-time">{{ $notification->created_at->diffForHumans() }} &middot; {{ $notification->created_at->format('M d, Y h:i A') }}</div>
                </div>
                <div class="notification-page-actions">
                    @if(!$notification->is_read)
                        <button class="btn-mark-read" data-id="{{ $notification->id }}" title="Mark as read">
                            <i class="mdi mdi-check"></i>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="notifications-empty-page">
                <i class="mdi mdi-bell-check-outline"></i>
                <h5>No notifications found</h5>
                <p>
                    @if(request('filter') || request('type'))
                        Try changing your filter to see more notifications.
                    @else
                        You're all caught up! New notifications will appear here.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="pagination-wrapper">
            {{ $notifications->appends(request()->query())->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
@vite(['resources/js/notifications/page.js'])
@endpush
