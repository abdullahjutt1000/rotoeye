<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWrongDateTimeStringsEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        Mail::send('emails.wrong-date-time-strings', $data, function ($message) use ($data) {
            $message->from('systems.services@packages.com.pk', 'RotoEye Cloud');
            $message->to('ameer.hamza@packages.com.pk', 'Ameer Hamza')
                ->to('a4ashraf23@gmail.com', 'Ashraf Wali')
                ->cc('mahhnoor.pasha@packages.com.pk', 'Mahnoor Pasha')
                ->cc('haroon.naseer@packages.com.pk', 'Haroon Naseer')
                ->subject("RotoEye Cloud - Wrong Date & Time Strings");
        });
    }
}
