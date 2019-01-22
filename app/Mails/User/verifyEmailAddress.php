<?php

namespace App\Mails\User;

use App\Models\User;
use App\Models\SystemEmails;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class verifyEmailAddress extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $code = \str_random(32);
        $saveToDB = new SystemEmails;
        $saveToDB->user_id = $this->user->id;
        $saveToDB->code = $code;
        $saveToDB->type = 'verify-email';
        $saveToDB->expire_at = Carbon::now()->addHours(2);
        $saveToDB->save();

        $verifyUrl = route('auth.verify', ['code' => $code]);

        return $this->subject('Vérifiez votre adresse e-mail')
                    ->view('emails.user.verify-email-address')
                    ->with(['verify_url' => $verifyUrl]);
    }
}
