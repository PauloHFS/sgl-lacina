<?php

namespace App\Mail;

use App\Models\Ausencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NovaAusenciaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Ausencia $ausencia;

    /**
     * Create a new message instance.
     */
    public function __construct(Ausencia $ausencia)
    {
        $this->ausencia = $ausencia;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nova Solicitação de Ausência',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.nova-ausencia-mail',
            with: [
                'ausencia' => $this->ausencia,
            ],
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
