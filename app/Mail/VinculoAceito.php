<?php

namespace App\Mail;

use App\Models\Projeto;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VinculoAceito extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $colaborador,
        public Projeto $projeto,
        public string $url
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vínculo ao Projeto Aceito',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.vinculo-aceito',
            with: [
                'colaborador' => $this->colaborador,
                'projeto' => $this->projeto,
                'url' => $this->url,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \\Illuminate\\Mail\\Mailables\\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
