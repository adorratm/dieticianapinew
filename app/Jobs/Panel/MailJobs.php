<?php

namespace App\Jobs\Panel;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public array $users;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($datam, $usersm)
    {
        $this->data = $datam;
        $this->users = $usersm;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $users = $this->users;
        $key = $data["key"];
        $mail = mail::send('basvuru', ["data" => $data], function ($message) use ($users, $key) {
            $message->from('info@klinikdiyetisyen.com', 'İletişim');
            $message->subject("DİYETİSYEN DANIŞAN BAŞVURUSU");
            $message->to($users["email"]);
			
        });
    }
}
