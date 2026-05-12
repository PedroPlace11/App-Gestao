<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo(a) ao sistema Inovcorp',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome_user',
            with: [
                'user'           => $this->user,
                'plainPassword'  => $this->plainPassword,
                'loginUrl'       => url('/login'),
            ],
        );
    }
}
