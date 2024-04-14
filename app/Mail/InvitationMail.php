<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $email;
    public $company;
    public $resetLink;
    /**
     * Create a new message instance.
     */
    public function __construct(string $name,string $email,string $company,string $resetLink)
    {
        $this->name = $name;
        $this->email = $email;
        $this->company=$company;
        $this->resetLink=$resetLink;
    }
  


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('example@example.com', 'Test Sender'),
            subject: 'Company Admin Invitation Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'admin_invitation_email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
