<?php

declare(strict_types=1);

namespace App\Mail;

use App\Dto\UserCreateDto;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreateMail extends Mailable
{
    use Queueable;
    use SerializesModels;
    public $from_email;
    public $site_title;
    public $subject;
    public UserCreateDto $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_from, $subject, UserCreateDto $user)
    {
        $basic = (object) config('basic');
        $this->from_email = $email_from;
        $this->site_title = $basic->site_title;
        $this->subject = $subject;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return @$this->from(env('MAIL_USERNAME'), 'Babama.ru')->view('mail.user.create')->with('user', $this->user);
    }
}
