<?php

namespace App\Providers;

use App\Events\ComplaintStatusUpdated;
use App\Listeners\SendComplaintResolvedEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ComplaintStatusUpdated::class => [
            SendComplaintResolvedEmail::class,
        ],
    ];

    public function boot()
    {
        //
    }

    public function shouldDiscoverEvents()
    {
        return false;
    }
}