<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ComplaintStatusUpdated;
use App\Jobs\SendComplaintResolvedNotification;
use App\Models\Complaint;

class SendComplaintResolvedEmail
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
        if ($event->newStatus === Complaint::STATUS_RESOLVED && 
            $event->oldStatus !== Complaint::STATUS_RESOLVED) {
            SendComplaintResolvedNotification::dispatch($event->complaint);
        }
    }
}
