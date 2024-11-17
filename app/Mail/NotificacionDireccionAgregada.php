<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionDireccionAgregada extends Mailable
{
    use Queueable, SerializesModels;

    public $direccion;

    /**
     * Create a new message instance.
     */
    public function __construct($direccion)
    {
        $this->direccion = $direccion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Dirección Agregada'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.NotificacionDireccionAgregada'
        );
    }
}
