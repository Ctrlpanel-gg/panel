<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate();

        return view('notifications.index')->with([
            'notifications' => $notifications
        ]);
    }

    /** Display the specified resource. */
    public function show(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);

        $notification->markAsRead();
        return view('notifications.show')->with([
            'notification' => $notification
        ]);
    }
}
