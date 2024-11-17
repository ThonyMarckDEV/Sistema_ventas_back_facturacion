<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionPagoCompletado extends Mailable
{
    use Queueable, SerializesModels;

    public $nombreCompleto;
    public $detallesPedido;
    public $total;
    protected $pdfPath;

    public function __construct($nombreCompleto, $detallesPedido, $total, $pdfPath)
    {
        $this->nombreCompleto = $nombreCompleto;
        $this->detallesPedido = $detallesPedido;
        $this->total = $total;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->view('emails.notificacionPagoCompletado')
                    ->subject('Pago Completado - Detalles de su Pedido')
                    ->attach($this->pdfPath, [
                        'as' => 'boleta.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
