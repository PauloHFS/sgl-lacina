<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ParticipacaoLacinaReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;

    public Collection $historico;

    public ?string $pdfContent;

    public ?string $pdfFilename;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Collection $historico, ?string $pdfContent = null, ?string $pdfFilename = null)
    {
        $this->user = $user;
        $this->historico = $historico;
        $this->pdfContent = $pdfContent;
        $this->pdfFilename = $pdfFilename ?? 'relatorio_participacao.pdf';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relatório de Participação no LACINA',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reports.participacao',
            with: [
                'user' => $this->user,
                'historico' => $this->historico,
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
        $attachments = [];
        if ($this->pdfContent) {
            $attachments[] = Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
