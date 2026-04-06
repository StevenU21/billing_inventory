<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class NotificationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', DatabaseNotification::class);
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [5, 10, 15, 25, 50, 100], true) ? $perPage : 15;

        $user = $request->user();
        $baseQuery = $user->notifications()->latest();

        $notifications = QueryBuilder::for($baseQuery)
            ->allowedFilters([
                AllowedFilter::callback('status', function ($query, $value) {
                    if ($value === 'unread') {
                        $query->whereNull('read_at');
                    } elseif ($value === 'read') {
                        $query->whereNotNull('read_at');
                    }
                }),
                AllowedFilter::callback('category', function ($query, $value) {
                    if ($value !== 'all') {
                        $query->whereJsonContains('data->category', $value);
                    }
                }),
            ])
            ->defaultSort('-created_at')
            ->paginate($perPage)
            ->appends($request->query());

        // Calculate stats (still needed for view)
        $total = $user->notifications()->count();
        $unread = $user->unreadNotifications()->count();
        $stats = [
            'total' => $total,
            'unread' => $unread,
            'read' => max(0, $total - $unread),
        ];

        $filter = $request->input('filter.status', 'all');
        $category = $request->input('filter.category', 'all');

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'category' => $category,
            'perPage' => $perPage,
            'stats' => $stats,
        ]);
    }

    public function feed(Request $request)
    {
        $this->authorize('viewAny', DatabaseNotification::class);
        $limit = (int) $request->integer('limit', 10);
        $limit = max(1, min($limit, 30));

        $user = $request->user();
        $notifications = $user->notifications()->latest()->limit($limit)->get();

        return response()->json([
            'data' => $notifications->map(fn(DatabaseNotification $notification) => $this->formatNotification($notification)),
            'meta' => [
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, string $notificationId)
    {
        $notification = $this->findUserNotification($request, $notificationId);
        $this->authorize('mark', $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
            $this->updateAppBadge($request->user());
        }

        return $this->respond($request, 'Notificación marcada como leída.');
    }

    public function markAll(Request $request)
    {
        $this->authorize('markAll', DatabaseNotification::class);
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->updateAppBadge($request->user());

        return $this->respond($request, 'Todas las notificaciones fueron marcadas como leídas.');
    }

    public function destroy(Request $request, string $notificationId)
    {
        $notification = $this->findUserNotification($request, $notificationId);
        $this->authorize('delete', $notification);
        $notification->delete();
        $this->updateAppBadge($request->user());

        return $this->respond($request, 'Notificación eliminada.');
    }

    public function destroyAll(Request $request)
    {
        abort_unless($request->user()->can('destroy notifications'), 403);
        $deleted = $request->user()->notifications()->delete();
        $this->updateAppBadge($request->user());

        $message = $deleted > 0
            ? 'Todas las notificaciones fueron eliminadas.'
            : 'No hay notificaciones para eliminar.';

        return $this->respond($request, $message);
    }

    private function updateAppBadge($user)
    {
        if (class_exists(\Native\Desktop\Facades\Window::class)) {
            try {
                $count = $user->unreadNotifications()->count();
                \Native\Desktop\Facades\Window::setBadgeCount($count);
            } catch (\Throwable $e) {
                // Ignorar error si no estamos en entorno NativePHP
            }
        }
    }

    private function findUserNotification(Request $request, string $notificationId): DatabaseNotification
    {
        return $request->user()->notifications()->where('id', $notificationId)->firstOrFail();
    }

    private function formatNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'unknown',
            'category' => $data['category'] ?? 'general',
            'icon' => $data['icon'] ?? 'fa-bell',
            'title' => $data['title'] ?? 'Notificación',
            'message' => $data['message'] ?? null,
            'read_at' => optional($notification->read_at)->toIso8601String(),
            'occurred_at' => $data['occurred_at'] ?? optional($notification->created_at)->toIso8601String(),
            'url' => $data['url'] ?? null,
            'data' => $data,
        ];
    }

    private function respond(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }
}
