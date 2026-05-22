<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\NotificationResource;
use App\Jobs\SendPushNotificationJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);

        return $this->success(NotificationResource::collection($notifications));
    }

    public function broadcast(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string'],
            'message' => ['required', 'string'],
            'role' => ['nullable', 'string', 'exists:roles,slug'],
        ]);

        $query = User::query()->where('is_active', true);

        if ($request->role) {
            $query->whereHas('roleModel', fn ($q) => $q->where('slug', $request->role));
        }

        $users = $query->get();

        foreach ($users as $user) {
            SendPushNotificationJob::dispatch($user->id, $request->title, $request->message);
            $user->notify(new \App\Notifications\GeneralBroadcastNotification($request->title, $request->message));
        }

        return $this->success(['count' => $users->count()], 'Broadcast queued.');
    }
}
