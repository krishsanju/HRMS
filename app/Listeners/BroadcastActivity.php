<?php

namespace App\Listeners;

use App\Events\ActivityLogged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BroadcastActivity
{
    public function handle(object $event): void
    {
        // The event object is the Activity model instance itself
        // because we are listening to eloquent.created
        ActivityLogged::dispatch($event);
    }
}