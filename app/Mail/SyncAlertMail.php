<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SyncAlertMail extends Mailable
{
    use Queueable, SerializesModels;
 
    public array $alertas;

    public function __construct(array $alertas)
    {
        $this->alertas = $alertas;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[AquaStaff] Alerta: estructuras sin sincronizar — ' . now()->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sync-alert',
        );
    }
}
