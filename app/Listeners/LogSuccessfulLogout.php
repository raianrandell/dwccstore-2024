<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserLog;
use Illuminate\Support\Facades\Request;


class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event->user) { // Ensure the user object exists
            UserLog::create([
                'user_id' => $event->user->id,
                'activity' => 'Offline at ' . now()->format('h:i:s a'),
                'ip_address' => Request::ip(),
            ]);
        }
    }
}
