/**
 * Notifications Page JavaScript
 * Handles mark-as-read functionality and navigation
 */

$(document).ready(function() {
    // Mark single notification as read
    $('.btn-mark-read').on('click', function(e) {
        e.stopPropagation();
        var btn = $(this);
        var id = btn.data('id');
        var item = btn.closest('.notification-page-item');

        $.ajax({
            url: '/notifications/' + id + '/read',
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                item.removeClass('unread').addClass('read');
                btn.fadeOut(200);
            }
        });
    });

    // Mark all as read
    $('#markAllReadPage').on('click', function() {
        var btn = $(this);
        $.ajax({
            url: '/notifications/mark-all-read',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function() {
                $('.notification-page-item.unread').removeClass('unread').addClass('read');
                $('.btn-mark-read').fadeOut(200);
                btn.fadeOut(200);
            }
        });
    });

    // Click notification to navigate
    $('.notification-page-item').on('click', function(e) {
        if ($(e.target).closest('.btn-mark-read').length) return;
        var link = $(this).data('link');
        var id = $(this).data('id');
        var item = $(this);

        if (!item.hasClass('read')) {
            $.ajax({
                url: '/notifications/' + id + '/read',
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            });
        }

        if (link) {
            window.location.href = link;
        }
    });
});
