<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Return latest notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->take(20)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => class_basename($n->type),
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at->toDateTimeString(),
            ];
        });

        $unread = $user->unreadNotifications()->count();

        return response()->json(['success' => true, 'notifications' => $notifications, 'unread' => $unread]);
    }

    /**
     * Mark a notification as read, or mark all as read.
     */
    public function markRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $id = $request->input('id');
        if ($id === 'all' || $request->input('all') === true) {
            $user->unreadNotifications->markAsRead();
            return response()->json(['success' => true, 'unread' => 0]);
        }

        if (! $id) {
            return response()->json(['success' => false, 'message' => 'Missing id'], 422);
        }

        $notif = $user->unreadNotifications()->where('id', $id)->first();
        if ($notif) {
            $notif->markAsRead();
        }

        $unread = $user->unreadNotifications()->count();
        return response()->json(['success' => true, 'unread' => $unread]);
    }
}
