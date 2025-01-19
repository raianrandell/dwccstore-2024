<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserLog;
use Illuminate\Support\Facades\Request;


class LogSuccessfulLogin
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
        UserLog::create([
            'user_id' => $event->user->id,
            'activity' => 'Active Now',
            'ip_address' => Request::ip(),
        ]);
    }
}
