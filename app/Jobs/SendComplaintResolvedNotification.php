<?php

namespace App\Jobs;

use App\Mail\ComplaintResolvedMail;
use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendComplaintResolvedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $complaint;

    /**
     * Create a new job instance.
     */
    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->complaint->load(['customer', 'technician']);
        
        Mail::to($this->complaint->customer->email)
            ->send(new ComplaintResolvedMail($this->complaint));
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Failed to send complaint resolved notification: ' . $exception->getMessage());
    }
}
