<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionDireccionEliminada extends Mailable
{
    use Queueable, SerializesModels;

    public $direccion;

    public function __construct($direccion)
    {
        $this->direccion = $direccion;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dirección Eliminada'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.NotificacionDireccionEliminada'
        );
    }
}
