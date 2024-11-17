<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionDireccionPredeterminada extends Mailable
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
            subject: 'Dirección Predeterminada Seleccionada'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.NotificacionDireccionPredeterminada'
        );
    }
}
